<?php

namespace Drupal\userinfo\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\Entity\User;

/**
 * Provides a resource to get view modes by entity and bundle.
 * @RestResource(
 *   id = "userinfo_rest",
 *   label = @Translation("Userinfo API"),
 *   uri_paths = {
 *	   "create" = "/api/create-user",
 *   }
 * )
*/

class UserinfoRest extends ResourceBase {
	/*
	* Create User API : Post Method
	*/
	public function post(Request $data) {
		try {
			$content = $data->getContent();
			$params = json_decode($content, TRUE);
			
			$new_user = User::create([
				'name' => $params['username'],
				'pass' => $params['password'],
				'mail' => $params['email_id'],
				'field_firstname' => $params['first_name'],
				'field_lastname' => $params['last_name'],
				'field_mobile' => $params['mobile'],
				'status' => 1,
			])->save();
			
			$new_user_details = $this->fetch_user_detail($params['username']);
			
			$final_api_reponse = array(
				"status" => "OK",
				"message" => "New User Created",
				"result" => $new_user_details,
			);
			return new JsonResponse($final_api_reponse);
		}
		catch (EntityStorageException $e) {
			\Drupal::logger('candidate')->error($e->getMessage());
		}
	}
	
	/**
	* Fetch User Detail API based on User-ID
	*/
	public function fetch_user_detail($user_name){
		if(!empty($user_name)){
			$user = user_load_by_name($user_name);
			
			$user_detail['username'] = $user_name;
			$user_detail['email'] = $user->get('mail')->value;
			$user_detail['firstname'] = $user->get('field_firstname')->value;
			$user_detail['lastname'] = $user->get('field_lastname')->value;
			$user_detail['mobile'] = $user->get('field_mobile')->value;

			$final_api_reponse = array(
				'user' => $user_detail
			);
			return $final_api_reponse;
		}
		else{
			$this->exception_error_msg("Candidate details not found.");
		}
	}
}