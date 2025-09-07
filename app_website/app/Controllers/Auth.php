<?php namespace App\Controllers;

	use App\Models\UserModel;
	use App\Models\UserRoleModel;
	use App\Models\UserPublicModel;
	use App\Models\UserLoginLogoutModel;
	use App\Models\UserAttendanceModel;
	use App\Models\SessionModel;

	use App\Libraries\JwtLib;

	class Auth extends BaseController
	{
		public function login()
		{
			$jwt = new JwtLib();
			if ($this->isUserLoggedIn()) {
				return redirect()->route('/');
			}
			if ($this->isPost()) {

				$post = $this->getPost();
				$isvalidrequest = true;
				$errors = "";

				if ((!isset($post['mobile_number']) || empty($post['mobile_number']))) {
					$errors = 'Incorrect mobile number.';
					$isvalidrequest = false;
				} else if ((!isset($post['password']) || empty($post['password']))) {
					$errors = 'Incorrect password.';
					$isvalidrequest = false;
				} else if ((!isset($post['captcha']) || empty($post['captcha']))) {
					$errors = 'Incorrect captcha.';
					$isvalidrequest = false;
				}

				/* check captcha */
				$realCaptcha = session()->get('captcha_code');
				if ($realCaptcha != $post['captcha']) {
					$errors = 'Invalid Captcha.';
					$isvalidrequest = false;
				}

				if (!validateCSRF($this->request)) {
					$errors = 'Invalid CSRF token.';
					$isvalidrequest = false;
				}
				if ($isvalidrequest) {
					$userModel = new UserModel();
					$user = $userModel->where('phone', $post['mobile_number'])->where('status', 1)->first();
					if ($user) {
						if($post['user_type_id'] != 11){
							if ($user && (password_verify($post['password'], $user['password_hash']))) {
								if($this->AppConfig->twoFactorAuth['enabled']){
									if(sendOtp($user['user_id'])){
										return redirect()->to('verify-otp/'.$user['code']);
									} else {
										$errors = "Error while sending the OTP. Please try again.";
									}
								} else {
									$sessionToken = $jwt->generateToken(array('phone' => $user['phone']),$this->AppConfig->jwt_expiry);

									$sessionModel = new SessionModel();

									if($this->AppConfig->single_login){
										$sessionModel->set('status', 0)->where('user_id', $user['user_id'])->update();
									}
									$sessionModel->insert(array(
										'session_token' => $sessionToken,
										'logged_in' => date("Y-m-d H:i:s"),
										'logged_out' => null,
										'user_id' => $user['user_id'],
										'status' => 1
									));

									$param = array();
									$userModel->update($user['user_id'],array(
										'last_login_at' => date("Y-m-d H:i:s"),
										'last_login_ip' => $this->request->getIPAddress()
									));

									// Log login action in UserLoginLogoutModel
									$userLoginLogoutModel = new UserLoginLogoutModel();
									$userLoginLogoutModel->insert(array(
										'user_id' => $user['user_id'],
										'action' => 'login',
										'date_time' => date("Y-m-d H:i:s"),
										'device_ip' => $this->request->getIPAddress()
									));

									// Record login in UserAttendanceModel
									$userAttendanceModel = new UserAttendanceModel();
									$userAttendanceModel->recordLogin($user['user_id'], date("Y-m-d"));

									$this->session->set('user_id', $user['user_id']);
									$this->session->set('center_id', isset($post['center_id']) ? $post['center_id'] : 0);
									$this->session->set('session_token', $sessionToken);

									// Set user language preference
									$userLanguage = $user['language'] ?? 'en';
									$this->session->set('user_language', $userLanguage);

									return redirect()->route('/');
								}

								} else {
								$errors = 'Incorrect email or password.';
							}
						} else {
							$errors = 'This user type is not allowed to login.';
						}
						} else {
						$errors = 'Incorrect User or status is not active.';
					}
				}
			}
			$userRoleModel = new UserRoleModel();
			$userRoles = $userRoleModel->findAll();

			$formdata = array(
            'csrftoken' => md5(uniqid(rand(), true)),
            'mobile_number' => isset($post['mobile_number']) ? $post['mobile_number'] : '8788786675',
			'password' => isset($post['password']) ? $post['password'] : 'Admin@123',
			'user_type_id' => isset($post['user_type_id']) ? $post['user_type_id'] : '1',
			'center_id' => isset($post['center_id']) ? $post['center_id'] : '1',
            'errors' => (isset($errors) && !empty($errors)) ? $errors : '',
			);

			$this->setData('userRoles', $userRoles);
			$this->setData('formdata', $formdata);
			$this->pageTitle('Login');
			$this->bodyClass('login');
			return view('auth/login', $this->viewdata);
		}

		public function register()
		{
			$userPublicModel = new UserPublicModel();
			$user_type_id = 5;
			if ($this->isPost()) {
				$post = esc($this->getPost());
				$isvalidrequest = true;
				$errors = "";
				if (!isset($post['full_name']) || empty($post['full_name'])) {
					$errors = "Please enter full name.";
					$isvalidrequest = false;
				}
				else if ($isvalidrequest && (!isset($post['mobile_number']) || empty($post['mobile_number'])) || !preg_match('/^\d{10}$/', $post['mobile_number'])) {
					$errors = "Invalid mobile number. It must be exactly 10 digits.";
					$isvalidrequest = false;
				}
				else if ($isvalidrequest && (!isset($post['email']) || empty($post['email']))) {
					$errors = "Please enter email.";
					$isvalidrequest = false;
				}
				else if ($isvalidrequest && (!isset($post['password']) || empty($post['password']))) {
					$errors = "Please enter password.";
					$isvalidrequest = false;
				}
				if (!validateCSRF($this->request)) {
					$errors = 'Invalid CSRF token.';
					$isvalidrequest = false;
				}
				if ($isvalidrequest) {
						$code = GetUserCode();
						$user_id = $userPublicModel->insert(array(
						'code' => $code,
						'user_type_id' => 11,
						'full_name' => $post['full_name'],
						'email' => $post['email'],
						'mobile_number' => $post['mobile_number'],
						'password' => password_hash($post['password'], PASSWORD_DEFAULT),
						'created_at' => date("Y-m-d H:i:s"),
						'updated_at' => date("Y-m-d H:i:s")
						));
						//echo $userPublicModel->getLastQuery()->getQuery();exit;
						if($user_id > 0){
							$user_id = $userPublicModel->update($user_id,array(
								'status' => 1,
							));
							return redirect()->to('welcome/'.$code);
							} else {
							$errors = "Error while creating the user. Please try again.";
						}
				}
			}
			$formdata = array(
			'errors' => isset($errors) ? $errors : '',
			'full_name' => isset($post['full_name']) ? $post['full_name'] : '',
			'email' => isset($post['email']) ? $post['email'] : '',
			'mobile_number' => isset($post['mobile_number']) ? $post['mobile_number'] : '',
			'password' => isset($post['password']) ? $post['password'] : '',
			);

			$this->setData('formdata', $formdata);
			$this->pageName('Register');
			$this->pageTitle('Register');
			return view('auth/register', $this->viewdata);

		}

		public function welcome($code)
		{
			$userPublicModel = new UserPublicModel();
			$user = $userPublicModel->where('code', $code)->first();
			if ($user) {
				$this->setData('user', $user);
				return view('auth/welcome', $this->viewdata);
			} else {
				return redirect()->route('login');
			}
		}

		public function verifyOtp($code)
		{
			$jwt = new JwtLib();
			if ($this->isPost()) {
				$post = esc($this->getPost());
				$userModel = new UserModel();
				$user = $userModel->where('code', $code)->first();
				if ($user) {
					if($post['otp'] == $user['otp']){
						if($user['otp_expiry'] < date("Y-m-d H:i:s")){
							$this->session->setFlashdata('flashmessage', array('OTP expired. Please try again.','danger'));
							return redirect()->to('verify-otp/'.$code);
						} else if($user['otp_attempts'] >= 3){
							$this->session->setFlashdata('flashmessage', array('OTP expired. Please try again.','danger'));
							return redirect()->to('verify-otp/'.$code);
						}
						$sessionToken = $jwt->generateToken(array('phone' => $user['phone']),$this->AppConfig->jwt_expiry);

						$sessionModel = new SessionModel();
						$sessionModel->insert(array(
							'session_token' => $sessionToken,
							'logged_in' => date("Y-m-d H:i:s"),
							'logged_out' => null,
							'user_id' => $user['user_id'],
							'status' => 1
						));

						$userModel->update($user['user_id'], array(
							'otp' => '',
							'otp_expiry' => '',
							'otp_attempts' => 0,
							'last_login_at' => date("Y-m-d H:i:s"),
							'last_login_ip' => $this->request->getIPAddress()
						));
						$this->session->set('user_id', $user['user_id']);
						$this->session->set('center_id', 0);
						$this->session->set('session_token', $sessionToken);

						// Set user language preference
						$userLanguage = $user['language'] ?? 'en';
						$this->session->set('user_language', $userLanguage);

						return redirect()->route('/');
					}
					$userModel->update($user['user_id'], array(
						'otp_attempts' => $user['otp_attempts'] + 1
					));
					$this->session->setFlashdata('flashmessage', array('Invalid OTP.','danger'));
					return redirect()->to('verify-otp/'.$code);
				} else {
					return redirect()->route('login');
				}
			} else {
				$this->setData('code', $code);
				return view('auth/verify-otp', $this->viewdata);
			}
		}

		public function forgotpassword($resetkey = "")
		{
			if (!$this->isUserLoggedIn()) {
				if (!empty($resetkey)) {
					$this->setData('formtype', 'resetpassword');
					$resetpasswordstatus = array('success' => false, 'heading' => "Reset Password", 'message' => "");
					$userModel = new UserModel();
					$user = $userModel->where('resetpassword_token', $resetkey)->first();
					if ($user && (strtotime(date("Y-m-d H:i:s")) <= strtotime('+12 hours', strtotime($user['resetpassword_sent_at'])))) {
						if ($this->isPost()) {
							$post = $this->getPost();
							$isvalidrequest = true;
							$errors = "";
							$csrftoken = $this->session->get('ftadmin_resetpassword_csrftoken');
							$this->session->remove('ftadmin_resetpassword_csrftoken');
							if (!isset($post['csrftoken']) || empty($post['csrftoken']) || $post['csrftoken'] !== $csrftoken) {
								$errors = 'Security token missing or invalid.';
								$isvalidrequest = false;
								} else {
								if ($isvalidrequest && (!isset($post['password']) || empty($post['password']))) {
									$errors = "Please enter the password.";
									$isvalidrequest = false;
									} elseif ($isvalidrequest && (!isset($post['confirmpassword']) || empty($post['confirmpassword']))) {
									$errors = "Please enter confirm password.";
									$isvalidrequest = false;
									} elseif ($isvalidrequest && ($post['confirmpassword'] !== $post['password'])) {
									$errors = "Password and Confirm Password not matched.";
									$isvalidrequest = false;
								}
							}
							if ($isvalidrequest) {
								$userModel->update($user['user_id'], array(
                                'password' => password_hash($post['password'], PASSWORD_DEFAULT),
                                'resetpassword_token' => '',
                                'resetpassword_sent_at' => null,
								));
								$resetpasswordstatus['success'] = true;
								$resetpasswordstatus['heading'] = 'Password Changed!';
								$resetpasswordstatus['message'] = "Your password has been changed successfully.<br>Use your new password to log in.";
							}
						}
						$image = !empty($user['image']) ? $user['image'] : '';
						if (!empty($image)) {
							$image = filter_var($image, FILTER_VALIDATE_URL) ? $image : root_url('data/user/' . $image);
							} else {
							$image = site_url('assets/img/user.png');
						}
						$formdata = array(
                        'csrftoken' => md5(uniqid(rand(), true)),
                        'resettoken' => $user['resetpassword_token'],
                        'name' => $user['name'],
                        'image' => $image,
                        'errors' => isset($errors) ? $errors : '',
						);
						$this->session->set('ftadmin_resetpassword_csrftoken', $formdata['csrftoken']);
						$this->setData('formdata', $formdata);
						} else {
						$resetpasswordstatus['message'] = 'Either the link had already expired or this link was already used to change the password. You can request a new reset password link <a href="' . site_url('/password/forgot') . '">here</a>.';
					}
					$this->setData('resetpasswordstatus', $resetpasswordstatus);
					$this->pageTitle('Reset Password');
					$this->pageName('resetpassword');
					$this->bodyClass('resetpassword');
					} else {
					$this->setData('formtype', 'forgotpassword');
					$forgotpasswordstatus = $this->session->getTempdata('forgotpasswordstatus');
					if (!isset($forgotpasswordstatus['email']) || empty($forgotpasswordstatus['email'])) {
						if ($this->isPost()) {
							$post = $this->getPost();
							$isvalidrequest = true;
							$errors = "";
							$csrftoken = $this->session->get('ftadmin_forgotpassword_csrftoken');
							$this->session->remove('ftadmin_forgotpassword_csrftoken');
							if (!isset($post['csrftoken']) || empty($post['csrftoken']) || $post['csrftoken'] !== $csrftoken) {
								$errors = 'Security token missing or invalid.';
								$isvalidrequest = false;
								} else {
								if ($isvalidrequest && (!isset($post['email']) || empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL))) {
									$errors = "Please enter a valid email address.";
									$isvalidrequest = false;
								}
							}
							if ($isvalidrequest) {
								$userModel = new userModel();
								$admin = $userModel->where('email', $post['email'])->first();
								if ($admin) {
									$resetkey = md5(uniqid(rand(), true));
									$message = view('templates/emails/resetpassword', array(
                                    'name' => $admin['name'],
                                    'reset_link' => site_url('/password/reset/' . $resetkey),
									));
									$email = \Config\Services::email();
									$email->clear(true);
									$email->setFrom($this->AppConfig->appEmails['admin'], $this->AppConfig->appName);
									$email->setTo($admin['email'], $admin['name']);
									$email->setSubject('Reset password instructions');
									$email->setMessage($message);
									if ($email->send()) {
										$userModel->update($admin['user_id'], array(
                                        'resetpassword_token' => $resetkey,
                                        'resetpassword_sent_at' => date('Y-m-d H:i:s'),
										));
										$this->session->setTempdata('forgotpasswordstatus', array('name' => $admin['name'], 'email' => $admin['email']), 60);
										return redirect()->route('password/forgot');
										} else {
										$errors = 'Unable to submit reset password request. Failed to send email, Please try again later.';
									}
									} else {
									$errors = 'Email address not found.';
								}
							}
						}
						$formdata = array(
                        'csrftoken' => md5(uniqid(rand(), true)),
                        'email' => isset($post['email']) ? $post['email'] : '',
                        'errors' => isset($errors) ? $errors : '',
						);
						$this->session->set('ftadmin_forgotpassword_csrftoken', $formdata['csrftoken']);
						$this->setData('formdata', $formdata);
					}
					$this->setData('forgotpasswordstatus', $forgotpasswordstatus);
					$this->pageTitle('Forgot Password');
					$this->pageName('forgotpassword');
					$this->bodyClass('forgotpassword');
				}
				$this->setData('statusclass', (isset($forgotpasswordstatus['email']) && !empty($forgotpasswordstatus['email']) || isset($resetpasswordstatus['message']) && !empty($resetpasswordstatus['message'])) ? ' auth-status' : '');
				return view('auth/forgotpassword', $this->viewdata);
				} else {
				return redirect()->route('/');
			}
		}

		public function logout()
		{
			if ($this->isUserLoggedIn()) {
				if ($this->session->has('user_id')) {
					$user_id = $this->session->get('user_id');
					// Log logout action in UserLoginLogoutModel
					$userLoginLogoutModel = new UserLoginLogoutModel();
					$userLoginLogoutModel->insert(array(
						'user_id' => $user_id,
						'action' => 'logout',
						'date_time' => date("Y-m-d H:i:s"),
						'device_ip' => $this->request->getIPAddress()
					));

					// Record logout in UserAttendanceModel
					$userAttendanceModel = new UserAttendanceModel();
					$userAttendanceModel->recordLogout($user_id, date("Y-m-d"));

					if ($this->session->has('user_id')) {
						$sessionModel = new SessionModel();
						$session = $sessionModel->findByToken($this->session->get('session_token'));
						if($session){
							$sessionModel->update($session['session_id'], array(
								'status' => 0,
								'logged_out' => date("Y-m-d H:i:s")
							));
						}

						$this->session->remove('session_token');
					}
					$this->session->remove('user_id');
				}
				$this->session->setFlashdata('flashmessage',array('Signed out successfully.','success'));
			}
			return redirect()->route('login');
		}

		public function profile()
		{
			if ($this->isUserLoggedIn()) {
				$userModel = new UserModel();
				$user = $userModel->findByID($this->_user['id']);

				if ($this->isPost()) {
					$post = $this->getPost();
					$action = $post['action'] ?? '';

					if ($action === 'update_profile') {
						return $this->updateProfile($post);
					} elseif ($action === 'change_password') {
						return $this->changePassword($post);
					}
				}

				$formdata = array(
					'id' => $user['user_id'],
					'full_name' => $user['full_name'],
					'email' => $user['email'],
					'phone' => $user['phone'],
					'role' => $user['role'],
					'language' => $user['language'] ?? 'en',
					'profile_photo' => $user['profile_photo'] ?? '',
					'last_login_at' => $user['last_login_at'] ?? '',
					'last_login_ip' => $user['last_login_ip'] ?? '',
					'status' => $user['status'] ?? 0,
					'created_at' => $user['created_at'] ?? '',
					'updated_at' => $user['updated_at'] ?? '',
					'csrftoken' => md5(uniqid(rand(), true))
				);

				$this->setData('formdata', $formdata);
				$this->pageTitle('Profile');
				$this->pageName('profile');
				return view('auth/profile', $this->viewdata);
			} else {
				return redirect()->route('login');
			}
		}

		private function updateProfile($post)
		{
			if (!validateCSRF($this->request)) {
				$this->session->setFlashdata('flashmessage', array('Invalid CSRF token.', 'danger'));
				return redirect()->to('profile');
			}

			$isvalidrequest = true;
			$errors = "";

			// Validate full name
			if (!isset($post['full_name']) || empty($post['full_name'])) {
				$errors = "Please enter full name.";
				$isvalidrequest = false;
			}

			// Validate language
			if (!isset($post['language']) || empty($post['language'])) {
				$errors = "Please select language.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$userModel = new UserModel();
				$updateData = array(
					'full_name' => esc($post['full_name']),
					'language' => esc($post['language']),
					'updated_at' => date("Y-m-d H:i:s"),
					'updated_by' => $this->_user['id']
				);

				// Handle profile photo upload with thumbnail and large image generation
				if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
					$file = $_FILES['profile_photo'];

					// Validate file type
					$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
					$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

					if (in_array($fileExt, $allowedTypes)) {
						// Validate file size (max 10MB)
						$maxFileSize = 10 * 1024 * 1024;
						if ($file['size'] <= $maxFileSize) {
							// Create upload directories
							$largeDir = FCPATH . 'uploads/users/large';
							$thumbDir = FCPATH . 'uploads/users/thumb';

							if (!is_dir($largeDir)) {
								mkdir($largeDir, 0755, true);
							}
							if (!is_dir($thumbDir)) {
								mkdir($thumbDir, 0755, true);
							}

							// Generate unique filename
							$timestamp = time();
							$randomString = md5(uniqid(rand(), true));
							$fileName = 'user_' . $timestamp . '_' . $randomString . '.' . $fileExt;

							try {
								// Use ImageResize library for processing
								$imageresize = new \App\Libraries\ImageResize($file['tmp_name']);

								// Set quality settings
								$imageresize->quality_jpg = 90;
								$imageresize->quality_png = 8;
								$imageresize->quality_webp = 90;

								// Get image sizes from config
								$imageSizes = $this->AppConfig->imageSizes ?? ['large' => [800, 600], 'thumb' => [340, 255]];

								// Resize and save large image
								$imageresize->resizeToBestFit($imageSizes['large'][0], $imageSizes['large'][1]);

								if ($imageresize->save($largeDir . '/' . $fileName)) {
									// Create thumbnail
									$thumbResize = new \App\Libraries\ImageResize($largeDir . '/' . $fileName);
									$thumbResize->quality_jpg = 85;
									$thumbResize->quality_png = 7;
									$thumbResize->quality_webp = 85;

									$thumbResize->resizeToBestFit($imageSizes['thumb'][0], $imageSizes['thumb'][1]);
									$imagebase64 = $thumbResize->getImageAsString();

									if (file_put_contents($thumbDir . '/' . $fileName, $imagebase64)) {
										// Delete old profile photo if exists
										if (!empty($this->_user['profile_photo'])) {
											$this->deleteOldProfilePhoto($this->_user['profile_photo']);
										}

										$updateData['profile_photo'] = $fileName;
									} else {
										// Clean up large image if thumbnail creation fails
										unlink($largeDir . '/' . $fileName);
									}
								}
							} catch (Exception $e) {
								log_message('error', 'Error processing profile photo: ' . $e->getMessage());
								// Continue without photo update
							}
						}
					}
				}

				$result = $userModel->update($this->_user['id'], $updateData);

				if ($result) {
					// Update session data
					$this->_user['name'] = $post['full_name'];
					$this->session->set('user_language', $post['language']);

					$this->session->setFlashdata('flashmessage', array('Profile updated successfully.', 'success'));
				} else {
					$this->session->setFlashdata('flashmessage', array('Error updating profile. Please try again.', 'danger'));
				}
			} else {
				$this->session->setFlashdata('flashmessage', array($errors, 'danger'));
			}

			return redirect()->to('profile');
		}

		private function changePassword($post)
		{
			if (!validateCSRF($this->request)) {
				$this->session->setFlashdata('flashmessage', array('Invalid CSRF token.', 'danger'));
				return redirect()->to('profile');
			}

			$isvalidrequest = true;
			$errors = "";

			// Validate current password
			if (!isset($post['current_password']) || empty($post['current_password'])) {
				$errors = "Please enter current password.";
				$isvalidrequest = false;
			}

			// Validate new password
			if (!isset($post['new_password']) || empty($post['new_password'])) {
				$errors = "Please enter new password.";
				$isvalidrequest = false;
			}

			// Validate confirm password
			if (!isset($post['confirm_password']) || empty($post['confirm_password'])) {
				$errors = "Please enter confirm password.";
				$isvalidrequest = false;
			}

			// Check if passwords match
			if ($post['new_password'] !== $post['confirm_password']) {
				$errors = "New password and confirm password do not match.";
				$isvalidrequest = false;
			}

			if ($isvalidrequest) {
				$userModel = new UserModel();
				$user = $userModel->find($this->_user['id']);

				// Verify current password
				if (!password_verify($post['current_password'], $user['password_hash'])) {
					$errors = "Current password is incorrect.";
					$isvalidrequest = false;
				}

				if ($isvalidrequest) {
					$result = $userModel->update($this->_user['id'], array(
						'password_hash' => password_hash($post['new_password'], PASSWORD_DEFAULT),
						'updated_at' => date("Y-m-d H:i:s"),
						'updated_by' => $this->_user['id']
					));

					if ($result) {
						$this->session->setFlashdata('flashmessage', array('Password changed successfully.', 'success'));
					} else {
						$this->session->setFlashdata('flashmessage', array('Error changing password. Please try again.', 'danger'));
					}
				}
			}

			if (!empty($errors)) {
				$this->session->setFlashdata('flashmessage', array($errors, 'danger'));
			}

			return redirect()->to('profile');
		}

		/**
		 * Delete old profile photo files when updating
		 */
		private function deleteOldProfilePhoto($oldPhoto)
		{
			if (empty($oldPhoto)) {
				return;
			}

			$largePath = FCPATH . 'uploads/users/large/' . $oldPhoto;
			$thumbPath = FCPATH . 'uploads/users/thumb/' . $oldPhoto;

			// Delete old large image
			if (file_exists($largePath)) {
				unlink($largePath);
			}

			// Delete old thumbnail
			if (file_exists($thumbPath)) {
				unlink($thumbPath);
			}
		}

		/**
		 * Remove profile photo
		 */
		public function removeProfilePhoto()
		{
			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to remove profile photo.');
				return $this->response();
			}

			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$userModel = new UserModel();
			$user = $userModel->find($this->_user['id']);

			if (!empty($user['profile_photo'])) {
				// Delete photo files
				$this->deleteOldProfilePhoto($user['profile_photo']);

				// Update database
				$result = $userModel->update($this->_user['id'], array(
					'profile_photo' => '',
					'updated_at' => date("Y-m-d H:i:s"),
					'updated_by' => $this->_user['id']
				));

				if ($result) {
					$this->_user['profile_photo'] = '';
					$this->setSuccess('Profile photo removed successfully.');
				} else {
					$this->setError('Failed to remove profile photo. Please try again.');
				}
			} else {
				$this->setError('No profile photo to remove.');
			}

			return $this->response();
		}

		/**
		 * Get profile photo information
		 */
		public function getProfilePhotoInfo()
		{
			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to access profile photo information.');
				return $this->response();
			}

			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$userModel = new UserModel();
			$user = $userModel->find($this->_user['id']);

			$photoInfo = array();

			if (!empty($user['profile_photo'])) {
				$largePath = FCPATH . 'uploads/users/large/' . $user['profile_photo'];
				$thumbPath = FCPATH . 'uploads/users/thumb/' . $user['profile_photo'];

				$photoInfo = array(
					'filename' => $user['profile_photo'],
					'thumb_url' => site_url('uploads/users/thumb/' . $user['profile_photo']),
					'large_url' => site_url('uploads/users/large/' . $user['profile_photo']),
					'thumb_exists' => file_exists($thumbPath),
					'large_exists' => file_exists($largePath),
					'thumb_size' => file_exists($thumbPath) ? filesize($thumbPath) : 0,
					'large_size' => file_exists($largePath) ? filesize($largePath) : 0,
					'upload_date' => $user['updated_at'] ?? $user['created_at']
				);
			}

			$this->setOutput($photoInfo, 'photo_info');
			$this->setSuccess('Profile photo information retrieved successfully.');
			return $this->response();
		}

		/**
		 * Regenerate thumbnail for profile photo
		 */
		public function regenerateProfilePhotoThumbnail()
		{
			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to regenerate thumbnail.');
				return $this->response();
			}

			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$userModel = new UserModel();
			$user = $userModel->find($this->_user['id']);

			if (empty($user['profile_photo'])) {
				$this->setError('No profile photo found.');
				return $this->response();
			}

			$largePath = FCPATH . 'uploads/users/large/' . $user['profile_photo'];
			$thumbPath = FCPATH . 'uploads/users/thumb/' . $user['profile_photo'];

			if (!file_exists($largePath)) {
				$this->setError('Large image not found.');
				return $this->response();
			}

			try {
				// Get image sizes from config
				$imageSizes = $this->AppConfig->imageSizes ?? ['thumb' => [340, 255]];

				// Create new thumbnail
				$thumbResize = new \App\Libraries\ImageResize($largePath);
				$thumbResize->quality_jpg = 85;
				$thumbResize->quality_png = 7;
				$thumbResize->quality_webp = 85;

				$thumbResize->resizeToBestFit($imageSizes['thumb'][0], $imageSizes['thumb'][1]);
				$imagebase64 = $thumbResize->getImageAsString();

				if (file_put_contents($thumbPath, $imagebase64)) {
					$this->setSuccess('Profile photo thumbnail regenerated successfully.');
				} else {
					$this->setError('Failed to regenerate thumbnail.');
				}
			} catch (Exception $e) {
				log_message('error', 'Error regenerating profile photo thumbnail: ' . $e->getMessage());
				$this->setError('An error occurred while regenerating thumbnail.');
			}

			return $this->response();
		}

		/**
		 * Bulk regenerate thumbnails for all user profile photos (Admin only)
		 */
		public function bulkRegenerateUserThumbnails()
		{
			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to regenerate thumbnails.');
				return $this->response();
			}

			// Check if user is admin (you can modify this condition based on your admin check)
			if (!isset($this->_user['role']) || $this->_user['role'] !== 'admin') {
				$this->setError('Access denied. Admin privileges required.');
				return $this->response();
			}

			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$userModel = new UserModel();
			$allUsers = $userModel->findAll();

			$successCount = 0;
			$errorCount = 0;
			$skippedCount = 0;
			$imageSizes = $this->AppConfig->imageSizes ?? ['thumb' => [340, 255]];

			foreach ($allUsers as $user) {
				if (empty($user['profile_photo'])) {
					$skippedCount++;
					continue;
				}

				$largePath = FCPATH . 'uploads/users/large/' . $user['profile_photo'];
				$thumbPath = FCPATH . 'uploads/users/thumb/' . $user['profile_photo'];

				if (!file_exists($largePath)) {
					$errorCount++;
					continue;
				}

				try {
					// Create new thumbnail
					$thumbResize = new \App\Libraries\ImageResize($largePath);
					$thumbResize->quality_jpg = 85;
					$thumbResize->quality_png = 7;
					$thumbResize->quality_webp = 85;

					$thumbResize->resizeToBestFit($imageSizes['thumb'][0], $imageSizes['thumb'][1]);
					$imagebase64 = $thumbResize->getImageAsString();

					if (file_put_contents($thumbPath, $imagebase64)) {
						$successCount++;
					} else {
						$errorCount++;
					}
				} catch (Exception $e) {
					log_message('error', 'Error regenerating thumbnail for user ID ' . $user['id'] . ': ' . $e->getMessage());
					$errorCount++;
				}
			}

			$result = array(
				'success_count' => $successCount,
				'error_count' => $errorCount,
				'skipped_count' => $skippedCount,
				'total_processed' => count($allUsers)
			);

			$this->setOutput($result, 'bulk_result');
			$this->setSuccess("Bulk thumbnail regeneration completed. Success: {$successCount}, Errors: {$errorCount}, Skipped: {$skippedCount}");
			return $this->response();
		}

		/**
		 * Get user profile photo statistics (Admin only)
		 */
		public function getUserProfilePhotoStats()
		{
			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to access photo statistics.');
				return $this->response();
			}

			// Check if user is admin (you can modify this condition based on your admin check)
			if (!isset($this->_user['role']) || $this->_user['role'] !== 'admin') {
				$this->setError('Access denied. Admin privileges required.');
				return $this->response();
			}

			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$userModel = new UserModel();
			$allUsers = $userModel->findAll();

			$totalUsers = count($allUsers);
			$withPhotos = 0;
			$withoutPhotos = 0;
			$totalPhotoSize = 0;
			$photoTypes = array();

			foreach ($allUsers as $user) {
				if (!empty($user['profile_photo'])) {
					$withPhotos++;

					$largePath = FCPATH . 'uploads/users/large/' . $user['profile_photo'];
					$thumbPath = FCPATH . 'uploads/users/thumb/' . $user['profile_photo'];

					if (file_exists($largePath)) {
						$totalPhotoSize += filesize($largePath);
					}
					if (file_exists($thumbPath)) {
						$totalPhotoSize += filesize($thumbPath);
					}

					$fileExt = strtolower(pathinfo($user['profile_photo'], PATHINFO_EXTENSION));
					$photoTypes[$fileExt] = ($photoTypes[$fileExt] ?? 0) + 1;
				} else {
					$withoutPhotos++;
				}
			}

			$stats = array(
				'total_users' => $totalUsers,
				'with_photos' => $withPhotos,
				'without_photos' => $withoutPhotos,
				'photo_percentage' => $totalUsers > 0 ? round(($withPhotos / $totalUsers) * 100, 2) : 0,
				'total_photo_size_mb' => round($totalPhotoSize / (1024 * 1024), 2),
				'photo_types' => $photoTypes,
				'average_photo_size_kb' => $withPhotos > 0 ? round(($totalPhotoSize / $withPhotos) / 1024, 2) : 0
			);

			$this->setOutput($stats, 'photo_stats');
			$this->setSuccess('User profile photo statistics retrieved successfully.');
			return $this->response();
		}
	}