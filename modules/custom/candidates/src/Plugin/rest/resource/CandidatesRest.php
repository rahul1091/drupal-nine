<?php

namespace Drupal\candidates\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;

/**
 * Provides a resource to get view modes by entity and bundle.
 * @RestResource(
 *   id = "candidates_rest",
 *   label = @Translation("Candidates API"),
 *   uri_paths = {
 *     "canonical" = "/api/get-candidate",
 *	   "create" = "/api/add-candidate",
 *	   "edit" = "/api/edit-candidate",
 *	   "delete" = "/api/delete-candidate"
 *   }
 * )
*/

class CandidatesRest extends ResourceBase {
    /**
    * Responds to GET request.
    * Get Candidates Details API : Get API
    */
    public function get() {
        $all_candidates = array();
        $candidates_fetch = \Drupal::database()->query("SELECT node_field_data.nid AS nid
        FROM {node_field_data} node_field_data
        WHERE (node_field_data.status = '1') AND (node_field_data.type IN ('candidate'))");
        $candidates_fetch_details = $candidates_fetch->fetchAll();

        foreach($candidates_fetch_details as $key => $node_id){
            $nodeid = $node_id->nid;
            $node = \Drupal::EntityTypeManager()->getStorage('node')->load($nodeid);
            $candidate_details['id'] = $nodeid;
            $candidate_details['name'] = $node->get('title')->value;
            $candidate_details['email'] = $node->get('field_email_id')->value;
			$candidate_details['gender'] = ucfirst($node->get('field_gender')->value);
			$candidate_details['country'] = $node->get('field_country')->value;
			$date = date_create($node->get('field_birth_date')->value);
			$date_of_birth = date_format($date, "d-m-Y");
			$candidate_details['dob'] = $date_of_birth;
            array_push($all_candidates, $candidate_details);
        }

        $final_api_reponse = array(
            "status" => "OK",
            "message" => "Candidate List",
            "result" => $all_candidates
        );
        return new JsonResponse($final_api_reponse);
    }
	
	/*
	* Add Candidates Details API : Post Method
	*/
	public function post(Request $data) {
		try {
			$content = $data->getContent();
			$params = json_decode($content, TRUE);
			
			$date = explode('/', $params['birth_date']);
			$birth_date = $date[2] . "-" . $date[1] . "-" . $date[0];
			
			// Create & Save candidate details
			$newCandidate = Node::create([
				'type' => 'candidate',
				'uid' => 1,
				'title' => array('value' => $params['name']),
				'field_email_id' => array('value' => $params['email']),
				'field_birth_date' => array('value' => $birth_date),
				'field_gender' => array('value' => $params['gender']),
				'field_country' => array('value' => $params['country']),
			]);
			$newCandidate->enforceIsNew();
			$newCandidate->save();
			
			$nid = $newCandidate->id();
			$new_candidate_details = $this->fetch_candidate_detail($nid);
			
			$final_api_reponse = array(
				"status" => "OK",
				"message" => "Candidate Data Saved",
				"result" => $new_candidate_details,
			);
			return new JsonResponse($final_api_reponse);
		}
		catch (EntityStorageException $e) {
			\Drupal::logger('candidate')->error($e->getMessage());
		}
	}
	
	/*
	* Edit Candidates Details API : Patch Method
	*/
	public function patch(Request $data) {
		try {
			$content = $data->getContent();
			$params = json_decode($content, TRUE);
			
			$nid = $params['nid'];
			
			$date = explode('/', $params['birth_date']);
			$birth_date = $date[2] . "-" . $date[1] . "-" . $date[0];

			if(!empty($nid)){
				$node = \Drupal::EntityTypeManager()->getStorage('node')->load($nid);
				$node->set("field_email_id", array('value' => $params['email']));
				$node->set("field_birth_date", array('value' => $birth_date));
				$node->set("field_gender", array('value' => $params['gender']));
				$node->set("field_country", array('value' => $params['country']));
				$node->save();

				$updated_candidate_details = $this->fetch_candidate_detail($nid);
				$final_api_reponse = array(
					"status" => "OK",
					"message" => "Candidate Data Updated",
					"result" => $updated_candidate_details,
				);
			}
			else{
				$this->exception_error_msg('Candidate ID is reqired');
			}
			return new JsonResponse($final_api_reponse);
		}
		catch (EntityStorageException $e) {
			\Drupal::logger('candidate')->error($e->getMessage());
		}
	}
	
	/**
	* Delete Candidates Details : Delete API
	*/
	public function delete(Request $data) {
		try{
			$content = $data->getContent();
			$params = json_decode($content, TRUE);
			
			$nid = $params['nid'];
			if(!empty($nid)){
				$deleted_candidate_details = $this->fetch_candidate_detail($nid);
				$node = \Drupal::EntityTypeManager()->getStorage('node')->load($nid);	
				$node->delete();
				$final_api_reponse = array(
					"status" => "OK",
					"message" => "Deleted Candidate Record",
					"result" => $deleted_candidate_details,
				);
			}
			return new JsonResponse($final_api_reponse);
		}
		catch(Exception $exception) {
			$web_service->error_exception_msg($exception->getMessage());
		}
	}
	
	/**
	* Fetch Candidates Detail API based on Node-ID
	*/
	public function fetch_candidate_detail($nid){
		if(!empty($nid)){
			$node = \Drupal::EntityTypeManager()->getStorage('node')->load($nid);

			$date = date_create($node->get('field_birth_date')->value);
			$birth_date = date_format($date, "d M Y");
			$candidate_details['name'] = $node->get('title')->value;
			$candidate_details['email'] = $node->get('field_email_id')->value;
			$candidate_details['dob'] = $birth_date;
			$candidate_details['gender'] = ucfirst($node->get('field_gender')->value);
			$candidate_details['country'] = $node->get('field_country')->value;

			$final_api_reponse = array(
				'candidate' => $candidate_details
			);
			return $final_api_reponse;
		}
		else{
			$this->exception_error_msg("Candidate details not found.");
		}
	}
}