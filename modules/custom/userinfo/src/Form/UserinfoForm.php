<?php

/**
 * @file
 * Contains \Drupal\userinfo\Form\UserinfoForm.
*/

namespace Drupal\userinfo\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
use Drupal\Core\Routing;
use Drupal\user\Entity\User;

class UserinfoForm extends FormBase {
	/**
	* {@inheritdoc}
	*/
	public function getFormId() {
		return 'userinfo_form';
	}

	/**
	* {@inheritdoc}
	*/
	public function buildForm(array $form, FormStateInterface $form_state) {
		$form['first_name'] = array(
			'#type' => 'textfield',
			'#title' => t('First Name'),
			'#required' => TRUE,
			'#maxlength' => 50,
			'#default_value' => '',
		);
		$form['last_name'] = array(
			'#type' => 'textfield',
			'#title' => t('Last Name'),
			'#required' => TRUE,
			'#maxlength' => 50,
			'#default_value' => '',
		);
		$form['email_id'] = array(
			'#type' => 'email',
			'#title' => t('Email-ID'),
			'#required' => TRUE,
			'#maxlength' => 50,
			'#default_value' => '',
		);
		$form['mobile'] = array(
			'#type' => 'textfield',
			'#title' => t('Mobile'),
			'#required' => TRUE,
			'#maxlength' => 10,
			'#default_value' => '',
		);
		$form['username'] = array(
			'#type' => 'textfield',
			'#title' => t('Username'),
			'#required' => TRUE,
			'#maxlength' => 50,
			'#default_value' => '',
		);
		$form['password'] = array(
			'#type' => 'password',
			'#title' => t('Password'),
			'#required' => TRUE,
			'#maxlength' => 50,
			'#default_value' => '',
		);
		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = array(
			'#type' => 'submit',
			'#value' => $this->t('Create User'),
			'#button_type' => 'primary',
		);
		return $form;
	}

	/**
	* {@inheritdoc}
	*/
	public function validateForm(array &$form, FormStateInterface $form_state) {
		if ($form_state->getValue('first_name') == '') {
			$form_state->setErrorByName('first_name', $this->t('Please Enter First Name'));
		}
		if ($form_state->getValue('last_name') == '') {
			$form_state->setErrorByName('last_name', $this->t('Please Enter Last Name'));
		}
		if ($form_state->getValue('email_id') == '') {
			$form_state->setErrorByName('email_id', $this->t('Please Enter Email-ID'));
		}
		if ($form_state->getValue('mobile') == '') {
			$form_state->setErrorByName('mobile', $this->t('Please Enter Mobile'));
		}
		if ($form_state->getValue('username') == '') {
			$form_state->setErrorByName('username', $this->t('Please Enter Username'));
		}
		if ($form_state->getValue('password') == '') {
			$form_state->setErrorByName('password', $this->t('Please Enter Password'));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submitForm(array &$form, FormStateInterface $form_state) {
		try{
			$new_user = User::create([
				'name' => $form_state->getValue('username'),
				'pass' => $form_state->getValue('password'),
				"mail" => $form_state->getValue('email_id'),
				"field_firstname" => $form_state->getValue('first_name'),
				"field_lastname" => $form_state->getValue('last_name'),
				"field_mobile" => $form_state->getValue('mobile'),
				'status' => 1,
			])->save();

			\Drupal::messenger()->addMessage('User created succesfully');
		}
		catch(Exception $ex){
			\Drupal::messenger()->addError($ex->getMessage());
		}
	}
}