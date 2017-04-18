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

		$params = json_decode($_GET['question']);

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

		// Perform the search.
		$result = "Ben Helmer enjoys photography.";

		// Send the response.
		return [
		  "value" => $result,
		  "status" => ($result !== NULL) ? 0 : 1,
		  "q" => $question,
		];
	}


	/**
	 * @TODO
	 *
	 *   IN PROGRESS
	 *
	 *
	 *
	 *
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

			$results = [];

			foreach ($terms->query AS $term) {
				if (!isset($term->weight) || !isset($term->word)) {
					Throw new Exception("Expected term with weight, found: " . print_r($term, TRUE));
				}
				else if ($term->weight <= 0) {
					$result[$term->word] = $term->weight;
				}
				else {
					// Ignore terms with a 0 weight.
				}
			}
		}
		catch (Exception $e) {
			return false;
		}
	}
}