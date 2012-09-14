<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accounts_model extends CI_Model {

	public function __construct(){	
		$this->load->model('users_model');
	}

	public function getAccountInfo($userHash){
		$user = $this->users_model->get_user(array('userHash' => $userHash));
		$pubKey = $this->users_model->get_pubKey_by_id($user['id']);

		$fingerprint = $this->users_model->get_pubKey_by_id($user['id'],true);

		if($pubKey === NULL){
			$pubKey = "No Public Key found.";
			$fingerprint = NULL;
			$dispFingerprint = NULL;
		} else if($fingerprint !== NULL){
			$pubKey = $pubKey;
			$dispFingerprint = substr($fingerprint,0,31).'<b>'.substr($fingerprint,32).'</b>';
		}

		$results = array(	'userName' => $user['userName'],
					'userHash' => $user['userHash'],
					'pubKey'   => $pubKey,
					'pubKeyFingerprint' => $fingerprint,
					'displayFingerprint' => $dispFingerprint,
					'twoStepAuth' => $user['twoStepAuth'],
					'profileMessage' => $user['profileMessage'] );
		return $results;
	}

	public function updateAccount($userID, $changes){

		if(count($changes) > 0){

			$keys = array_keys($changes);
			$count = 0;
			$results = array();


			foreach($keys as $key){

	                        if($key == 'pubKey'){

	                                $gpg = gnupg_init();
	                                $keyInfo = gnupg_import($gpg,$changes['pubKey']);
	                                $checkPrev = $this->db->get_where('publicKeys', array('userID'=>$userID));
	                                if($checkPrev->num_rows() > 0){
	                                        $this->db->where('userID',$userID);
	
	                                        $update = array('key' => $changes['pubKey'],
	                                                        'fingerprint' => $keyInfo['fingerprint']);
	                                        if($this->db->update('publicKeys', $update) === TRUE){
	                                                $result['pubKey'] = true;
	                                        } else {
	                                                $result['pubKey'] = false;
	                                        }
	                                } else {
	
	                                        $update = array('key' => $changes['pubKey'],
	                                                        'userID' => $userID,
	                                                        'fingerprint' => $keyInfo['fingerprint']);
	                                        if($this->db->insert('publicKeys',$update)){
	                                                $result['pubKey'] = true;
	                                        } else {
	                                                $result['pubKey'] = false;
	                                        }
	                                }
	                        } 

	                       if($key == 'password'){
	                                $this->db->where('id',$userID);
	                                $update = array('password' => $changes['password']);
	                                if($this->db->update('users',$update)){
	                                        $result['password'] = true;
	                                } else {
	                                        $result['password'] = false;
	                                }
	                        }
				
				if($key == 'twoStep'){
					$this->db->where('id',$userID);
					$update = array('twoStepAuth' => $changes['twoStep']);
					if($this->db->update('users',$update)){
						$result['twoStep'] = true;
					} else {
						$result['twoStep'] = false;
					}
				}


				if($key == 'profileMessage'){
					$this->db->where('id',$userID);
					$update = array('profileMessage' => nl2br($changes['profileMessage']));
					if($this->db->update('users',$update)){
						$result['profileMessage'] = true;
					} else {
						$result['profileMessage'] = false;
					}
				}

			}

			$returnVal = TRUE;
			foreach($result as $test){
				if($test == false)
					$returnVal = FALSE;
			}

			return $returnVal;
		} else {
			return NULL;
		}
	}
};