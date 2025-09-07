<?php namespace App\Controllers;

	use App\Models\UserModel;
	use App\Models\UserRoleModel;
	use App\Models\UserAttendanceModel;
	use App\Models\UserLoginLogoutModel;
	use App\Libraries\ImageResize;

	class User extends BaseController
	{
		public function index()
		{
			if ($this->isUserLoggedIn()) {
				$recordsTotal = intval($this->getParam('recordstotal', 0));
				$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
				$params = array();
				$userModel = new UserModel();
				if ($recordsTotal == 0) {
					$params['count'] = true;
					$recordsTotal = $userModel->search($params);
					unset($params['count']);
				}
				if ($this->request->isAJAX()) {
					$draw = intval($this->getParam('draw', 1));
					$start = intval($this->getParam('start', 0));
					$length = intval($this->getParam('length', 50));
					$sorting = $this->getParam('order', '');
					$filter_keywords = $this->getParam('keywords', '');
					$filter_role = $this->getParam('role', '');
					$filter_status = $this->getParam('status', '');
					$filter_email = $this->getParam('email', '');
					$filter_phone = $this->getParam('phone', '');
					$filter_daterange = $this->getParam('daterange', '');

					$hasfilter = false;

					if (!empty($filter_keywords)) {
						$params['keywords'] = $filter_keywords;
						$hasfilter = true;
					}
					if (!empty($filter_role)) {
						$params['role'] = $filter_role;
						$hasfilter = true;
					}
					if (!empty($filter_status)) {
						$params['status'] = $filter_status == 'active' ? '1' : '0';
						$hasfilter = true;
					}
					if (!empty($filter_email)) {
						$params['email'] = $filter_email;
						$hasfilter = true;
					}
					if (!empty($filter_phone)) {
						$params['phone'] = $filter_phone;
						$hasfilter = true;
					}
					if (!empty($filter_daterange)) {
						// Handle date range format (DD MMM YYYY - DD MMM YYYY)
						if (strpos($filter_daterange, ' - ') !== false) {
							$dates = explode(' - ', $filter_daterange);
							if (count($dates) == 2) {
								$start_date = date('Y-m-d', strtotime(trim($dates[0])));
								$end_date = date('Y-m-d', strtotime(trim($dates[1])));
								$params['start_date'] = $start_date;
								$params['end_date'] = $end_date;
							}
						} else {
							// Single date
							$params['date'] = phpDate($filter_daterange	);
						}
						$hasfilter = true;
					}
					if ($hasfilter) {
						$params['count'] = true;
						$recordsFiltered = $userModel->search($params);
						unset($params['count']);
						} else {
						$recordsFiltered = $recordsTotal;
					}
					$params['limit'] = array('length' => $length, 'offset' => $start);

					$users = $userModel->search($params);
					$data = array();
					if (count($users) > 0) {
						foreach ($users as $k => $u) {
							$status_badge = ($u['status'] == '1') ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
							$data[] = array(
                            'id' => $u['user_id'],
							'image' => '<img src="'.($u['profile_photo'] ? site_url('uploads/users/thumb/'.$u['profile_photo']) : site_url('assets/images/user.png')).'" class="img-thumbnail" alt="Profile Photo"  width="60" height="60">',
							'param1' => $u['full_name'] . "<br> <span class='text-muted'>Email: ".$u['email']."<br> Phone: ".$u['phone']."</span>",
							'param2' => $u['role'] . "<br> <span class='text-muted'>Status: ".$status_badge."</span>",
							'param3' => $u['language'] . "<br> <span class='text-muted'>Last Login: ".($u['last_login_at'] ? applicationDate($u['last_login_at']) : 'Never')."</span>",
							'param4' => applicationDate($u['created_at']),
							'param5' => 'N/A',
                            'actions' => '
									<a href="'. site_url('users/view/'.$u['user_id']).'" class="btn btn-sm btn-soft-success waves-effect waves-light mb-1" title="View Details">
										<i class="ri-eye-line align-middle"></i>
									</a>
									<a href="'. site_url('users/edit/'.$u['user_id']).'" class="btn btn-sm btn-soft-primary waves-effect waves-light mb-1" title="Edit">
										<i class="ri-edit-line align-middle"></i>
									</a>
									<button type="button" class="btn btn-sm btn-soft-warning waves-effect waves-light mb-1" onclick="resetPassword('.$u['user_id'].')" title="Reset Password">
										<i class="ri-lock-line align-middle"></i>
									</button>
									<button type="button" class="btn btn-sm btn-soft-info waves-effect waves-light mb-1" onclick="logoutUser('.$u['user_id'].')" title="Logout">
										<i class="ri-logout-box-line align-middle"></i>
									</button>'.($u['role_id'] == 1 || $u['role_id'] == 2 ? '' : '
									<button type="button" class="btn btn-sm btn-soft-danger waves-effect waves-light mb-1" onclick="deleteRecord('.$u['user_id'].')" title="Delete">
										<i class="ri-delete-bin-line align-middle"></i>
									</button>').'
								',
							);
						}
					}
					return $this->response(array('draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $data));
					} else {

					$userRoleModel = new UserRoleModel();
					$roles = $userRoleModel->findAll();
					$this->setData('roles', $roles);

					$this->setData('filters', array('keywords'=>$this->getParam('keywords', '')));
					$this->pageTitle('Users');
					$this->pageJs('assets/js/custom/users.js?v=s' . $this->AppConfig->jsVersion);
					return view('users/index', $this->viewdata);
				}
				} else {
				return redirect()->route('login');
			}
		}

		public function new()
		{
			if ($this->isUserLoggedIn()) {
				$error = '';
				if ($this->isPost()) {
					$post = esc($this->getPost());

					$isvalidrequest = true;

					if (!isset($post['full_name']) || empty($post['full_name'])) {
						$error = "Please enter full name.";
						$isvalidrequest = false;
					}
					if (!isset($post['email']) || empty($post['email'])) {
						$error = "Please enter email.";
						$isvalidrequest = false;
					}
					if (!isset($post['phone']) || empty($post['phone'])) {
						$error = "Please enter phone number.";
						$isvalidrequest = false;
					}
					if (!isset($post['role_id']) || empty($post['role_id'])) {
						$error = "Please select role.";
						$isvalidrequest = false;
					}
					if (!isset($post['password']) || empty($post['password'])) {
						$error = "Please enter password.";
						$isvalidrequest = false;
					}
					if ($isvalidrequest) {
						$created_at = date('Y-m-d H:i:s');

						$userModel = new UserModel();

						// Check if email already exists
						$existingUser = $userModel->search(['email' => $post['email']]);
						if (!empty($existingUser)) {
							$error = 'Email already exists. Please use a different email.';
							if ($this->request->isAJAX()) {
								$this->setError($error);
								return $this->response();
							}
							return redirect()->to('users/new');
						}

						// Check if phone already exists
						$existingUser = $userModel->search(['phone' => $post['phone']]);
						if (!empty($existingUser)) {
							$error = 'Phone number already exists. Please use a different phone number.';
							if ($this->request->isAJAX()) {
								$this->setError($error);
								return $this->response();
							}
							return redirect()->to('users/new');
						}

						$user_id = $userModel->insert(array(
						'profile_photo' => $post['profile_photo'],
                        'full_name' => $post['full_name'],
						'email' => $post['email'],
						'phone' => $post['phone'],
						'role_id' => $post['role_id'],
						'password_hash' => password_hash($post['password'], PASSWORD_DEFAULT),
						'status' => $post['status']=='active' ? '1' : '0',
						'language' => $post['language'],
                        'created_at' => $created_at,
						'created_by' => $this->_user['id'],
						'updated_at' => $created_at,
						'updated_by' => 0
						));
						if($user_id > 0){
							if ($this->request->isAJAX()) {
								$this->setSuccess('User created successfully!');
								return $this->response();
							}
							return redirect()->to('users/view/'.$user_id);
						} else {
							$error = 'Error creating user. Please try again.';
						}
					}

					if ($this->request->isAJAX()) {
						$this->setError($error);
						return $this->response();
					}
					return redirect()->to('users/new');
				}
				$formdata = array(
                'mode' => 'new',
				'id' => 0,
                'uniqid' => uniqid(true),
				'profile_photo' => '',
                'full_name' => '',
                'email' => '',
				'phone' => '',
				'role_id' => '',
				'password' => '',
				'status' => 'active',
				'language' => 'en',
				);

				$userRoleModel = new UserRoleModel();
				$roles = $userRoleModel->findAll();
				$this->setData('roles', $roles);
				$this->setData('error', $error);

				$this->setData('formdata', $formdata);
				$this->pageTitle('New User');
				return view('users/details', $this->viewdata);
				} else {
				return redirect()->route('login');
			}
		}

		public function edit($id = null)
		{
			if ($this->isUserLoggedIn()) {
				$userModel = new UserModel();
				$user = $userModel->findByID($id);
				$error = '';
				if($user){
					if ($this->isPost()) {
						$post = esc($this->getPost());

						$isvalidrequest = true;

						if (!isset($post['full_name']) || empty($post['full_name'])) {
							$error = "Please enter full name.";
							$isvalidrequest = false;
						}
						if (!isset($post['email']) || empty($post['email'])) {
							$error = "Please enter email.";
							$isvalidrequest = false;
						}
						if (!isset($post['phone']) || empty($post['phone'])) {
							$error = "Please enter phone number.";
							$isvalidrequest = false;
						}
						if (!isset($post['role_id']) || empty($post['role_id'])) {
							$error = "Please select role.";
							$isvalidrequest = false;
						}
						if ($isvalidrequest) {
							$created_at = date('Y-m-d H:i:s');

							// Check if email already exists for other users
							$existingUser = $userModel->search(['email' => $post['email']]);
							if (!empty($existingUser) && $existingUser[0]['user_id'] != $id) {
								$error = 'Email already exists. Please use a different email.';
								if ($this->request->isAJAX()) {
									$this->setError($error);
									return $this->response();
								}
								return redirect()->to('users/edit/'.$id);
							}

							// Check if phone already exists for other users
							$existingUser = $userModel->search(['phone' => $post['phone']]);
							if (!empty($existingUser) && $existingUser[0]['user_id'] != $id) {
								$error = 'Phone number already exists. Please use a different phone number.';
								if ($this->request->isAJAX()) {
									$this->setError($error);
									return $this->response();
								}
								return redirect()->to('users/edit/'.$id);
							}

							$updateData = array(
							'profile_photo' => $post['profile_photo'],
							'full_name' => $post['full_name'],
							'email' => $post['email'],
							'phone' => $post['phone'],
							'role_id' => $post['role_id'],
							'status' => $post['status']=='active' ? '1' : '0'	,
							'language' => $post['language'],
							'updated_at' => $created_at,
							'updated_by' => $this->_user['id']
							);

							// Only update password if provided
							if (!empty($post['password'])) {
								$updateData['password_hash'] = password_hash($post['password'], PASSWORD_DEFAULT);
							}

							$userModel = new UserModel();
							$user_id = $userModel->update($id, $updateData);
							if($user_id > 0){
								if ($this->request->isAJAX()) {
									$this->setSuccess('User updated successfully!');
									return $this->response();
								}
								return redirect()->to('users/view/'.$id);
							} else {
								$error = 'Error updating user. Please try again.';
							}
						}

						if ($this->request->isAJAX()) {
							$this->setError($error);
							return $this->response();
						}
					}
					$formdata = array(
					'mode' => 'edit',
					'id' => $user['user_id'],
					'uniqid' => uniqid(true),
					'profile_photo' => $user['profile_photo'],
					'full_name' => $user['full_name'],
					'email' => $user['email'],
					'phone' => $user['phone'],
					'role_id' => $user['role_id'],
					'password' => '',
					'status' => $user['status']=='1' ? 'active' : 'inactive',
					'language' => $user['language'],
					);

					$userRoleModel = new UserRoleModel();
					$roles = $userRoleModel->findAll();
					$this->setData('roles', $roles);
					$this->setData('error', $error);

					$this->setData('formdata', $formdata);
					$this->pageTitle('Edit User');
					return view('users/details', $this->viewdata);
				} else {
					throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
				}
				} else {
				return redirect()->route('login');
			}
		}

		public function view($id = null)
		{
			if ($this->isUserLoggedIn()) {
				$userModel = new UserModel();
				$user = $userModel->findByID($id);
				if($user){
					$this->setData('user', $user);
					$this->pageTitle('View User Details');
					return view('users/views', $this->viewdata);
				} else {
					throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
				}
			} else {
				return redirect()->route('login');
			}
		}

		public function savePhoto()
		{
			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to save photo.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$post = esc($this->getPost());

			// Check if user exists (optional validation)
			if (isset($post['id']) && !empty($post['id'])) {
				$userModel = new UserModel();
				$user = $userModel->findByID($post['id']);
				if (!$user) {
					$this->setError('User not found.');
					return $this->response();
				}
			}

			// Check if file was uploaded
			if (!isset($_FILES['profilePhoto']) || $_FILES['profilePhoto']['error'] !== UPLOAD_ERR_OK) {
				$this->setError('Please select a valid image file.');
				return $this->response();
			}

			$upload_dir = FCPATH . 'uploads/users/large';
			$thumb_dir = FCPATH . 'uploads/users/thumb';
			$imageSizes = $this->AppConfig->imageSizes;
			$uploaded_images = array();

			// Ensure upload directories exist
			if (!is_dir($upload_dir)) {
				mkdir($upload_dir, 0755, true);
			}
			if (!is_dir($thumb_dir)) {
				mkdir($thumb_dir, 0755, true);
			}

			// Handle single file upload
			$file = $_FILES['profilePhoto'];
			$filetype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
			$filename = md5(uniqid(rand(), true)) . '.' . $filetype;

			// Validate file type
			$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
			if (!in_array($filetype, $allowed_types)) {
				$this->setError('Invalid file type. Please upload JPG, PNG or GIF image.');
				return $this->response();
			}

			// Resize and save large image
			$imageresize = new ImageResize($file['tmp_name']);
			$imageresize->resizeToBestFit($imageSizes['large'][0], $imageSizes['large'][1]);

			if ($imageresize->save($upload_dir . '/' . $filename)) {
				// Create thumbnail
				$imageresize = new ImageResize($upload_dir . '/' . $filename);
				$imageresize->resizeToBestFit($imageSizes['thumb'][0], $imageSizes['thumb'][1]);
				$imagebase64 = $imageresize->getImageAsString();
				file_put_contents($thumb_dir . '/' . $filename, $imagebase64);

				$image[] = array(
					'thumb' => site_url('uploads/users/thumb/' . $filename),
					'large' => site_url('uploads/users/large/' . $filename),
					'filename' => $filename
				);

				$this->setOutput($image, 'images');
				$this->setSuccess('Photo uploaded successfully.');
			} else {
				$this->setError('File could not be uploaded. Please try uploading it again.');
			}

			return $this->response();
		}

		public function resetPassword($id = null)
		{
			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to reset password.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$post = esc($this->getPost());

			if (!isset($post['id']) || empty($post['id'])) {
				$this->setError('User ID is required.');
				return $this->response();
			}

			$userModel = new UserModel();
			$user = $userModel->findByID($post['id']);
			if (!$user) {
				$this->setError('User not found.');
				return $this->response();
			}

			// Generate a random password
			$newPassword = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

			$updateData = array(
				'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
				'updated_at' => date('Y-m-d H:i:s'),
				'updated_by' => $this->_user['id']
			);

			$result = $userModel->update($post['id'], $updateData);
			if ($result) {
				$this->setOutput(array('new_password' => $newPassword), 'response');
				$this->setSuccess('Password reset successfully. New password: ' . $newPassword);
			} else {
				$this->setError('Error resetting password. Please try again.');
			}

			return $this->response();
		}

		public function delete($id = null)
		{
			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to delete user.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$post = esc($this->getPost());

			if (!isset($post['id']) || empty($post['id'])) {
				$this->setError('User ID is required.');
				return $this->response();
			}

			$userModel = new UserModel();
			$user = $userModel->findByID($post['id']);
			if (!$user) {
				$this->setError('User not found.');
				return $this->response();
			}

			// Prevent deleting own account
			if ($post['id'] == $this->_user['id']) {
				$this->setError('You cannot delete your own account.');
				return $this->response();
			}

			/*$result = $userModel->delete($post['id']);
			if ($result) {
				$userAttendanceModel = new UserAttendanceModel();
				$userLoginLogoutModel = new UserLoginLogoutModel();
				$userAttendanceModel->delete($post['id']);
				$userLoginLogoutModel->delete($post['id']);
				$this->setSuccess('User deleted successfully.');
			} else {
				$this->setError('Error deleting user. Please try again.');
			}*/
			$this->setError('User Cant be Deleted.');

			return $this->response();
		}

		public function logout($id = null)
		{
			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to logout user.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}

			$post = esc($this->getPost());

			if (!isset($post['id']) || empty($post['id'])) {
				$this->setError('User ID is required.');
				return $this->response();
			}

			$userModel = new UserModel();
			$user = $userModel->findByID($post['id']);
			if (!$user) {
				$this->setError('User not found.');
				return $this->response();
			}

			// Prevent logging out own account
			if ($post['id'] == $this->_user['id']) {
				$this->setError('You cannot logout your own account.');
				return $this->response();
			}

			// Update user's session token to null
			$updateData = array(
				'updated_at' => date('Y-m-d H:i:s'),
				'updated_by' => $this->_user['id']
			);

			$result = $userModel->update($post['id'], $updateData);
			if ($result) {
				$this->setSuccess('User logged out successfully. Session has been invalidated.');
			} else {
				$this->setError('Error logging out user. Please try again.');
			}

			return $this->response();
		}
		public function logoutUser($id = null)
		{
			if (!$this->request->isAJAX()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}
			if (!$this->isUserLoggedIn()) {
				$this->setError('Please login to logout user.');
				return $this->response();
			}

			if (!$this->isPost()) {
				$this->setError('Invalid Method.');
				return $this->response();
			}
			$post = esc($this->getPost());

			if (!isset($post['id']) || empty($post['id'])) {
				$this->setError('User ID is required.');
				return $this->response();
			}
			$userModel = new UserModel();
			$user = $userModel->findByID($post['id']);
			if (!$user) {
				$this->setError('User not found.');
				return $this->response();
			}
			$updateData = array(
				'updated_at' => date('Y-m-d H:i:s'),
				'updated_by' => $this->_user['id']
			);
			$result = $userModel->update($post['id'], $updateData);
			if ($result) {
				$this->setSuccess('User logged out successfully. Session has been invalidated.');
			} else {
				$this->setError('Error logging out user. Please try again.');
			}

			return $this->response();
		}

		public function exportExcel()
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}

			if (!$this->isPost()) {
				return redirect()->route('users');
			}

			$post = esc($this->getPost());
			$params = array();

			// Apply filters if provided
			if (!empty($post['keywords'])) {
				$params['keywords'] = $post['keywords'];
			}
			if (!empty($post['role'])) {
				$params['role'] = $post['role'];
			}
			if (!empty($post['status'])) {
				$params['status'] = $post['status'];
			}
			if (!empty($post['email'])) {
				$params['email'] = $post['email'];
			}
			if (!empty($post['phone'])) {
				$params['phone'] = $post['phone'];
			}
			if (!empty($post['date'])) {
				// Handle date range format (DD MMM YYYY - DD MMM YYYY)
				if (strpos($post['date'], ' - ') !== false) {
					$dates = explode(' - ', $post['date']);
					if (count($dates) == 2) {
						$start_date = date('Y-m-d', strtotime(trim($dates[0])));
						$end_date = date('Y-m-d', strtotime(trim($dates[1])));
						$params['start_date'] = $start_date;
						$params['end_date'] = $end_date;
					}
				} else {
					// Single date
					$params['date'] = phpDate($post['date']);
				}
			}

			// Get all users with filters applied
			$userModel = new UserModel();
			$users = $userModel->search($params);

			// Create Excel file
			$excel = new \App\Libraries\ExcelExporter();
			$spreadsheet = $excel->spreadsheet;
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setTitle('Users Report');

			// Set headers
			$headers = [
				'A1' => 'ID',
				'B1' => 'Full Name',
				'C1' => 'Email',
				'D1' => 'Phone',
				'E1' => 'Role',
				'F1' => 'Status',
				'G1' => 'Language',
				'H1' => 'Last Login',
				'I1' => 'Created Date',
				'J1' => 'Updated Date'
			];

			// Apply header styling
			$headerStyle = [
				'font' => [
					'bold' => true,
					'color' => ['rgb' => 'FFFFFF']
				],
				'fill' => [
					'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
					'startColor' => ['rgb' => '4472C4']
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
					'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
				],
				'borders' => [
					'allBorders' => [
						'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
						'color' => ['rgb' => '000000']
					]
				]
			];

			// Set headers and apply styling
			foreach ($headers as $cell => $value) {
				$sheet->setCellValue($cell, $value);
				$sheet->getStyle($cell)->applyFromArray($headerStyle);
			}

			// Set data
			$row = 2;
			foreach ($users as $user) {
				$status = ($user['status'] == '1') ? 'Active' : 'Inactive';
				$lastLogin = $user['last_login_at'] ? applicationDate($user['last_login_at']) : 'Never';
				$createdDate = applicationDate($user['created_at']);
				$updatedDate = $user['updated_at'] ? applicationDate($user['updated_at']) : 'N/A';

				$sheet->setCellValue('A' . $row, $user['user_id']);
				$sheet->setCellValue('B' . $row, $user['full_name']);
				$sheet->setCellValue('C' . $row, $user['email']);
				$sheet->setCellValue('D' . $row, $user['phone']);
				$sheet->setCellValue('E' . $row, $user['role']);
				$sheet->setCellValue('F' . $row, $status);
				$sheet->setCellValue('G' . $row, $user['language']);
				$sheet->setCellValue('H' . $row, $lastLogin);
				$sheet->setCellValue('I' . $row, $createdDate);
				$sheet->setCellValue('J' . $row, $updatedDate);

				// Apply border to data row
				$sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
					'borders' => [
						'allBorders' => [
							'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
							'color' => ['rgb' => '000000']
						]
					]
				]);

				$row++;
			}

			// Auto-size columns
			foreach (range('A', 'J') as $column) {
				$sheet->getColumnDimension($column)->setAutoSize(true);
			}

			// Generate filename with timestamp and filters
			$filename = 'users_report_' . date('Y-m-d_H-i-s');
			if (!empty($post['keywords']) || !empty($post['role']) || !empty($post['status']) || !empty($post['email']) || !empty($post['phone']) || !empty($post['date'])) {
				$filename .= '_filtered';
			}
			$filename .= '.xlsx';

			// Download the file
			$excel->download($filename);
		}
	}
