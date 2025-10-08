<?php namespace App\Controllers;
	use App\Models\SessionModel;
	use App\Models\UserModel;

	class Auth extends BaseController
	{
		public function login()
		{
			$data = array();
			if ($this->isPost()) {
				if ($this->AuthenticateApikey()) {
					$post = esc($this->getPost());
					$isvalid = true;
					if (!isset($post['phone']) || empty($post['phone'])) {
						$isvalid = false;
						$this->setError("Please enter your phone.");
					}
					else if ($isvalid && (!isset($post['password']) || empty($post['password']))) {
						$isvalid = false;
						$this->setError("Please enter the password.");
					}
					if ($isvalid) {
						$userModel = new UserModel();
						$user = $userModel->where('phone', $post['phone'])->first();
							if ($user && $user['status'] && password_verify($post['password'], $user['password'])) {
									$sessionToken = md5(uniqid(rand(), true));
									$sessionModel = new SessionModel();
									if($user['user_id']){
										//Remove Multiple Login
										//$sessionModel->updateLasttToken($member['user_id'],array('status' => 0));
									}
									$session_id = $sessionModel->insert(array(
									'user_id' => $user['user_id'],
									'session_token' => $sessionToken,
									'platform' => isset($post['platform']) ? $post['platform'] : '',
									'created_at' => date("Y-m-d H:i:s"),
									));
									if ($session_id > 0) {
										$param = array();
										$param['fcm_token'] = isset($post['fcm_token']) ? $post['fcm_token'] : '';
										$userModel->update($user['user_id'],$param);
										//echo $userModel->getLastQuery()->getQuery();exit;
										$sessionData = $this->sessionData($user['user_id']);
										$data[] = array(
										'id' => $sessionData['id'],
										'name' => $sessionData['name'],
										'email' => $sessionData['email'],
										'phone' => $sessionData['phone'],
										'role' => $sessionData['role'],
										'status' => $sessionData['status']);
										$this->setSuccess("Login Successfully");
										$this->setOutput($sessionToken, 'sessionToken');
										$this->setOutput(array('sessionData' => $data));
									}
								} else {
								if($user && $user['status'] == 0){
									$this->setError('Login Failed! Your account is suspended!');
								}
								else {
									$this->setError('You entered an incorrect phone or password. Please try again.');
								}
							}
						}
					} else {
					$this->setError($this->invalidApiKey);
				}
				} else {
				$this->setError($this->methodNotAllowed);
			}
			return $this->response();
		}

		public function register()
		{
			if ($this->AuthenticateApikey()) {
				$userModel = new UserModel();
				if($this->isPost()){
					$post = esc($this->getPost());
					$isvalidrequest = true;
					$errors = "";
					if (!isset($post['name']) || empty($post['name'])) {
						$this->setError("Please enter name.");
						$isvalidrequest = false;
					}
					if ($isvalidrequest && (!isset($post['email']) || empty($post['email']))) {
						$this->setError("Please enter email.");
						$isvalidrequest = false;
					}
					if ($isvalidrequest && (!isset($post['phone']) || empty($post['phone']))) {
						$this->setError("Please enter phone.");
						$isvalidrequest = false;
					}
					if ($isvalidrequest && (!isset($post['username']) || empty($post['username']))) {
						$this->setError("Please enter username.");
						$isvalidrequest = false;
					}
					if($isvalidrequest && isset($_FILES['image']) && $_FILES['image']['name']){
						$file_name = $_FILES['image']['name'];
						$file_size = $_FILES['image']['size'];
						$file_tmp = $_FILES['image']['tmp_name'];
						$file_type = $_FILES['image']['type'];
						$tmp = explode('.', $_FILES['image']['name']);
						$file_ext = end($tmp);
						$extensions = array("jpeg","jpg","png","JPEG","JPG","PNG");
						if(in_array($file_ext,$extensions)=== false){
							$errors = "extension not allowed, please choose a JPEG or PNG file.";
						}
						if($file_size > 2097152){
							$errors = 'File size must be excately 2 MB';
						}
						if(empty($errors) == true){
							$new_file_name = md5(uniqid(rand(),true));
							move_uploaded_file($file_tmp,"assets/upload/".$new_file_name.'.'.$file_ext);
							$post['image'] = "assets/upload/".$new_file_name.'.'.$file_ext;
						} else {
							$this->setError($errors);
						}
					}
					if($isvalidrequest && isset($post['reference_code']) && !empty($post['reference_code'])){
						$reference_by = $userModel->findByReferCode($post['reference_code']);
						if($reference_by){
							$post['refer_by'] = $reference_by['user_id'];
							$userModel->update($reference_by['user_id'], array('refer_points' => $reference_by['refer_points']+1));
						} else {
							$this->setError("Wrong Reference Code.");
							$isvalidrequest = false;
						}
					}
					if($isvalidrequest){
						$user = $userModel->where('username', $post['username'])->findAll();
						if(count($user) < 1){
							$created_at = date('Y-m-d H:i:s');

							if($post['user_type_id'] == 2){
								$national['admin_id'] = 1;
							}if($post['user_type_id'] == 3){
								$national = $userModel->find($post['parent_id']);
							}if($post['user_type_id'] == 4){
								$super = $userModel->find($post['parent_id']);
								$national = $userModel->find($super['national_id']);
							}if($post['user_type_id'] == 5){
								$distributor = $userModel->find($post['parent_id']);
								$super = $userModel->find($distributor['super_id']);
								$national = $userModel->find($super['national_id']);
							}

							$values = array(
							'name' => $post['name'],
							'email' => $post['email'],
							'phone' => $post['phone'],
							'company' => $post['company'],
							'gstin' => $post['gstin'],
							'username' => $post['username'],
							'password' => $post['password'],
							'country_id' => $post['country_id'],
							'state_id' => $post['state_id'],
							'city_id' => $post['city_id'],
							'address' => $post['address'],
							'user_type_id' => $post['user_type_id'],
							'status' => $post['status'],
							'transtype' => 'keys',
							'credit' => '1',
							'viewer_id' => 0,
							'image' => isset($post['image']) ? $post['image'] : '',
							'language_id' => isset($post['language_id']) ? $post['language_id'] : '1',
							'distributor_id' => isset($distributor['user_id']) ? $distributor['user_id'] : 0,
							'super_id' => isset($super['user_id']) ? $super['user_id'] : 0,
							'national_id' =>  isset($national['user_id']) ? $national['user_id'] : 0,
							'admin_id' => isset($national['admin_id']) ? $national['admin_id'] : 0,
							'refer_code' => generateReferralCode(),
							'refer_by' => isset($post['refer_by']) ? $post['refer_by'] : 0,
							'parent_id' => $post['parent_id'],
							'code' => GetUserCode(),
							'cvv' => rand(111,999),
							'updated_by' => 0,
							'updated_at' => $created_at,
							'created_at' => $created_at,
							'created_by' => 1
							);

							$user_id = $userModel->insert($values);

							$this->setSuccess("Register successfully");

						} else {
							$this->setError('User name found use another');
						}
					}
					} else {
					$this->setError($this->methodNotAllowed);
				}
				} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function resetpasswordrequest()
		{
			if ($this->isPost()) {
				if ($this->AuthenticateApikey()) {
					$post = esc($this->getPost());
					$isvalid = true;
					if (!isset($post['phone']) || empty($post['phone'])) {
						$this->setError("Please enter a phone no.");
						$isvalid = false;
					}
					else if ($isvalid  && strlen($post['phone']) != 10) {
						$this->setError("Please enter a valid phone no.");
						$isvalid = false;
					}
					if ($isvalid) {
						$userModel = new UserModel();
						$member = $userModel->where('phone', $post['phone'])->first();
						if ($member) {
							$resetkey = rand(100000,900000);
							$userModel->update($member['user_id'], array(
							'resetpassword_token' => $resetkey,
							'resetpassword_sent_at' => date('Y-m-d H:i:s')
							));
							$this->setSuccess("OTP for reset password");
							$this->setOutput(array("otp"=>$resetkey));
							} else {
							$this->setError("Phone no address not found.");
						}
					}
					} else {
					$this->setError($this->invalidApiKey);
				}
				} else {
				$this->setError($this->methodNotAllowed);
			}
			return $this->response();
		}

		public function resetpassword()
		{
			if ($this->isPost()) {
				if ($this->AuthenticateApikey()) {
					$post = esc($this->getPost());
					$isvalid = true;
					if (!isset($post['phone']) || empty($post['phone'])) {
						$this->setError("Please enter a phone no.");
						$isvalid = false;
					}
					else if ($isvalid  && strlen($post['phone']) != 10) {
						$this->setError("Please enter a valid phone no.");
						$isvalid = false;
					}
					else if (!isset($post['otp']) || empty($post['otp'])) {
						$this->setError("Please enter a otp.");
						$isvalid = false;
					}
					else if ($isvalid  && strlen($post['otp']) != 6) {
						$this->setError("Please enter a valid otp.");
						$isvalid = false;
						}else if (!isset($post['new_password']) || empty($post['new_password'])) {
						$this->setError("Please enter a new password.");
						$isvalid = false;
					}
					if ($isvalid) {
						$userModel = new UserModel();
						$member = $userModel->where('phone', $post['phone'])->first();
						if ($member) {
							if ($member['resetpassword_token'] == $post['otp']) {
								$userModel->update($member['user_id'], array(
                                'resetpassword_token' => 0,
                                'password' => password_hash($post['new_password'], PASSWORD_DEFAULT)
								));
								$this->setSuccess("Password reset done");
								$this->setOutput();
								} else {
								$this->setError("otp is wrong.");
							}
							} else {
							$this->setError("Phone no address not found.");
						}
					}
					} else {
					$this->setError($this->invalidApiKey);
				}
				} else {
				$this->setError($this->methodNotAllowed);
			}
			return $this->response();
		}

		public function set_firebase_res_id()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					$userModel = new UserModel();
					$member = $userModel->where('user_id', $this->_member['id'])->first();
					if ($member) {
						if ($this->isPost()) {
							$post = $this->getPost();
							$isvalid = true;
							if (!isset($post['firebase_res_id']) || empty($post['firebase_res_id'])) {
								$this->setError("Please enter your firebase_res_id.");
								$isvalid = false;
							}
							if ($isvalid) {
								$userModel->update($member['user_id'], array(
								'firebase_res_id' => isset($post['firebase_res_id']) ? $post['firebase_res_id'] : '',
								'updated_at' => date("Y-m-d H:i:s"),
								));
								$this->setSuccess("firebase Token Set");
							}
							}  else {
							$this->setError($this->methodNotAllowed);
						}
					}
					} else {
					$this->setError($this->invalidToken);
				}
				} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function logout()
		{
			if ($this->isDelete()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						$userModel = new UserModel();
						$userModel->update($this->_user['id'], array('firebase_res_id' => ''));
						$sessionModel = new SessionModel();
						$session = $sessionModel->findByID($this->_session['id']);
						$sessionModel->update($this->_session['id'], array(
                        'status' => 0,
                        'logged_out' => date("Y-m-d H:i:s"),
						));
						$this->setSuccess('You have successfully logout');
						$this->setOutput(json_decode("{}"));
						} else {
						$this->setError($this->invalidToken);
					}
					} else {
					$this->setError($this->invalidApiKey);
				}
				} else {
				$this->setError($this->methodNotAllowed);
			}
			return $this->response();
		}

		public function profile()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					$errors = '';
					$userModel = new UserModel();
					if($this->isPost()){
						$post = esc($this->getPost());
						$isvalidrequest = true;
						$errors = "";
						if (!isset($post['name']) || empty($post['name'])) {
							$this->setError("Please enter name.");
							$isvalidrequest = false;
						}
						if($isvalidrequest && isset($_FILES['upiqrcode']) && $_FILES['upiqrcode']['name']){
							$file_name = $_FILES['upiqrcode']['name'];
							$file_size = $_FILES['upiqrcode']['size'];
							$file_tmp = $_FILES['upiqrcode']['tmp_name'];
							$file_type = $_FILES['upiqrcode']['type'];
							$tmp = explode('.', $_FILES['upiqrcode']['name']);
							$file_ext = end($tmp);
							$extensions = array("jpeg","jpg","png","JPEG","JPG","PNG");
							if(in_array($file_ext,$extensions)=== false){
								$errors = "extension not allowed, please choose a JPEG or PNG file.";
							}
							if($file_size > 2097152){
								$errors = 'File size must be excately 2 MB';
							}
							if(empty($errors) == true){
								$new_file_name = md5(uniqid(rand(),true));
								move_uploaded_file($file_tmp,"assets/upload/".$new_file_name.'.'.$file_ext);
								$post['upiqrcode'] = "assets/upload/".$new_file_name.'.'.$file_ext;
							}
						}
						if($isvalidrequest){
							if($errors){
								$this->setError($errors);
							}
							else{
								$user = $userModel->findByID($this->_member['id']);
								$values = array(
								'name' => $post['name'],
								'email' => isset($post['email']) ? $post['email'] : $user['email'],
								'company' => isset($post['company']) ? $post['company'] : $user['company'],
								'gstin' => isset($post['gstin']) ? $post['gstin'] : $user['gstin'],
								'upi' => isset($post['upi']) ? $post['upi'] : $user['upi'],
								'upiqrcode' => isset($post['upiqrcode']) ? $post['upiqrcode'] : $user['upiqrcode'],
								'updated_by' => $this->_member['id'],
								'updated_at' => date("Y-m-d H:i:s")
								);

								$userModel->update($this->_member['id'], $values);
								$this->setSuccess('Updated Successfully !!!');
								//echo $userModel->getLastQuery()->getQuery(); exit();
							}
						}
						} else {
						$user = $userModel->findByID($this->_member['id']);
						if($user){
							$this->setSuccess('Successfully !!!');
							$user['upiqrcode'] = site_url().$user['upiqrcode'];
							$this->setOutput($user);
							} else {
							$this->setError('Error !!!!');
						}
					}
					} else {
					$this->setError($this->invalidToken);
				}
				} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}
	}