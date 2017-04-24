<?php

namespace Drupal\npd_alexa\Controller;

// use Drupal\npd_alexa\Utility\SomethingTemplateTrait;
use Symfony\Component\HttpFoundation\Response;
use Drupal\node\Entity\Node;
use Drupal\node\Entity;
use Drupal\Core\Field\Plugin\Field;

class AlexaException extends \Exception {};

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

//		Throw new \Exception(print_r(json_decode($_GET['question']), TRUE));

		try {
			if (isset($_POST['question'])) {
				return $_POST;
				$params = json_decode($_POST['question']);
			}
			elseif (isset($_GET['question'])) {
				$params = json_decode($_GET['question']);
			}
			else {
				Throw new AlexaException('No question detected.');
			}

			$answer = $this->findAlexaResponse($params);
/**
			$answer = [
			  "value" => "Ben Helmer enjoys photography.",
			  "status" => 0
			];
*/

			$response = new Response();
			$response->headers->set('Content-Type', 'application/json');
			$response->setContent(json_encode($answer));

			return $response;
		}
		catch (AlexaException $e) {
			Throw new \Exception(' >>> ' . $e->getMessage());
		}
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
		syslog(1, '');
		syslog(1, ' > Begin -----------------------');
		syslog(1, ' > Searching for question array.');

		// Get our terms to search.
		$terms = $this->filterAlexaRequest($question);

		syslog(1, ' > Terms: ' . print_r($terms, TRUE));

		// Using our terms, query SOLR.
		$result = $this->getSolrResults($terms);

		// Clean up the response.
		$analysis = $this->analyzeSolrResponse($result);

		// Choose final response.
		$final = $this->selectAlexaResponse($analysis, $terms);

		if ($analysis !== FALSE && isset($_GET['debug']) && strtolower($_GET['debug']) === 'true') {
			dsm('<pre>' . print_r($analysis, TRUE) . '</pre>');
		}

		// Send the response.
		return array(
		  "value" => $final,
		  "status" => ($result !== NULL && $result !== false) ? 0 : 1,
		);
	}


	protected function getSolrResults($terms) {

		$max_score = 0;
		$max_result = null;
		$master_fields = [
			'tm_rendered_item',
			'spell',
		];


		foreach ($master_fields AS $field) {

			$my_score = 0;

			// Build a query string.
			$query = $this->assembleSolrQuery($terms, $field);

			// Perform the search.
			$result = $this->getSolrResponse($query);

			// Determine the max score.
			$my_score = $this->getSolrResponseMaxScore($result);

			// If this is a new record for max score, prefer it.
			if ($my_score > $max_score) {
				$max_score = $my_score;
				$max_result = $result;
			}
		}

		return $max_result;
	}


	protected function getSolrResponseMaxScore($result) {
		// decode the response.
		$response = json_decode($result);

		return $response->response->maxScore;
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
	protected function assembleSolrQuery($terms, $search_field) {

		// http://ey-alexa.local:8983/solr/collection1/query
		// http://solr:8983/solr/#/collection1
		// http://solr:8983/solr/collection1/query
		$host = 'solr'; // 'solr'; // 'ey-alexa.local';
		$port = '8983';
		$core_path = 'solr';
		$collection_path = 'collection1';
		$master_solr_field = $search_field;
//		$master_solr_field = 'spell';
		$fl = '*' . '%20' .'score';

		$q = 'http://' . $host . ':' . $port
			. '/' . $core_path . '/' . $collection_path . '/'
			. 'query?';

		// Get the main query terms.
		$args = 'q=' . $this->assembleFieldTerms($master_solr_field, $terms);

		return $q . $args . '&wt=json&indent=true&debugQuery=false&start=0' . '&fl=' . $fl;
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
			Throw new AlexaException('No terms to process!');
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
	 





	 	 @TODO

			IN PROGRESS




	 */
	protected function selectAlexaResponse($analysis, $terms) {
//		dsm($analysis);

		syslog(1, ' > Select Alexa Response...');

		$response = null;
		$entity = null;
		$entity_field = null;

		try {

			// Select the first result, and load the entity.
			if (is_array($analysis) && isset($analysis[0])) {
				syslog(1, ' > Select Alexa Response > Result: ' . print_r($analysis[0], TRUE));


				switch ($analysis[0]['bundle']) {

					case 'node':
						syslog(1, ' > Loading node id: ' . $analysis[0]['id']);
						$entity = node::load($analysis[0]['id']);

						if ($analysis[0]['type'] == 'homepage') {
							$entity_field = array(
								'title',
								'body',
								"field_main_hero",
								"field_middle_hero",
							);
						}
						else {
							$entity_field = array(
								'title',
								'body',
							);
						}

						break;

					default:
						syslog(1, ' > Select Alexa Response > Entity Type: ' . $analysis[0]['bundle']);
				}
			}

			// Look at the body.
			if ($entity !== null && $entity_field !== null) {
				syslog(1, ' >> Loading node fields: ');
				foreach ($entity_field AS $field) {
					syslog(1, ' >> Loading node fields > Checking: ' . $field);
					if (isset($entity->$field->value)) {
						$response .= $entity->$field->value;
					}
				}
			}
			else {
				syslog(1, ' > Error loading node: ' . $analysis[0]['id']);
			}

			// Check body for approprate response.
			if ($response !== null) {
				$response = $this->selectAlexaResponseContext($response, $terms);

				return strip_tags($response);
			}
		}
		catch (\Exception $e) {
			syslog(1, ' >>> ERROR LOADING NODE: ' . $e->getMessage());
		}

		return null;
	}


	protected function selectAlexaResponseContext($body, $terms) {
		$response = '';

		syslog(1, ' > Select Alexa Response: Body: ' . $body);


		$type = ['h1', 'h2', 'h3', 'h4', 'alexa'];
		// Search h1, h2, h3
		foreach ($type AS $tag) {
			$results[$tag] = $this->findAlexaResponseTags($tag, $body);
		}

//		dsm($results);
		syslog(1, ' > Possible responses: ' . print_r($results, TRUE));

		// Pick the best result.
		$response = $this->weightResponseTags($results, $terms);

		syslog(1, ' > Final Response: ' . print_r($response['response'], TRUE));

		return $response['response'];
	}


	protected function findAlexaResponseTags($type="alexa", $body) {
		$pattern = '';
		$answer_position = 0;
		$filter_short = true;


		//
		// @TODO
		//
		// Get any context back to the previous <h*> tag, or other break.
		// Use it to provide context.

		// Concept:
		//   When multiple tag-types are adjacent, group them. Then, select between them with whichever has the highest score.

		/**



		   @TODO



		 */


    $pattern_prefix = '#';
		$pattern_context = '((?:(?:<[^>]*>(?:[^<]*)(?:<[^>]*>?)*)){0,4})';
		$pattern_suffix = '#is';

		switch ($type) {
			case 'alexa':

				// Update 2 (For tags inside span)
				$pattern = '<span\s?class="alexa"\E\s?(?:.[^<>]*)?>((?:(?!</span>).)*)</span>';

/**
 
// Regex:
((?:(?:<[^>]*>(?:[^<]*)(?:<[^>]*>?)*)){0,4})
<span\s?class="alexa"\E\s?(?:.[^<>]*)?>((?:(?!</span>).)*)</span>

// Update 1
				$pattern = '<span\s?\Qclass="alexa"\E\s?(?:.[^<>]*)?>(.[^<>]*)</span>';

// Original
				$pattern = '#<span[^>]*class="alexa">(.[^<>]*)</span>#is';
*/

				$answer_position = 1;
				$filter_short = false;
				break;

			case 'h1':
			case 'h2':
			case 'h3':
			case 'h4':
			case 'h5':
			case 'h6':
							//		 <h3           (  [^>])*>((?!</    h3       >).)*<\/h3>
				$pattern = '<' . $type . '(?:[^>])*>((?:(?!</' . $type . '>).)*)</' . $type .'>';
				$answer_position = 1;
				break;

			default:
				return false;
		}

		// Build pattern.
		$pattern = $pattern_prefix . $pattern_context . $pattern . $pattern_suffix;

		// Since we add a capture group for conext, bump the position down by to accomodate.
		$answer_position += 1;


		// Match all tags.
		preg_match_all($pattern, $body, $matches);

/**
		if ($type == 'alexa' || $type == 'h3') {
			syslog(1, ' >>>>>>>>>>>>>>>>>>>>>>>>');
			syslog(1, ' > PREGMATCH ' . $type . ': ' . print_r($matches, TRUE));
			syslog(1, ' > PREGMATCH ' . $type . ' Pattern: ' . $pattern);
			syslog(1, ' >>>>>>>>>>>>>>>>>>>>>>>>');
		}
*/

		// If we had results, return them.
		if (isset($matches[$answer_position])) {
			$results = array();

			if (!is_array($matches[$answer_position])) {
				foreach($matches AS $pos => $data) {
					$matches[$pos] = array($data);
				}
			}

			foreach ($matches[$answer_position] AS $key => $data) {

				if ($filter_short === TRUE && strlen($data) < 32) {
					syslog(1, ' > <!> LENGTH FILTER -- Excluding: ' . $data);
					continue;
				}

				// Check the complete tag for any hints.
				if ($my_hint = $this->findHintTags($matches[0][$key])) {
					$results[$key]['hints'] = $my_hint;
				}

				if ($matches[1][$key]) {
					$results[$key]['context'] = trim($matches[1][$key]);
				}

				// Add match to $results[$key]['response'].
				$results[$key]['response'] = $data;
			}

			return $results;
		}
		else {
			syslog(1, 'No matches for ' . $type . ' at location: ' . $answer_position . ' ? Body size is: ' . strlen($body));
			syslog(1, print_r($matches, TRUE));
		}

		return false;
	}


	/**
	 * Find the first hints="" syntax for a string, and return it.
	 *
	 * @return string | boolean
	 *   False if no results, or a string of the contents of the first hint tag
	 *   found in the string.
	 */
	protected function findHintTags($string) {
		$pattern_hint = "#<.*hints=(?:\"|')([^\"']*)(?:'|\")?>#is";

		if (isset($string)) {
			preg_match($pattern_hint, $string, $matches);
			if ($matches[1]) {
				return $matches[1];
			}
		}
		return false;
	}


	protected function weightResponseTags($tags, $terms) {

		// Score card:
		// h1 = 3
		// h2 = 2
		// h3 || alexa = 1
		$score_chart = array(
			'h1' => 15,
			'h2' => 10,
			'h3' => 5,
			'alexa' => 0,
			'bonus_score_all_terms' => 25,
			'hint_multiplier' => 4,
			'context_multiplier' => 0.5,
			'combo_2' => 5,
			'combo_3' => 8,
			'combo_4' => 10,
			'combo_5' => 15,
			'combo_6' => 20,
		);
		$minimum_score_threshold = 10;
		$response_score = array();
		$top_score = 0;
		$top_scorer = null;

		foreach ($tags AS $type => $t_data) {
			$response_score[$type] = array();
			if (is_array($t_data)) {
				foreach ($t_data AS $key => $t) {
					$response_score[$type][$key] = 0;

					$all_my_terms = true;
					$terms_found_distinct = 0;
					syslog(1, ' --> Scoring Response: ' . print_r($tags[$type][$key]['response'], TRUE));

					// For each terms, find the number of occurances,
					// and talley a score for this response.
					foreach ($terms AS $term => $weight) {

						$found_my_term = false;

						// Response
						$response_score[$type][$key] += $this->getResponseTermScore($t['response'], $term, $weight, 1, $found_my_term);

						// Hints
						if (isset($t['hints'])) {
							$response_score[$type][$key] += $this->getResponseTermScore($t['hints'], $term, $weight, $score_chart['hint_multiplier'], $found_my_term);
						}

						// Context
						if (isset($t['context'])) {
							$response_score[$type][$key] += $this->getResponseTermScore($t['context'], $term, $weight, $score_chart['context_multiplier'], $found_my_term);
						}

						if ($weight > 1) {
							if ($found_my_term === false) {
								$all_my_terms = false;
							}
							else {
								$terms_found_distinct++;
							}
						}
					}

					// If the score is more than mediocre, add weights based on the tag type.
					if ($response_score[$type][$key] > 5 && isset($score_chart[$type])) {
						syslog(1, '  >> ' . $type . ' Weight Bonus: +' . $score_chart[$type]);
						$response_score[$type][$key] += $score_chart[$type];
					}
					// If multiple major words matched, add a bonus.
					if ($terms_found_distinct > 1) {
						if ($terms_found_distinct > 6) {
							$terms_found_distinct = 6;
						}
						syslog(1, '  >> <!!> Weighted ' . $terms_found_distinct .  ' term combo! +' . $score_chart['combo_' . $terms_found_distinct]);
						$response_score[$type][$key] += $score_chart['combo_' . $terms_found_distinct];
					}
					// If all major terms matched the response, add a bonus.
					if ($all_my_terms === true) {
						syslog(1, '  >> <!!!> All terms found!!! +' . $score_chart['bonus_score_all_terms']);
						$response_score[$type][$key] += $score_chart['bonus_score_all_terms'];
					}

					syslog(1, '  >> Final: ' . $response_score[$type][$key]);
					syslog(1, '');


					// Keep track of the top score.
					if ($response_score[$type][$key] > $top_score) {
						$top_score = $response_score[$type][$key];
						$top_scorer = $tags[$type][$key];
					}
				}
			}
			else {
				syslog(1, ' > ' . $term . ' > ' . $type . ' >      <!> NONE');
			}
		}

		syslog(1, '~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
		if ($top_score >= $minimum_score_threshold) {
			return $top_scorer;
		}

		// Return the top scorer, unless none met minimum score standards.
		syslog(1, ' > NO SUBSTANTIAL RESULTS FOUND. Highest score was: ' . $top_score);
		return false;
	}


	protected function getResponseTermScore($haystack, $needle, $weight, $multiplier, &$found_term) {
		$score = 0;
		if ($my_count = substr_count(strtolower($haystack), strtolower($needle))) {
			$score = ($my_count * $weight) * $multiplier;
			syslog(1, '  -> ' . $needle . ' x' . $my_count . ' | +' . $score);
			$found_term = true;
			return $score;
		}

		// Only maintain false. Don't swap if TRUE.
		// Other request to this function may have already found this term.
		$found_term = ($found_term === TRUE) ? true :false;
		return $score;
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
				Throw new AlexaException('No response returned from SOLR.');
			}

			if ($response->response->numFound > 0) {
				return $this->formatResponseAnalysis($response->response->docs);
			}
		}
		catch (AlexaException $e) {
			/**
			 
			   @TODO
			 
			 */
			dsm($e->getMessage());
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
			$result[$num]['score'] = $doc->score;
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
				Throw new AlexaException('Expected request with query key.');
			}

			$results = array();

			foreach ($terms->query AS $term) {
				if (!isset($term->weight) || !isset($term->word)) {
					Throw new AlexaException("Expected term with weight, found: " . print_r($term, TRUE));
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
		catch (AlexaException $e) {
			/**
			 
			   @TODO
			 
			 */
			
			Throw new \Exception(' >>> ' . $e->getMessage());

			return ' >>> ' . $e->getMessage();
		}
	}
}