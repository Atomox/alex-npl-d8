<?php

namespace Drupal\npd_alexa\Form;

// use Drupal\npd_alexa\Utility\SomethingTemplateTrait;
// use Symfony\Component\HttpFoundation\Response;

// Form API
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AlexaAdminSearchForm extends FormBase {
	// use SomethingTemplateTrait;

	protected function getModuleName() {
		return 'npd_alexa';
	}

	public function getFormId() {
    return 'mymodule_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['phone_number'] = array(
      '#type' => 'tel',
      '#title' => 'Example phone',
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#title' => 'Submit',
      '#value' => 'Submit'
    );
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
	  /**
	  if (strlen($form_state->getValue('phone_number')) < 3) {
	    $form_state->setErrorByName('phone_number', $this->t('The phone number is too short. Please enter a full phone number.'));
	  }
	  */

		dsm('Validate SHUT UP');
	}

	/**
	 * {@inheritdoc}
	 */
	public function submitForm(array &$form, FormStateInterface $form_state) {

		// Assemble a query.
		$q = $this->buildAlexaQuery([]);

		// Convert to JSON.
		$q = json_encode($q, JSON_FORCE_OBJECT);

		// Encode query.
		$q = urlencode($q);

		// cURL Alexa endpoint.
//		$q = 'http://ey-alexa.local/api/v1/alexa?question=' . $q;

		$q = 'http://ey-alexa.local/api/v1/alexa?question=%7B%0A++%22query%22+%3A+%5B+%7B%0A++++%22word%22+%3A+%22new%20york%22%2C%0A++++%22weight%22+%3A+4%0A++%7D%2C+%7B%0A++++%22word%22+%3A+%22ey%22%2C%0A++++%22weight%22+%3A+4%0A++%7D%2C+%7B%0A++++%22word%22+%3A+%22ey%22%2C%0A++++%22weight%22+%3A+2%0A++%7D+%5D%2C%0A++%22required%22+%3A+null%2C%0A++%22path%22+%3A+%22%2Fcontent%2Fey%22%2C%0A++%22type%22+%3A+%22%5Bnt%3Abase%5D%22%0A%7D';

//		$q = 'http://ey-alexa.local/api/v1/alexa?question=%7B%22query%22%3A%7B%220%22%3A%7B%22word%22%3A%22helmer%22%2C%22weight%22%3A4%7D%2C%221%22%3A%7B%22word%22%3A%22ben%22%2C%22weight%22%3A4%7D%2C%222%22%3A%7B%22word%22%3A%22hobby%22%2C%22weight%22%3A2%7D%7D%2C%22required%22%3Anull%2C%22path%22%3A%22%5C%2Fcontent%5C%2Fey%22%2C%22type%22%3A%22%5Bnt%3Abase%5D%22%7D'


		$result = $this->getCurlResponse($q);

		dsm($q);
		dsm('<pre>' . print_r($result, TRUE) . '</pre>');


//	  drupal_set_message($this->t('Your phone number is @number', array('@number' => $form_state->getValue('phone_number'))));
	}





	public function buildAlexaQuery($terms) {
		$q = [
			"query" => array(
				[
			    "word" => "helmer",
			    "weight" => 4
			  ],
			  [
			    "word" => "ben",
			    "weight" => 4
			  ],
			  [
			    "word" => "hobby",
			    "weight" => 2
			  ]
			),
		  "required" => null,
		  "path" => "/content/ey",
		  "type" => "[nt:base]"
		];

		return $q;
	}



	public function alexaAdminSearch() {
		return array(
        '#markup' => '' . t('Hello there!') . '',
    );
	}


	protected function getCurlResponse($query) {

//		Throw new \Exception(' >>> ' . print_r($query, TRUE));

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
}
