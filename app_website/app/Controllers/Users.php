<?php namespace App\Controllers;

	use App\Models\UserModel;
	use App\Models\UserTypeModel;
	use App\Models\VendorModel;
	use App\Models\GodownModel;
	use App\Models\ShopModel;
	use App\Libraries\ExcelExporter;

	class Users extends BaseController
	{
		public function index()
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}

			$userModel = new UserModel();
			$recordsTotal = intval($this->getParam('recordstotal', 0));
			$recordsFiltered = intval($this->getParam('recordsfiltered', 0));
			$params = array();

			if ($recordsTotal === 0) {
				$params['count'] = true;
				$recordsTotal = $userModel->search($params);
				unset($params['count']);
			}

			if ($this->request->isAJAX()) {
				$draw = intval($this->getParam('draw', 1));
				$start = intval($this->getParam('start', 0));
				$length = intval($this->getParam('length', 50));
				$sorting = $this->getParam('order', array());
				$filter_keywords = trim($this->getParam('keywords', ''));
				$filter_status = trim($this->getParam('status', ''));

				if ($filter_keywords !== '') {
					$params['keywords'] = $filter_keywords;
				}

				if ($filter_status !== '') {
					$params['status'] = $filter_status;
				}

				if ($filter_keywords !== '' || $filter_status !== '') {
					$countParams = $params;
					$countParams['count'] = true;
					$recordsFiltered = $userModel->search($countParams);
					unset($countParams);
				} else {
					$recordsFiltered = $recordsTotal;
				}

				$params['limit'] = array('length' => $length, 'offset' => $start);

				if (!empty($sorting) && isset($sorting[0]['column'])) {
					$columnIndex = intval($sorting[0]['column']);
					$columnMap = array(
						0 => 'users.full_name',
						1 => 'user_types.type_name',
						2 => 'users.email',
						3 => 'users.phone',
						4 => 'users.status',
						5 => 'users.created_at'
					);
					if (isset($columnMap[$columnIndex])) {
						$params['sort'] = array(
							'column' => $columnMap[$columnIndex],
							'order' => isset($sorting[0]['dir']) && in_array(strtolower($sorting[0]['dir']), array('asc','desc')) ? $sorting[0]['dir'] : 'asc'
						);
					}
				}

				$users = $userModel->search($params);
				//echo $userModel->getLastQuery()->getQuery();exit;
				$data = array();
				if (!empty($users)) {
					foreach ($users as $user) {
						$avatar = !empty($user['profile_photo'])
							? site_url('uploads/users/thumb/' . $user['profile_photo'])
							: site_url('assets/images/user.png');
						$statusClass = ((string) $user['status'] === '1') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-muted';
						$createdAt = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : 'N/A';
						$data[] = array(
							'id' => $user['user_id'],
							'name' => '<div class="d-flex align-items-center">'.
								'<div class="flex-shrink-0 me-3"><img src="'.$avatar.'" alt="'.esc($user['full_name']).'" class="avatar-xs rounded-circle"></div>'.
								'<div><a href="'. site_url('users/view/'.$user['user_id']).'" class="fw-semibold text-reset">'.$user['full_name'].'</a>'.
								'</div></div>',
							'email' => '<a href="mailto:'.$user['email'].'" class="text-reset">'.$user['email'].'</a>',
							'phone' => !empty($user['phone']) ? '<a href="tel:'.$user['phone'].'" class="text-reset">'.$user['phone'].'</a>' : 'N/A',
							'type' => $user['user_type'],
							'status' => '<span class="badge '.$statusClass.' text-uppercase">'.($user['status'] == '1' ? 'Active' : 'Inactive').'</span>',
							'created_at' => $createdAt,
							'actions' => '
								<div class="dropdown text-center">
									<button type="button" class="btn btn-sm btn-soft-info btn-icon fs-14" data-bs-toggle="dropdown" aria-expanded="false">
										<i class="ri-more-2-fill"></i>
									</button>
									<ul class="dropdown-menu dropdown-menu-end">
										<li><a class="dropdown-item" href="'. site_url('users/view/'.$user['user_id']).'"><i class="ri-eye-line align-middle me-1"></i> View</a></li>
										<li><a class="dropdown-item" href="'. site_url('users/edit/'.$user['user_id']).'"><i class="ri-edit-line align-middle me-1"></i> Edit</a></li>
										<li><a class="dropdown-item text-danger" data-action="delete" data-id="'.$user['user_id'].'" data-name="'.$user['full_name'].'" href="javascript:void(0);"><i class="ri-delete-bin-line align-middle me-1"></i> Delete</a></li>
									</ul>
								</div>'
						);
					}
				}
				return $this->response(array('draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered, 'data' => $data));
			}

			$this->setData('filters', array(
				'keywords' => $this->getParam('keywords', ''),
				'status' => $this->getParam('status', '')
			));
			$this->pageTitle('Users');
			$this->pageJs('assets/js/custom/users.js?v=s' . $this->AppConfig->jsVersion);
			return view('users/index', $this->viewdata);
		}

		public function export()
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}

			$userModel = new UserModel();
			$params = array();
			$filter_keywords = trim($this->getParam('keywords', ''));
			$filter_status = trim($this->getParam('status', ''));

			if ($filter_keywords !== '') {
				$params['keywords'] = $filter_keywords;
			}

			if ($filter_status !== '') {
				$params['status'] = $filter_status;
			}

			$users = $userModel->search($params);
			$statusOptions = $this->getStatusOptions();

			$exporter = new ExcelExporter();
			$sheet = $exporter->spreadsheet->getActiveSheet();

			$headers = array('Full Name', 'Email', 'Phone', 'User Type', 'Status', 'Created At');
			$rows = array();

			if (!empty($users)) {
				foreach ($users as $user) {
					$statusKey = strtolower((string) $user['status']);
					$statusLabel = $statusOptions[$statusKey] ?? ($user['status'] == '1' ? 'Active' : 'Inactive');
					$createdAt = !empty($user['created_at']) ? date('Y-m-d H:i:s', strtotime($user['created_at'])) : '';
					$rows[] = array(
						$user['full_name'] ?? '',
						$user['email'] ?? '',
						$user['phone'] ?? '',
						$user['user_type'] ?? '',
						$statusLabel,
						$createdAt
					);
				}
			}

			$sheet->fromArray($headers, null, 'A1');
			if (!empty($rows)) {
				$sheet->fromArray($rows, null, 'A2');
			}

			// Basic formatting
			$sheet->freezePane('A2');
			$sheet->getStyle('A1:F1')->getFont()->setBold(true);
			foreach (range('A', 'F') as $col) {
				$sheet->getColumnDimension($col)->setAutoSize(true);
			}

			$filename = 'users_' . date('Ymd_His') . '.xlsx';
			$exporter->download($filename);
		}

		public function new()
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}

			$error = '';
			$post = array();
			if ($this->isPost()) {
				$post = esc($this->getPost());
				$isvalidrequest = true;
				$userTypeId = isset($post['user_type_id']) ? (int) $post['user_type_id'] : 0;

				if (!isset($post['full_name']) || empty($post['full_name'])) {
					$error = "Please enter full name.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['email']) || empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL))) {
					$error = "Please enter a valid email.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['phone']) || empty($post['phone']))) {
					$error = "Please enter phone number.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['status']) || ($post['status'] !== '1' && $post['status'] !== '0'))) {
					$error = "Please select status.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['user_type_id']) || empty($post['user_type_id']))) {
					$error = "Please select user type.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['password']) || empty($post['password']))) {
					$error = "Please enter password.";
					$isvalidrequest = false;
				} elseif ($isvalidrequest && !$this->isStrongPassword($post['password'])) {
					$error = "Password must be at least 8 characters and include uppercase, lowercase, number and special character.";
					$isvalidrequest = false;
				}

				if ($isvalidrequest && !$this->isValidUserType($post['user_type_id'])) {
					$error = "Selected user type is invalid.";
					$isvalidrequest = false;
				}

				// Extra details validation based on user type
				if ($isvalidrequest && in_array($userTypeId, array(3, 4, 5), true)) {
					if ($userTypeId === 3) {
						if (!isset($post['company_name']) || trim($post['company_name']) === '') {
							$error = "Please enter company name.";
							$isvalidrequest = false;
						}
					} elseif ($userTypeId === 4) {
						if (!isset($post['godown_name']) || trim($post['godown_name']) === '') {
							$error = "Please enter godown name.";
							$isvalidrequest = false;
						}
					} elseif ($userTypeId === 5) {
						if (!isset($post['shop_name']) || trim($post['shop_name']) === '') {
							$error = "Please enter shop name.";
							$isvalidrequest = false;
						} elseif (!isset($post['shop_godown_id']) || (string) $post['shop_godown_id'] === '') {
							$error = "Please select godown for shop.";
							$isvalidrequest = false;
						} else {
							$godownId = (int) $post['shop_godown_id'];
							$godownModel = new GodownModel();
							if (!$godownModel->find($godownId)) {
								$error = "Selected godown is invalid.";
								$isvalidrequest = false;
							}
						}
					}
				}

				if ($isvalidrequest) {
					$userModel = new UserModel();
					$existingUser = $userModel->where('email', $post['email'])->first();
					if ($existingUser) {
						$error = "Email address already exists.";
					} else {
						$created_at = date('Y-m-d H:i:s');
						// crop_square = true, create_thumb = true (needed because UI uses uploads/users/thumb/...)
						$photoUpload = upload_image($_FILES['profile_photo'] ?? null, 'uploads/users', '', array('crop_square' => true, 'create_thumb' => true));
						if (!$photoUpload['success']) {
							$error = $photoUpload['message'];
							$isvalidrequest = false;
						}
					}

					if ($isvalidrequest) {
						$user_id = $userModel->insert(array(
							'full_name' => $post['full_name'],
							'email' => $post['email'],
							'phone' => $post['phone'],
							'status' => isset($post['status']) ? intval($post['status']) : 1,
							'user_type_id' => isset($post['user_type_id']) ? intval($post['user_type_id']) : 1,
							'language' => isset($post['language']) && !empty($post['language']) ? $post['language'] : 'en',
							'profile_photo' => $photoUpload['filename'],
							'password_hash' => password_hash($post['password'], PASSWORD_DEFAULT),
							'created_by' => $this->_user['id'],
							'created_at' => $created_at,
							'updated_at' => $created_at
						));

						if ($user_id > 0) {
							// Save extra details based on user type
							if ($userTypeId === 3) {
								$vendorModel = new VendorModel();
								$vendorModel->insert(array(
									'user_id' => $user_id,
									'company_name' => trim($post['company_name'] ?? ''),
									'gst_number' => trim($post['gst_number'] ?? ''),
									'pan_number' => trim($post['pan_number'] ?? ''),
									'bank_account_details' => trim($post['bank_account_details'] ?? ''),
									'payment_terms' => trim($post['payment_terms'] ?? ''),
									'rating' => (isset($post['rating']) && $post['rating'] !== '') ? $post['rating'] : null,
								));
							} elseif ($userTypeId === 4) {
								$godownModel = new GodownModel();
								$godownModel->insert(array(
									'user_id' => $user_id,
									'godown_name' => trim($post['godown_name'] ?? ''),
									'location' => trim($post['godown_location'] ?? ''),
									'capacity_sqft' => (isset($post['capacity_sqft']) && $post['capacity_sqft'] !== '') ? (int) $post['capacity_sqft'] : 0,
									'contact_person' => trim($post['godown_contact_person'] ?? ''),
									'is_active' => 1,
								));
							} elseif ($userTypeId === 5) {
								$shopModel = new ShopModel();
								$shopModel->insert(array(
									'user_id' => $user_id,
									'shop_name' => trim($post['shop_name'] ?? ''),
									'location' => trim($post['shop_location'] ?? ''),
									'contact_person' => trim($post['shop_contact_person'] ?? ''),
									'godown_id' => (int) ($post['shop_godown_id'] ?? 0),
									'is_active' => 1,
								));
							}
							return redirect()->to('users/view/'.$user_id);
						} else {
							$error = 'Error creating user. Please try again.';
						}
					}
				}
			}

			$formdata = array(
				'mode' => 'new',
				'id' => 0,
				'error' => $error,
				'full_name' => isset($post['full_name']) ? $post['full_name'] : '',
				'email' => isset($post['email']) ? $post['email'] : '',
				'phone' => isset($post['phone']) ? $post['phone'] : '',
				'status' => isset($post['status']) ? $post['status'] : '1',
				'user_type_id' => isset($post['user_type_id']) ? $post['user_type_id'] : '',
				'language' => isset($post['language']) ? $post['language'] : 'en',
				'profile_photo' => '',

				// Vendor fields
				'company_name' => isset($post['company_name']) ? $post['company_name'] : '',
				'gst_number' => isset($post['gst_number']) ? $post['gst_number'] : '',
				'pan_number' => isset($post['pan_number']) ? $post['pan_number'] : '',
				'bank_account_details' => isset($post['bank_account_details']) ? $post['bank_account_details'] : '',
				'payment_terms' => isset($post['payment_terms']) ? $post['payment_terms'] : '',
				'rating' => isset($post['rating']) ? $post['rating'] : '',

				// Godown fields
				'godown_name' => isset($post['godown_name']) ? $post['godown_name'] : '',
				'godown_location' => isset($post['godown_location']) ? $post['godown_location'] : '',
				'capacity_sqft' => isset($post['capacity_sqft']) ? $post['capacity_sqft'] : '',
				'godown_contact_person' => isset($post['godown_contact_person']) ? $post['godown_contact_person'] : '',

				// Shop fields
				'shop_name' => isset($post['shop_name']) ? $post['shop_name'] : '',
				'shop_location' => isset($post['shop_location']) ? $post['shop_location'] : '',
				'shop_contact_person' => isset($post['shop_contact_person']) ? $post['shop_contact_person'] : '',
				'shop_godown_id' => isset($post['shop_godown_id']) ? $post['shop_godown_id'] : '',
			);

			$this->setData('formdata', $formdata);
			$this->setData('statusOptions', $this->getStatusOptions());
			$this->setData('userTypeOptions', $this->getUserTypeOptions());
			$godownModel = new GodownModel();
			$this->setData('godownOptions', $godownModel->getActiveGodowns());
			$this->pageTitle('Add User');
			return view('users/details', $this->viewdata);
		}

		public function edit($id = null)
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}

			$userModel = new UserModel();
			$user = $userModel->findByID($id);
			if (!$user) {
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
			}

			// Load existing extra details for prefilling
			$vendorModel = new VendorModel();
			$godownModel = new GodownModel();
			$shopModel = new ShopModel();

			$vendorDetails = $vendorModel->findByUserID($id);
			$godownList = $godownModel->findByUserID($id);
			$godownDetails = !empty($godownList) ? $godownList[0] : null;
			$shopList = $shopModel->findByUserID($id);
			$shopDetails = !empty($shopList) ? $shopList[0] : null;

			$error = '';
			$post = array();
			if ($this->isPost()) {
				$post = esc($this->getPost());
				$isvalidrequest = true;
				$userTypeId = isset($post['user_type_id']) ? (int) $post['user_type_id'] : (int) ($user['user_type_id'] ?? 0);

				if (!isset($post['full_name']) || empty($post['full_name'])) {
					$error = "Please enter full name.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['email']) || empty($post['email']) || !filter_var($post['email'], FILTER_VALIDATE_EMAIL))) {
					$error = "Please enter a valid email.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['phone']) || empty($post['phone']))) {
					$error = "Please enter phone number.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['status']) || ($post['status'] !== '1' && $post['status'] !== '0'))) {
					$error = "Please select status.";
					$isvalidrequest = false;
				}
				if ($isvalidrequest && (!isset($post['user_type_id']) || empty($post['user_type_id']))) {
					$error = "Please select user type.";
					$isvalidrequest = false;
				}

				if ($isvalidrequest) {
					$duplicateEmail = $userModel->where('email', $post['email'])->where('user_id !=', $id)->first();
					if ($duplicateEmail) {
						$error = "Email address already exists.";
						$isvalidrequest = false;
					}
				}

				// crop_square = true, create_thumb = true (needed because UI uses uploads/users/thumb/...)
				$photoUpload = upload_image($_FILES['profile_photo'] ?? null, 'uploads/users', $user['profile_photo'] ?? '', array('crop_square' => true, 'create_thumb' => true));
				if (!$photoUpload['success']) {
					$error = $photoUpload['message'];
					$isvalidrequest = false;
				}

				// Extra details validation based on user type
				if ($isvalidrequest && in_array($userTypeId, array(3, 4, 5), true)) {
					if ($userTypeId === 3) {
						if (!isset($post['company_name']) || trim($post['company_name']) === '') {
							$error = "Please enter company name.";
							$isvalidrequest = false;
						}
					} elseif ($userTypeId === 4) {
						if (!isset($post['godown_name']) || trim($post['godown_name']) === '') {
							$error = "Please enter godown name.";
							$isvalidrequest = false;
						}
					} elseif ($userTypeId === 5) {
						if (!isset($post['shop_name']) || trim($post['shop_name']) === '') {
							$error = "Please enter shop name.";
							$isvalidrequest = false;
						} elseif (!isset($post['shop_godown_id']) || (string) $post['shop_godown_id'] === '') {
							$error = "Please select godown for shop.";
							$isvalidrequest = false;
						} else {
							$godownId = (int) $post['shop_godown_id'];
							if (!$godownModel->find($godownId)) {
								$error = "Selected godown is invalid.";
								$isvalidrequest = false;
							}
						}
					}
				}

				if ($isvalidrequest && !$this->isValidUserType($post['user_type_id'])) {
					$error = "Selected user type is invalid.";
					$isvalidrequest = false;
				}

				if ($isvalidrequest) {
					$updateData = array(
						'full_name' => $post['full_name'],
						'email' => $post['email'],
						'phone' => $post['phone'],
						'status' => isset($post['status']) ? intval($post['status']) : $user['status'],
						'user_type_id' => isset($post['user_type_id']) ? intval($post['user_type_id']) : $user['user_type_id'],
						'language' => isset($post['language']) && !empty($post['language']) ? $post['language'] : ($user['language'] ?? 'en'),
						'profile_photo' => $photoUpload['filename'],
						'updated_at' => date('Y-m-d H:i:s'),
						'updated_by' => $this->_user['id']
					);

					if (isset($post['password']) && !empty($post['password'])) {
						if (!$this->isStrongPassword($post['password'])) {
							$error = "Password must be at least 8 characters and include uppercase, lowercase, number and special character.";
							$isvalidrequest = false;
						} else {
							$updateData['password_hash'] = password_hash($post['password'], PASSWORD_DEFAULT);
						}
					}

					if ($isvalidrequest) {
						$result = $userModel->update($id, $updateData);
						if ($result) {
							// Save extra details based on user type
							if ($userTypeId === 3) {
								$existingVendor = $vendorModel->where('user_id', $id)->first();
								$vendorData = array(
									'user_id' => $id,
									'company_name' => trim($post['company_name'] ?? ''),
									'gst_number' => trim($post['gst_number'] ?? ''),
									'pan_number' => trim($post['pan_number'] ?? ''),
									'bank_account_details' => trim($post['bank_account_details'] ?? ''),
									'payment_terms' => trim($post['payment_terms'] ?? ''),
									'rating' => (isset($post['rating']) && $post['rating'] !== '') ? $post['rating'] : null,
								);
								if ($existingVendor && isset($existingVendor['vendor_id'])) {
									$vendorModel->update($existingVendor['vendor_id'], $vendorData);
								} else {
									$vendorModel->insert($vendorData);
								}
							} elseif ($userTypeId === 4) {
								$existingGodown = $godownModel->where('user_id', $id)->orderBy('godown_id', 'DESC')->first();
								$godownData = array(
									'user_id' => $id,
									'godown_name' => trim($post['godown_name'] ?? ''),
									'location' => trim($post['godown_location'] ?? ''),
									'capacity_sqft' => (isset($post['capacity_sqft']) && $post['capacity_sqft'] !== '') ? (int) $post['capacity_sqft'] : 0,
									'contact_person' => trim($post['godown_contact_person'] ?? ''),
									'is_active' => 1,
								);
								if ($existingGodown && isset($existingGodown['godown_id'])) {
									$godownModel->update($existingGodown['godown_id'], $godownData);
								} else {
									$godownModel->insert($godownData);
								}
							} elseif ($userTypeId === 5) {
								$existingShop = $shopModel->where('user_id', $id)->orderBy('shop_id', 'DESC')->first();
								$shopData = array(
									'user_id' => $id,
									'shop_name' => trim($post['shop_name'] ?? ''),
									'location' => trim($post['shop_location'] ?? ''),
									'contact_person' => trim($post['shop_contact_person'] ?? ''),
									'godown_id' => (int) ($post['shop_godown_id'] ?? 0),
									'is_active' => 1,
								);
								if ($existingShop && isset($existingShop['shop_id'])) {
									$shopModel->update($existingShop['shop_id'], $shopData);
								} else {
									$shopModel->insert($shopData);
								}
							}
							return redirect()->to('users/view/'.$id);
						} else {
							$error = 'Error updating user. Please try again.';
						}
					}
				}
			}

			$formdata = array(
				'mode' => 'edit',
				'id' => $user['user_id'],
				'error' => $error,
				'full_name' => isset($post['full_name']) ? $post['full_name'] : $user['full_name'],
				'email' => isset($post['email']) ? $post['email'] : $user['email'],
				'phone' => isset($post['phone']) ? $post['phone'] : $user['phone'],
				'status' => isset($post['status']) ? $post['status'] : (string) $user['status'],
				'user_type_id' => isset($post['user_type_id']) ? $post['user_type_id'] : $user['user_type_id'],
				'language' => isset($post['language']) ? $post['language'] : ($user['language'] ?? 'en'),
				'profile_photo' => $user['profile_photo'] ?? '',

				// Vendor fields
				'company_name' => isset($post['company_name']) ? $post['company_name'] : ($vendorDetails['company_name'] ?? ''),
				'gst_number' => isset($post['gst_number']) ? $post['gst_number'] : ($vendorDetails['gst_number'] ?? ''),
				'pan_number' => isset($post['pan_number']) ? $post['pan_number'] : ($vendorDetails['pan_number'] ?? ''),
				'bank_account_details' => isset($post['bank_account_details']) ? $post['bank_account_details'] : ($vendorDetails['bank_account_details'] ?? ''),
				'payment_terms' => isset($post['payment_terms']) ? $post['payment_terms'] : ($vendorDetails['payment_terms'] ?? ''),
				'rating' => isset($post['rating']) ? $post['rating'] : ($vendorDetails['rating'] ?? ''),

				// Godown fields (first active record)
				'godown_name' => isset($post['godown_name']) ? $post['godown_name'] : ($godownDetails['godown_name'] ?? ''),
				'godown_location' => isset($post['godown_location']) ? $post['godown_location'] : ($godownDetails['location'] ?? ''),
				'capacity_sqft' => isset($post['capacity_sqft']) ? $post['capacity_sqft'] : ($godownDetails['capacity_sqft'] ?? ''),
				'godown_contact_person' => isset($post['godown_contact_person']) ? $post['godown_contact_person'] : ($godownDetails['contact_person'] ?? ''),

				// Shop fields (first active record)
				'shop_name' => isset($post['shop_name']) ? $post['shop_name'] : ($shopDetails['shop_name'] ?? ''),
				'shop_location' => isset($post['shop_location']) ? $post['shop_location'] : ($shopDetails['location'] ?? ''),
				'shop_contact_person' => isset($post['shop_contact_person']) ? $post['shop_contact_person'] : ($shopDetails['contact_person'] ?? ''),
				'shop_godown_id' => isset($post['shop_godown_id']) ? $post['shop_godown_id'] : ($shopDetails['godown_id'] ?? ''),
			);

			$this->setData('formdata', $formdata);
			$this->setData('statusOptions', $this->getStatusOptions());
			$this->setData('userTypeOptions', $this->getUserTypeOptions());
			$this->setData('godownOptions', $godownModel->getActiveGodowns());
			$this->pageTitle('Edit User');
			return view('users/details', $this->viewdata);
		}

		public function view($id = null)
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}

			$userModel = new UserModel();
			$user = $userModel->findByID($id);
			if (!$user) {
				throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
			}

			$userTypeId = (int) ($user['user_type_id'] ?? 0);
			$vendorDetails = null;
			$godownList = array();
			$shopList = array();

			if ($userTypeId === 3) {
				$vendorModel = new VendorModel();
				$vendorDetails = $vendorModel->findByUserID($id);
			} elseif ($userTypeId === 4) {
				$godownModel = new GodownModel();
				$godownList = $godownModel->findByUserID($id);
			} elseif ($userTypeId === 5) {
				$shopModel = new ShopModel();
				$shopList = $shopModel->findByUserID($id);
			}

			$this->setData('user', $user);
			$this->setData('vendorDetails', $vendorDetails);
			$this->setData('godownList', $godownList);
			$this->setData('shopList', $shopList);
			$this->setData('statusOptions', $this->getStatusOptions());
			$this->pageTitle('View User Details');
			return view('users/view', $this->viewdata);
		}

		public function delete($id = null)
		{
			if (!$this->isUserLoggedIn()) {
				return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
			}

			$userModel = new UserModel();
			$user = $userModel->findByID($id);
			if(!$user){
				return $this->response->setJSON(['success' => false, 'message' => 'User not found!']);
			}

			$userModel->delete($id);
			return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully!']);
		}

		private function getStatusOptions()
		{
			return array(
				'1' => 'Active',
				'0' => 'Inactive'
			);
		}

		private function statusClass($status)
		{
			$status = strtolower((string) $status);
			switch ($status) {
				case 'active':
					return 'bg-success-subtle text-success';
				case 'suspended':
					return 'bg-warning-subtle text-warning';
				case 'inactive':
				default:
					return 'bg-secondary-subtle text-muted';
			}
		}

		private function getUserTypeOptions()
		{
			$userTypeModel = new UserTypeModel();
			return $userTypeModel->getActiveTypes();
		}

		private function isValidUserType($userTypeId)
		{
			if (empty($userTypeId)) {
				return false;
			}

			$userTypeModel = new UserTypeModel();
			return (bool) $userTypeModel->where('user_type_id', intval($userTypeId))->first();
		}

		private function isStrongPassword($password)
		{
			if (strlen($password) < 8) {
				return false;
			}

			return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $password);
		}

	}
