<?php namespace App\Controllers;
	use App\Models\SessionModel;
	use App\Models\UserModel;
	use App\Models\UserAttendanceModel;
	use App\Libraries\JwtLib;

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
							/* Two-Factor Authentication flow */
							if ($this->AppConfig->twoFactorAuth['enabled']) {
								sendOtp($user['user_id']);
								$this->setSuccess('OTP sent successfully. Please verify to continue.');
							} else {
								// Generate JWT token
								$jwt = new JwtLib();
								$AppConfig = new \Config\AppConfig();
								$tokenPayload = [
								'user_id' => $user['user_id'],
								'phone' => $user['phone'],
								'role' => $user['role_id']
								];
								$sessionToken = $jwt->generateToken($tokenPayload, $AppConfig->jwt_expiry);

								$sessionModel = new SessionModel();
								if($user['user_id']){
									//Remove Multiple Login
									//$sessionModel->updateLasttToken($member['user_id'],array('status' => 0));
								}
								$session_id = $sessionModel->insert(array(
								'user_id' => $user['user_id'],
								'session_token' => $sessionToken,
								'platform' => isset($post['platform']) ? $post['platform'] : '',
								'logged_in' => date("Y-m-d H:i:s"),
								));
								if ($session_id > 0) {
									$param = array();
									$param['fcm_token'] = isset($post['fcm_token']) ? $post['fcm_token'] : '';
									$userModel->update($user['user_id'],$param);

									// Record first login and last login in attendance
									$attendanceModel = new UserAttendanceModel();
									// Record first login of the day (only if not already recorded)
									$attendanceModel->recordLogin($user['user_id'], date('Y-m-d'));

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
					// Validate required fields
					if (!isset($post['full_name']) || empty($post['full_name'])) {
						$this->setError("Please enter full name.");
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
					if ($isvalidrequest && (!isset($post['password']) || empty($post['password']))) {
						$this->setError("Please enter password.");
						$isvalidrequest = false;
					}

					if($isvalidrequest){
						// Check if phone already exists
						$existingPhone = $userModel->where('phone', $post['phone'])->first();
						if($existingPhone){
							$this->setError('Phone number already registered.');
							$isvalidrequest = false;
						}
					}
					if($isvalidrequest){
						$created_at = date('Y-m-d H:i:s');
						$values = array(
						'full_name' => $post['full_name'],
						'email' => $post['email'],
						'phone' => $post['phone'],
						'password' => password_hash($post['password'], PASSWORD_DEFAULT),
						'role_id' => isset($post['role_id']) ? $post['role_id'] : 2, // Default role
						'status' => isset($post['status']) ? $post['status'] : 1,
						'language' => isset($post['language']) ? $post['language'] : 'en',
						'profile_photo' => isset($post['profile_photo']) ? $post['profile_photo'] : '',
						'created_by' => 0,
						'created_at' => $created_at,
						'updated_at' => $created_at
						);

						$user_id = $userModel->insert($values);

						if($user_id){
							$this->setSuccess("Registration successful");
							$this->setOutput(['user_id' => $user_id]);
							} else {
							$this->setError("Registration failed. Please try again.");
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
						$user = $userModel->where('phone', $post['phone'])->first();
						if ($user) {
							// Generate 6-digit OTP
							$otp = rand(100000, 999999);
							$otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

							$userModel->update($user['user_id'], array(
							'otp' => $otp,
							'otp_expiry' => $otp_expiry,
							'otp_attempts' => 0,
							'updated_at' => date('Y-m-d H:i:s')
							));

							// TODO: Send OTP via SMS

							$this->setSuccess("OTP sent successfully for password reset");
							$this->setOutput(array("otp" => $otp)); // Remove this in production
							} else {
							$this->setError("Phone number not found.");
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
						$this->setError("Please enter OTP.");
						$isvalid = false;
					}
					else if ($isvalid  && strlen($post['otp']) != 6) {
						$this->setError("Please enter a valid OTP.");
						$isvalid = false;
					}
					else if (!isset($post['new_password']) || empty($post['new_password'])) {
						$this->setError("Please enter a new password.");
						$isvalid = false;
					}

					if ($isvalid) {
						$userModel = new UserModel();
						$user = $userModel->where('phone', $post['phone'])->first();
						if ($user) {
							// Check if OTP is valid
							if ($user['otp'] == $post['otp']) {
								// Check if OTP has expired
								$current_time = strtotime(date('Y-m-d H:i:s'));
								$expiry_time = strtotime($user['otp_expiry']);

								if ($current_time > $expiry_time) {
									$this->setError("OTP has expired. Please request a new one.");
									} else {
									// Reset password
									$userModel->update($user['user_id'], array(
									'otp' => null,
									'otp_expiry' => null,
									'otp_attempts' => 0,
									'password' => password_hash($post['new_password'], PASSWORD_DEFAULT),
									'updated_at' => date('Y-m-d H:i:s')
									));
									$this->setSuccess("Password reset successful");
								}
								} else {
								// Increment OTP attempts
								$attempts = $user['otp_attempts'] + 1;
								$userModel->update($user['user_id'], array('otp_attempts' => $attempts));
								$this->setError("Invalid OTP. Please try again.");
							}
							} else {
							$this->setError("Phone number not found.");
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
					$user = $userModel->where('user_id', $this->_user['id'])->first();
					if ($user) {
						if ($this->isPost()) {
							$post = $this->getPost();
							$isvalid = true;
							if (!isset($post['fcm_token']) || empty($post['fcm_token'])) {
								$this->setError("Please enter FCM token.");
								$isvalid = false;
							}
							if ($isvalid) {
								$userModel->update($user['user_id'], array(
								'fcm_token' => $post['fcm_token'],
								'updated_at' => date("Y-m-d H:i:s"),
								));
								$this->setSuccess("FCM token set successfully");
							}
							} else {
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
						$userModel->update($this->_user['id'], array('fcm_token' => ''));
						$sessionModel = new SessionModel();
						if(isset($this->_session['session_id'])){
							$sessionModel->update($this->_session['session_id'], array(
								'status' => 0,
								'logged_out' => date("Y-m-d H:i:s"),
							));
						}

						// Record logout time in attendance
						$attendanceModel = new UserAttendanceModel();
						$attendanceModel->recordLogout($this->_user['id'], date('Y-m-d'));

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
					$userModel = new UserModel();
					if($this->isPost()){
						$post = esc($this->getPost());
						$isvalidrequest = true;
						$errors = "";

						if (!isset($post['full_name']) || empty($post['full_name'])) {
							$this->setError("Please enter full name.");
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

							if(in_array($file_ext,$extensions) === false){
								$errors = "Extension not allowed, please choose a JPEG or PNG file.";
							}
							if($file_size > 2097152){
								$errors = 'File size must be exactly 2 MB or less';
							}
							if(empty($errors) == true){
								// Use JWT for file naming instead of md5
								$jwt = new JwtLib();
								$new_file_name = bin2hex(random_bytes(16));
								$upload_path = "assets/images/users/".$new_file_name.'.'.$file_ext;
								move_uploaded_file($file_tmp, $upload_path);
								$post['profile_photo'] = $upload_path;
							}
						}

						if($isvalidrequest){
							if($errors){
								$this->setError($errors);
							}
							else{
								$user = $userModel->findByID($this->_user['id']);
								$values = array(
								'full_name' => $post['full_name'],
								'email' => isset($post['email']) ? $post['email'] : $user['email'],
								'phone' => isset($post['phone']) ? $post['phone'] : $user['phone'],
								'language' => isset($post['language']) ? $post['language'] : $user['language'],
								'profile_photo' => isset($post['profile_photo']) ? $post['profile_photo'] : $user['profile_photo'],
								'updated_by' => $this->_user['id'],
								'updated_at' => date("Y-m-d H:i:s")
								);

								$userModel->update($this->_user['id'], $values);
								$this->setSuccess('Profile updated successfully');

								// Return updated user data
								$updatedUser = $userModel->findByID($this->_user['id']);
								$this->setOutput($updatedUser);
							}
						}
						} else {
						// GET request - return user profile
						$user = $userModel->findByID($this->_user['id']);
						if($user){
							// Add full URL to profile photo if exists
							if(!empty($user['profile_photo'])){
								$user['profile_photo'] = site_url().$user['profile_photo'];
							}
							$this->setSuccess('Profile retrieved successfully');
							$this->setOutput($user);
							} else {
							$this->setError('User not found');
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

		public function verify_otp()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->isPost()) {
					$post = esc($this->getPost());
					$isvalid = true;
					if (!isset($post['phone']) || empty($post['phone'])) {
						$isvalid = false;
						$this->setError('Please enter your phone.');
					}
					else if (!isset($post['otp']) || empty($post['otp'])) {
						$isvalid = false;
						$this->setError('Please enter the OTP.');
					}
					if ($isvalid) {
						$userModel = new UserModel();
						$user = $userModel->where('phone', $post['phone'])->first();
						if ($user && (int)$user['status'] === 1) {
							// Validate OTP
							if ($post['otp'] == $user['otp']) {
								if (!empty($user['otp_expiry']) && strtotime($user['otp_expiry']) < time()) {
									$this->setError('OTP expired. Please request a new OTP.');
								} elseif (isset($user['otp_attempts']) && (int)$user['otp_attempts'] >= 3) {
									$this->setError('OTP expired. Please request a new OTP.');
								} else {
									$jwt = new JwtLib();
									$sessionToken = $jwt->generateToken(array('phone' => $user['phone']),$this->AppConfig->jwt_expiry);
									$sessionModel = new SessionModel();
									$sessionModel->insert(array(
									'user_id' => $user['user_id'],
									'session_token' => $sessionToken,
									'status' => 1,
									'logged_in' => date("Y-m-d H:i:s"),
									));

									$userModel->update($user['user_id'], array(
										'otp' => '',
										'otp_expiry' => '',
										'otp_attempts' => 0,
										'last_login_at' => date("Y-m-d H:i:s"),
										'last_login_ip' => $this->request->getIPAddress(),
									));

									$sessionData = $this->sessionData($user);
									$data = array();
									$data[] = array(
										'id' => $sessionData['id'],
										'name' => $sessionData['name'],
										'email' => $sessionData['email'],
										'phone' => $sessionData['phone'],
										'role_id' => $sessionData['role_id'],
										'status' => $sessionData['status']
									);
									$this->setSuccess('Login Successfully');
									$this->setOutput($sessionToken, 'sessionToken');
									$this->setOutput(array('sessionData' => $data));
								}
							} else {
								// Increment attempts on invalid OTP
								$userModel->update($user['user_id'], array(
									'otp_attempts' => isset($user['otp_attempts']) ? ((int)$user['otp_attempts'] + 1) : 1,
								));
								$this->setError('Invalid OTP.');
							}
						} else {
							$this->setError('Invalid user or inactive status.');
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
	}