<?php

namespace Drupal\npd_alexa\Controller;

// use Drupal\npd_alexa\Utility\SomethingTemplateTrait;
use Symfony\Component\HttpFoundation\Response;

class AlexaApiController {
	// use SomethingTemplateTrait;

	protected function getModuleName() {
		return 'npd_alexa';
	}


	/**
	 * Alexa hits this API endpoint to request a response to a question.
	 *
	 * Pass a GET request as 'question=json_string', like:
	 *
	 * {
	 *   "query": [
	 *     {
	 *       "word": "helmer",
	 *       "weight": 4
	 *     },
	 *     {
	 *       "word": "ben",
	 *       "weight": 4
	 *     },
	 *     {
	 *       "word": "hobby",
	 *       "weight": 2
	 *     }
	 *   ],
	 *   "required": null,
	 *   "path": "/content/ey",
	 *   "type": "[nt:base]"
	 * }
 	 *
	 * @return JSON object response.
	 */
	public function alexaRequest() {

		if (isset($_POST['question'])) {
			return $_POST;
			$params = json_decode($_POST['question']);
		}
		elseif (isset($_GET['question'])) {
			$params = json_decode($_GET['question']);
		}
		else {
			Throw new Exception('No question detected.');
		}

		$answer = $this->findAlexaResponse($params);

		$response = new Response();
		$response->setContent(json_encode($answer));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}


	/**
	 * Return a response to Alexa, as NPL expects it.
	 *
	 * @param stdClass $question
	 *   The query as passed from NPL/Alexa.
	 *
	 * @return stdClass
	 *   Final response, to be JSON encoded.
	 */
	protected function findAlexaResponse($question) {
		syslog(1, 'Searching for question array.');

		// Get our terms to search.
		$terms = $this->filterAlexaRequest($question);

		$query = $this->assembleSolrQuery($terms);

		// Perform the search.
		$result = $this->getSolrResponse($query);

		$analysis = $this->analyzeSolrResponse($result);

		if ($analysis !== FALSE) {
			dsm('<pre>' . print_r($analysis, TRUE) . '</pre>');
		}

		// Send the response.
		return [
		  "value" => $result,
		  "status" => ($result !== NULL) ? 0 : 1,
		  "q" => $query,
		  "question" => $question,
		  "terms" => $terms,
		];
	}


	/**
	 * Assemble a master SOLR query URL to call, given our search terms.
	 *
	 * @param stdClass $terms
	 *   A list of search term => weight pairs.
	 *
	 * @return string
	 *   A prepared SOLR search string/URL.
	 */
	protected function assembleSolrQuery($terms) {

		// http://ey-alexa.local:8983/solr/collection1/query
		// http://solr:8983/solr/#/collection1
		// http://solr:8983/solr/collection1/query
		$host = 'solr'; // 'solr'; // 'ey-alexa.local';
		$port = '8983';
		$core_path = 'solr';
		$collection_path = 'collection1';
		$master_solr_field = 'tm_rendered_item';

		$q = 'http://' . $host . ':' . $port
			. '/' . $core_path . '/' . $collection_path . '/'
			. 'query?';

		// Get the main query terms.
		$args = 'q=' . $this->assembleFieldTerms($master_solr_field, $terms);

		return $q . $args . '&wt=json&indent=true&debugQuery=false&start=0';
	}


	/**
	 * Given a list of terms to search for in a solr field,
	 * prepare them as a query string.
	 *
	 * @param  [type] $field [description]
	 * @param  [type] $terms [description]
	 *
	 * @return string
	 *   A query string fragment, like:
	 *
	 *   `tm_rendered_item:jenko^10%20tm_rendered_item:greg`, including
	 *   weighted term syntax.
	 */
	protected function assembleFieldTerms($field, $terms) {
		$q = array();

		if (empty($terms)) {
			Throw new Exception('No terms to process!');
		}

		foreach ($terms AS $key => $weight) {
			if (explode(" ", $key)) {
				$key = '"' . $key . '"';
			}

			$my_term = $field . ':' . $key;
			if ($weight > 1) {
				$my_term .= '^' . $weight;
			}

			$q[] = urlencode($my_term);
		}

		$q = implode('%20', $q);

		return $q;
	}


	/**
	 * Make a call to the passed URL, and get the response.
	 *
	 * @param string $query
	 *   A properly assembled URL we should call, with irregular
	 *   characters URLENCODED.
	 *
	 * @return mixed
	 *   The response of the call.
	 */
	protected function getSolrResponse($query) {
    // create curl resource
    $ch = curl_init();

    // set url
    curl_setopt($ch, CURLOPT_URL, $query);

    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // $output contains the output string
    $output = curl_exec($ch);

    // close curl resource to free up system resources
    curl_close($ch);

    return $output;
	}


	protected function analyzeSolrResponse($response) {

		try {
			// decode the response.
			$response = json_decode($response);

			if (!isset($response->response)) {
				Throw new Exception('No response returned from SOLR.');
			}

			if ($response->response->numFound > 0) {
				return $this->formatResponseAnalysis($response->response->docs);
			}
		}
		catch (Exception $e) {
			/**
			 
			   @TODO
			 
			 */
		}

		return false;
	}


	/**
	 * Format SOLR results into a human-readable, manipulatable array.
	 *
	 * @param array $docs
	 *  The docs array from a SOLR query result.
	 *
	 * @return array(stdClass)
	 *   All doc results, with single layer, drupal-centric information.
	 */
	protected function formatResponseAnalysis($docs) {

		$result = array();

		foreach ($docs AS $num => $doc) {
			$result[$num]['title'] = $doc->ts_title;
			$result[$num]['type'] = $doc->ss_type;

			// ID format: entity:node/id:lang
			$my_type = explode(':', $doc->ss_search_api_id);

			$result[$num]['bundle'] = explode('/', $my_type[1])[0];
			$result[$num]['id'] = explode('/', $my_type[1])[1];
			$result[$num]['lang'] = $my_type[2];
			$result[$num]['text'] = $doc->tm_rendered_item;
		}

		return $result;
	}


	/**
	 * Filter out all nonsense, and get an array of weights,
	 * keyed by their question keyword.
	 *
	 * @param stdClass $terms
	 *   The query as passed from NPL/Alexa.
	 *
	 * @return stdClass Relevant terms as keys, with the weight as the values.
	 */
	protected function filterAlexaRequest($terms) {
		try {
			if (!isset($terms->query)) {
				Throw new Exception('Expected request with query key.');
			}

			$results = array();

			foreach ($terms->query AS $term) {
				if (!isset($term->weight) || !isset($term->word)) {
					Throw new Exception("Expected term with weight, found: " . print_r($term, TRUE));
				}
				else if ($term->weight > 0) {
					$results[$term->word] = $term->weight;
				}
				else {
					// Ignore terms with a 0 weight.
				}
			}

			return $results;
		}
		catch (Exception $e) {
			/**
			 
			   @TODO
			 
			 */
			return ' >>> ' . $e->getMessage();
		}
	}
}