<?php namespace App\Controllers;
	use App\Models\UserModel;
	class Users extends BaseController
	{
		public function index()
		{
			if ($this->isGet()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						if($this->CheckPermission('users-view')){
							$userModel = new UserModel();
							$params = array();
							$page = intval($this->getParam('page', 1));
							$length = intval($this->getParam('per_page', 25));
							$totalrecords = intval($this->getParam('totalrecords', 0));
							$keywords = $this->getParam('keywords', '');
							$date = $this->getParam('date', '');
							$start_date = $this->getParam('start_date', '');
							$end_date = $this->getParam('end_date', '');
							$role = $this->getParam('role', '');
							$status = $this->getParam('status', '');
							$order_by_col = $this->getParam('order_by_col', "");
							$order_by = $this->getParam('order_by', "");

							if($keywords != ''){
								$params['keywords'] = $keywords;
							}
							if($date != ''){
								$params['date'] = $date;
							}
							if($start_date != '' && $end_date != ''){
								$params['start_date'] = $start_date;
								$params['end_date'] = $end_date;
							}
							if($role != ''){
								$params['role'] = $role;
							}
							if($status != ''){
								$params['status'] = $status;
							}

                            $params['non_admin'] = true;

							if($totalrecords == 0 || $page == 1){
								$params['count'] = true;
								$totalrecords = $userModel->search($params);
								unset($params['count']);
							}
							$paging = paging($page, $totalrecords, $length);
							$params['limit'] = array('length' => $paging['length'], 'offset' => $paging['offset']);

							if($order_by_col != ''){
								$params['sort'] = array('column' => $order_by_col, 'order' => $order_by);
							}

							$users = $userModel->search($params);
							//echo $userModel->getLastQuery()->getQuery();exit;

							// Remove sensitive information
							foreach($users as $key => $user){
								unset($users[$key]['password']);
								unset($users[$key]['otp']);
								unset($users[$key]['otp_expiry']);
								unset($users[$key]['otp_attempts']);
							}

							$remainingrecords = $totalrecords - ($paging['offset'] + count($users));
							$paging['remainingrecords'] = $remainingrecords;
							$this->setSuccess($this->successMessage);
							$this->setOutput(array('paging' => array($paging), 'users' => $users));
						} else {
							$this->setError('You do not have permission to access this module');
						}
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

		public function details($user_id = 0)
		{
			if ($this->isGet()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						if($this->CheckPermission('users-view')){
							if(is_numeric($user_id)){
								$user_id = intval($user_id);
								if ($user_id > 0) {
									$userModel = new UserModel();
									$user = $userModel->findByID($user_id);
									if($user){
										// Remove sensitive information
										unset($user['password']);
										unset($user['otp']);
										unset($user['otp_expiry']);
										unset($user['otp_attempts']);

										$this->setSuccess();
										$this->setOutput(array('user' => array($user)));
									} else {
										$this->setError($this->noContent);
									}
								} else {
									$this->setError("Please enter user_id");
								}
							} else {
								$this->setError("Please enter user_id in numeric");
							}
						} else {
							$this->setError('You do not have permission to access this module');
						}
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

		public function new()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->CheckPermission('users-create')){
						$userModel = new UserModel();

						if($this->isPost()){
							$post = esc($this->getPost());
							$isvalidrequest = true;

							// Required field validation
							if (!isset($post['full_name']) || empty($post['full_name'])) {
								$this->setError("Please enter full name.");
								$isvalidrequest = false;
							}
							if (!isset($post['email']) || empty($post['email'])) {
								$this->setError("Please enter email.");
								$isvalidrequest = false;
							}
							if (!isset($post['phone']) || empty($post['phone'])) {
								$this->setError("Please enter phone number.");
								$isvalidrequest = false;
							} else {
								// Check if phone already exists
								if($isvalidrequest){
									$existingUser = $userModel->where('phone', $post['phone'])->first();
									if($existingUser){
										$this->setError("Phone number already exists.");
										$isvalidrequest = false;
									}
								}
							}
							if (!isset($post['password']) || empty($post['password'])) {
								$this->setError("Please enter password.");
								$isvalidrequest = false;
							}
							if (!isset($post['role_id']) || empty($post['role_id'])) {
								$this->setError("Please select role.");
								$isvalidrequest = false;
							}

							// Handle profile photo upload
							if($isvalidrequest && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['name']){
								$file_name = $_FILES['profile_photo']['name'];
								$file_size = $_FILES['profile_photo']['size'];
								$file_tmp = $_FILES['profile_photo']['tmp_name'];
								$file_type = $_FILES['profile_photo']['type'];
								$tmp = explode('.', $_FILES['profile_photo']['name']);
								$file_ext = end($tmp);
								$extensions = array("jpeg","jpg","png","JPEG","JPG","PNG");
								if(in_array($file_ext,$extensions)=== false){
									$this->setError("extension not allowed, please choose a JPEG or PNG file.");
									$isvalidrequest = false;
								}
								if($isvalidrequest && empty($this->_output['message'])){
									$new_file_name = md5(uniqid(rand(),true));
									$upload_path = "uploads/users/";
									if (!is_dir($upload_path)) {
										mkdir($upload_path, 0755, true);
									}
									move_uploaded_file($file_tmp, $upload_path.$new_file_name.'.'.$file_ext);
									$post['profile_photo'] = $new_file_name.'.'.$file_ext;
								}
							}

							if($isvalidrequest && empty($this->_output['message'])){
								// Insert new user
								$values = array(
									'full_name' => $post['full_name'],
									'email' => $post['email'],
									'phone' => $post['phone'],
									'password' => password_hash($post['password'], PASSWORD_DEFAULT),
									'role_id' => $post['role_id'],
									'status' => isset($post['status']) ? $post['status'] : 1,
									'language' => isset($post['language']) ? $post['language'] : 'en',
									'profile_photo' => isset($post['profile_photo']) ? $post['profile_photo'] : '',
									'code' => isset($post['code']) ? $post['code'] : '',
									'fcm_token' => isset($post['fcm_token']) ? $post['fcm_token'] : '',
									'created_at' => date("Y-m-d H:i:s"),
									'created_by' => $this->_member['id']
								);
								$user_id = $userModel->insert($values);

								if($user_id > 0){
									$this->setOutput($user_id, 'user_id');
									$this->setSuccess("User added successfully");
								} else {
									$this->setError("Failed to add user");
								}
							}
						} else {
							$this->setError($this->methodNotAllowed);
						}
					} else {
						$this->setError('You do not have permission to access this module');
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function edit($user_id = 0)
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->CheckPermission('users-edit')){
						$userModel = new UserModel();

						if($this->isPost() || $this->isPut()){
							$post = esc($this->getPost());
							$user_id = isset($user_id) ? intval($user_id) : 0;
							$isvalidrequest = true;

							if($user_id <= 0){
								$this->setError("Please provide valid user_id");
								$isvalidrequest = false;
							}

							// Update validation
							if($isvalidrequest){
								if (isset($post['phone']) && !empty($post['phone'])) {
									// Check if phone already exists for other users
									$existingUser = $userModel->where('phone', $post['phone'])
																->where('user_id !=', $user_id)
																->first();
									if($existingUser){
										$this->setError("Phone number already exists.");
										$isvalidrequest = false;
									}
								}
							}

							// Handle profile photo upload
							if($isvalidrequest && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['name']){
								$file_name = $_FILES['profile_photo']['name'];
								$file_size = $_FILES['profile_photo']['size'];
								$file_tmp = $_FILES['profile_photo']['tmp_name'];
								$file_type = $_FILES['profile_photo']['type'];
								$tmp = explode('.', $_FILES['profile_photo']['name']);
								$file_ext = end($tmp);
								$extensions = array("jpeg","jpg","png","JPEG","JPG","PNG");
								if(in_array($file_ext,$extensions)=== false){
									$this->setError("extension not allowed, please choose a JPEG or PNG file.");
									$isvalidrequest = false;
								}
								if($isvalidrequest && empty($this->_output['message'])){
									$new_file_name = md5(uniqid(rand(),true));
									$upload_path = "uploads/users/";
									if (!is_dir($upload_path)) {
										mkdir($upload_path, 0755, true);
									}
									move_uploaded_file($file_tmp, $upload_path.$new_file_name.'.'.$file_ext);
									$post['profile_photo'] = $new_file_name.'.'.$file_ext;
								}
							}

							if($isvalidrequest && empty($this->_output['message'])){
								// Update existing user
								$user = $userModel->findByID($user_id);
								if($user){
									$values = array(
										'full_name' => isset($post['full_name']) ? $post['full_name'] : $user['full_name'],
										'email' => isset($post['email']) ? $post['email'] : $user['email'],
										'phone' => isset($post['phone']) ? $post['phone'] : $user['phone'],
										'role_id' => isset($post['role_id']) ? $post['role_id'] : $user['role_id'],
										'status' => isset($post['status']) ? $post['status'] : $user['status'],
										'language' => isset($post['language']) ? $post['language'] : $user['language'],
										'profile_photo' => isset($post['profile_photo']) ? $post['profile_photo'] : $user['profile_photo'],
										'updated_at' => date("Y-m-d H:i:s"),
										'updated_by' => $this->_member['id']
									);

									// Update password if provided
									if (isset($post['password']) && !empty($post['password'])) {
										$values['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
									}

									$userModel->update($user['user_id'], $values);

									$this->setOutput($user['user_id'], 'user_id');
									$this->setSuccess('User updated successfully');
								} else {
									$this->setError("Invalid user record");
								}
							}
						} else {
							$this->setError($this->methodNotAllowed);
						}
					} else {
						$this->setError('You do not have permission to access this module');
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function delete($user_id = 0)
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->CheckPermission('users-delete')){
						if($this->isDelete()){
							$user_id = isset($user_id) ? intval($user_id) : 0;

							if($user_id > 0){
								$userModel = new UserModel();
								$user = $userModel->findByID($user_id);

								if($user){
									// Prevent self-deletion
									if($user_id == $this->_member['id']){
										$this->setError("You cannot delete your own account");
									} else {
										// Soft delete by updating status
										$userModel->update($user_id, [
											'status' => 0,
											'updated_at' => date("Y-m-d H:i:s"),
											'updated_by' => $this->_member['id']
										]);

										$this->setSuccess("User deleted successfully");
									}
								} else {
									$this->setError("User not found");
								}
							} else {
								$this->setError("Please provide valid user_id");
							}
						} else {
							$this->setError($this->methodNotAllowed);
						}
					} else {
						$this->setError('You do not have permission to access this module');
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

