<?php

/**
 * @file
 * Contains \Drupal\candidates\Form\CandidatesForm.
*/

namespace Drupal\candidates\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class CandidatesForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'candidates_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$form['candidate_name'] = array(
			'#type' => 'textfield',
			'#title' => 'Name',
			'#required' => TRUE,
			'#default_value' => '',
		);
		$form['candidate_email'] = array(
			'#type' => 'email',
			'#title' => 'Email-ID',
			'#required' => TRUE,
			'#default_value' => '',
		);
		$form['candidate_dob'] = array(
			'#type' => 'date',
			'#title' => 'Date of Birth',
			'#required' => TRUE,
			'#default_value' => '',
		);
		$form['candidate_gender'] = array(
			'#type' => 'select',
			'#title' => 'Gender',
			'#required' => TRUE,
			'#options' => array(
				'' => '-- Select --',
				'male' => 'Male',
				'female' => 'Female',
			),
			'#default_value' => '',
		);
		$form['candidate_country'] = array(
			'#type' => 'textfield',
			'#title' => 'Country',
			'#required' => TRUE,
			'#default_value' => '',
		);
		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Save'),
			'#button_type' => 'primary',
		);
		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if ($form_state->getValue('candidate_name') == '') {
			$form_state->setErrorByName('candidate_name', $this->t('Please Enter Candidate Name'));
		}
		if ($form_state->getValue('candidate_email') == '') {
			$form_state->setErrorByName('candidate_email', $this->t('Please Enter Email-ID'));
		}
		if ($form_state->getValue('candidate_dob') == '') {
			$form_state->setErrorByName('candidate_dob', $this->t('Please Enter Date of Birth'));
		}
		if ($form_state->getValue('candidate_gender') == '') {
			$form_state->setErrorByName('candidate_gender', $this->t('Please Enter Gender'));
		}
		if ($form_state->getValue('candidate_country') == '') {
			$form_state->setErrorByName('candidate_country', $this->t('Please Enter Country'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		// Create & Save candidate details 
		$node = Node::create(['type' => 'candidate']);
		$node->langcode = "en";
		$node->uid = 1;
		$node->promote = 0;
		$node->sticky = 0;
		$node->title= $form_state->getValue('candidate_name');
		$node->field_email_id = $form_state->getValue('candidate_email');
		$node->field_birth_date = $form_state->getValue('candidate_dob');
		$node->field_gender = $form_state->getValue('candidate_gender');
		$node->field_country = $form_state->getValue('candidate_country');
		$node->save();

		\Drupal::messenger()->addMessage('Candidate Data Saved Successfully');
		//$send_mail = \Drupal::service('candidates_service')->candidate_notify_mail($mail);
	}
}
