<?php namespace App\Controllers;
	use App\Models\LostPeopleModel;
	use App\Models\UserModel;
	class LostPeople extends BaseController
	{
		public function index()
		{
			if ($this->isGet()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						$lostPeopleModel = new LostPeopleModel();
						$params = array();
						$page = intval($this->getParam('page', 1));
						$length = intval($this->getParam('per_page', 25));
						$totalrecords = intval($this->getParam('totalrecords', 0));
						$keywords = $this->getParam('keywords', '');
						$date = $this->getParam('date', '');
						$start_date = $this->getParam('start_date', '');
						$end_date = $this->getParam('end_date', '');
						$center_id = $this->getParam('center_id', '');
						$gender = $this->getParam('gender', '');
						$age = $this->getParam('age', '');
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
						if($center_id != ''){
							$params['center_id'] = $center_id;
						}
						if($gender != ''){
							$params['gender'] = $gender;
						}
						if($age != ''){
							$params['age'] = $age;
						}

                        if($totalrecords == 0 || $page == 1){
                            $params['count'] = true;
                            $totalrecords = $lostPeopleModel->search($params);
                            unset($params['count']);
                        }
                        $paging = paging($page, $totalrecords, $length);
                        $params['limit'] = array('length' => $paging['length'], 'offset' => $paging['offset']);

                        if($order_by_col != ''){
                            $params['sort'] = array('column' => $order_by_col, 'order' => $order_by);
                        }

                        $lostPeople = $lostPeopleModel->search($params);
                        //echo $lostPeopleModel->getLastQuery()->getQuery();exit;
                        $remainingrecords = $totalrecords - ($paging['offset'] + count($lostPeople));
                        $paging['remainingrecords'] = $remainingrecords;
                        $this->setSuccess($this->successMessage);
                        $this->setOutput(array('paging' => array($paging), 'lost_people' => $lostPeople));
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

		public function details()
		{
			if ($this->isGet()) {
				if ($this->AuthenticateApikey()) {
					if ($this->AuthenticateToken()) {
						$lost_id = $this->getParam('lost_id');
						if(is_numeric($lost_id)){
							$lost_id = intval($lost_id);
							if ($lost_id > 0) {
								$lostPeopleModel = new LostPeopleModel();
								$lostPerson = $lostPeopleModel->findByID($lost_id);
								if($lostPerson){
									$this->setSuccess();
									$this->setOutput(array('lost_person' => array($lostPerson)));
								} else {
									$this->setError($this->noContent);
								}
							} else {
								$this->setError("Please enter found_id");
							}
						} else {
							$this->setError("Please enter found_id in numeric");
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

		public function entry()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					$lostPeopleModel = new LostPeopleModel();

					if($this->isPost()){
						$userModel = new UserModel();
						$member = $userModel->where('user_id', $this->_member['id'])->first();
						if($member){
							$post = esc($this->getPost());
							$lost_id = isset($post['lost_id']) ? $post['lost_id'] : 0;
							$isvalidrequest = true;

							// Required field validation (matching website controller)
							if (!isset($post['complainant_relation']) || empty($post['complainant_relation'])) {
								$this->setError("Please enter complainant relation.");
								$isvalidrequest = false;
							}
							if (!isset($post['complainant_name']) || empty($post['complainant_name'])) {
								$this->setError("Please enter complainant name.");
								$isvalidrequest = false;
							}
							if (!isset($post['date_reported']) || empty($post['date_reported'])) {
								$this->setError("Please enter date reported.");
								$isvalidrequest = false;
							}
							if (!isset($post['first_name']) || empty($post['first_name'])) {
								$this->setError("Please enter first name.");
								$isvalidrequest = false;
							}
							if (!isset($post['gender']) || empty($post['gender'])) {
								$this->setError("Please enter gender.");
								$isvalidrequest = false;
							}
							if (!isset($post['age']) || empty($post['age'])) {
								$this->setError("Please enter age.");
								$isvalidrequest = false;
							}
							if (!isset($post['clothing_type']) || empty($post['clothing_type'])) {
								$this->setError("Please enter clothing type.");
								$isvalidrequest = false;
							}
							if (!isset($post['clothing_color']) || empty($post['clothing_color'])) {
								$this->setError("Please enter clothing color.");
								$isvalidrequest = false;
							}

							// Handle photo upload with improved structure
							if($isvalidrequest && isset($_FILES['photo']) && $_FILES['photo']['name']){
								$file_name = $_FILES['photo']['name'];
								$file_size = $_FILES['photo']['size'];
								$file_tmp = $_FILES['photo']['tmp_name'];
								$file_type = $_FILES['photo']['type'];
								$tmp = explode('.', $_FILES['photo']['name']);
								$file_ext = end($tmp);
								$extensions = array("jpeg","jpg","png","JPEG","JPG","PNG");
								if(in_array($file_ext,$extensions)=== false){
									$this->setError("extension not allowed, please choose a JPEG or PNG file.");
									$isvalidrequest = false;
								}
								if($isvalidrequest && empty($this->_output['message'])){
									$new_file_name = md5(uniqid(rand(),true));
									$upload_path = "uploads/lost-people/";
									if (!is_dir($upload_path)) {
										mkdir($upload_path, 0755, true);
									}
									move_uploaded_file($file_tmp, $upload_path.$new_file_name.'.'.$file_ext);
									$post['photo'] = $new_file_name.'.'.$file_ext;
								}
							}

							if($isvalidrequest && empty($this->_output['message'])){
								if($lost_id > 0){
									// Update existing record
									$lostPerson = $lostPeopleModel->findByID($lost_id);
									if($lostPerson){
										$values = array(
											'first_name' => $post['first_name'],
											'last_name' => isset($post['last_name']) ? $post['last_name'] : $lostPerson['last_name'],
											'gender' => $post['gender'],
											'age' => isset($post['age']) ? $post['age'] : $lostPerson['age'],
											'date_of_birth' => isset($post['date_of_birth']) ? $post['date_of_birth'] : $lostPerson['date_of_birth'],
											'height' => isset($post['height']) ? $post['height'] : $lostPerson['height'],
											'complexion' => isset($post['complexion']) ? $post['complexion'] : $lostPerson['complexion'],
											'distinguishing_marks' => isset($post['distinguishing_marks']) ? $post['distinguishing_marks'] : $lostPerson['distinguishing_marks'],
											'communication_ability' => isset($post['communication_ability']) ? $post['communication_ability'] : $lostPerson['communication_ability'],
											'languages_known' => isset($post['languages_known']) ? $post['languages_known'] : $lostPerson['languages_known'],
											'disability' => isset($post['disability']) ? $post['disability'] : $lostPerson['disability'],
											'disability_desc' => isset($post['disability_desc']) ? $post['disability_desc'] : $lostPerson['disability_desc'],
											'clothing_type' => isset($post['clothing_type']) ? $post['clothing_type'] : $lostPerson['clothing_type'],
											'clothing_color' => isset($post['clothing_color']) ? $post['clothing_color'] : $lostPerson['clothing_color'],
											'found_location' => isset($post['found_location']) ? $post['found_location'] : $lostPerson['found_location'],
											'last_seen_date' => isset($post['last_seen_date']) ? $post['last_seen_date'] : $lostPerson['last_seen_date'],
											'last_seen_location' => isset($post['last_seen_location']) ? $post['last_seen_location'] : $lostPerson['last_seen_location'],
											'aadhaar_number' => isset($post['aadhaar_number']) ? $post['aadhaar_number'] : $lostPerson['aadhaar_number'],
											'pan_number' => isset($post['pan_number']) ? $post['pan_number'] : $lostPerson['pan_number'],
											'police_station' => isset($post['police_station']) ? $post['police_station'] : $lostPerson['police_station'],
											'address' => isset($post['address']) ? $post['address'] : $lostPerson['address'],
											'pincode' => isset($post['pincode']) ? $post['pincode'] : $lostPerson['pincode'],
											'taluka' => isset($post['taluka']) ? $post['taluka'] : $lostPerson['taluka'],
											'city' => isset($post['city']) ? $post['city'] : $lostPerson['city'],
											'district' => isset($post['district']) ? $post['district'] : $lostPerson['district'],
											'state' => isset($post['state']) ? $post['state'] : $lostPerson['state'],
											'country' => isset($post['country']) ? $post['country'] : $lostPerson['country'],
											'complainant_relation' => isset($post['complainant_relation']) ? $post['complainant_relation'] : $lostPerson['complainant_relation'],
											'complainant_name' => isset($post['complainant_name']) ? $post['complainant_name'] : $lostPerson['complainant_name'],
											'complainant_phone' => isset($post['complainant_phone']) ? $post['complainant_phone'] : $lostPerson['complainant_phone'],
											'complainant_alternate_phone' => isset($post['complainant_alternate_phone']) ? $post['complainant_alternate_phone'] : $lostPerson['complainant_alternate_phone'],
											'relative_1' => isset($post['relative_1']) ? $post['relative_1'] : $lostPerson['relative_1'],
											'relative_1_name' => isset($post['relative_1_name']) ? $post['relative_1_name'] : $lostPerson['relative_1_name'],
											'relative_1_phone' => isset($post['relative_1_phone']) ? $post['relative_1_phone'] : $lostPerson['relative_1_phone'],
											'relative_2' => isset($post['relative_2']) ? $post['relative_2'] : $lostPerson['relative_2'],
											'relative_2_name' => isset($post['relative_2_name']) ? $post['relative_2_name'] : $lostPerson['relative_2_name'],
											'relative_2_phone' => isset($post['relative_2_phone']) ? $post['relative_2_phone'] : $lostPerson['relative_2_phone'],
											'center_id' => isset($post['center_id']) ? $post['center_id'] : $lostPerson['center_id'],
											'latitude' => isset($post['latitude']) ? $post['latitude'] : $lostPerson['latitude'],
											'longitude' => isset($post['longitude']) ? $post['longitude'] : $lostPerson['longitude'],
											'date_reported' => isset($post['date_reported']) ? $post['date_reported'] : $lostPerson['date_reported'],
											'photo' => isset($post['photo']) ? $post['photo'] : $lostPerson['photo'],
											'updated_at' => date("Y-m-d H:i:s"),
											'updated_by' => $this->_member['id']
										);
										$lostPeopleModel->update($lostPerson['lost_id'], $values);

										$this->setOutput($lostPerson['lost_id'], 'lost_id');
										$this->setSuccess('Updated successfully');
									} else {
										$this->setError("Invalid lost person record");
									}
								} else {
									// Insert new record
									$values = array(
										'first_name' => $post['first_name'],
										'last_name' => isset($post['last_name']) ? $post['last_name'] : '',
										'gender' => $post['gender'],
										'age' => isset($post['age']) ? $post['age'] : '',
										'date_of_birth' => isset($post['date_of_birth']) ? $post['date_of_birth'] : '',
										'height' => isset($post['height']) ? $post['height'] : '',
										'complexion' => isset($post['complexion']) ? $post['complexion'] : '',
										'distinguishing_marks' => isset($post['distinguishing_marks']) ? $post['distinguishing_marks'] : '',
										'communication_ability' => isset($post['communication_ability']) ? $post['communication_ability'] : '',
										'languages_known' => isset($post['languages_known']) ? $post['languages_known'] : '',
										'disability' => isset($post['disability']) ? $post['disability'] : '',
										'disability_desc' => isset($post['disability_desc']) ? $post['disability_desc'] : '',
										'clothing_type' => isset($post['clothing_type']) ? $post['clothing_type'] : '',
										'clothing_color' => isset($post['clothing_color']) ? $post['clothing_color'] : '',
										'found_location' => isset($post['found_location']) ? $post['found_location'] : '',
										'last_seen_date' => isset($post['last_seen_date']) ? $post['last_seen_date'] : '',
										'last_seen_location' => isset($post['last_seen_location']) ? $post['last_seen_location'] : '',
										'aadhaar_number' => isset($post['aadhaar_number']) ? $post['aadhaar_number'] : '',
										'pan_number' => isset($post['pan_number']) ? $post['pan_number'] : '',
										'police_station' => isset($post['police_station']) ? $post['police_station'] : '',
										'address' => isset($post['address']) ? $post['address'] : '',
										'pincode' => isset($post['pincode']) ? $post['pincode'] : '',
										'taluka' => isset($post['taluka']) ? $post['taluka'] : '',
										'city' => isset($post['city']) ? $post['city'] : '',
										'district' => isset($post['district']) ? $post['district'] : '',
										'state' => isset($post['state']) ? $post['state'] : '',
										'country' => isset($post['country']) ? $post['country'] : '',
										'complainant_relation' => isset($post['complainant_relation']) ? $post['complainant_relation'] : '',
										'complainant_name' => isset($post['complainant_name']) ? $post['complainant_name'] : '',
										'complainant_phone' => isset($post['complainant_phone']) ? $post['complainant_phone'] : '',
										'complainant_alternate_phone' => isset($post['complainant_alternate_phone']) ? $post['complainant_alternate_phone'] : '',
										'relative_1' => isset($post['relative_1']) ? $post['relative_1'] : '',
										'relative_1_name' => isset($post['relative_1_name']) ? $post['relative_1_name'] : '',
										'relative_1_phone' => isset($post['relative_1_phone']) ? $post['relative_1_phone'] : '',
										'relative_2' => isset($post['relative_2']) ? $post['relative_2'] : '',
										'relative_2_name' => isset($post['relative_2_name']) ? $post['relative_2_name'] : '',
										'relative_2_phone' => isset($post['relative_2_phone']) ? $post['relative_2_phone'] : '',
										'center_id' => isset($post['center_id']) ? $post['center_id'] : 0,
										'latitude' => isset($post['latitude']) ? $post['latitude'] : '',
										'longitude' => isset($post['longitude']) ? $post['longitude'] : '',
										'date_reported' => isset($post['date_reported']) ? $post['date_reported'] : date("Y-m-d"),
										'handover_id' => 0,
										'is_ai_traced' => 0,
										'is_bhashini_traced' => 0,
										'photo' => isset($post['photo']) ? $post['photo'] : '',
										'created_at' => date("Y-m-d H:i:s"),
										'created_by' => $this->_member['id']
									);
									$lost_id = $lostPeopleModel->insert($values);

									if($lost_id > 0){
										$this->setOutput($lost_id, 'lost_id');
										$this->setSuccess("Added successfully");
									} else {
										$this->setError("Failed to add lost person record");
									}
								}
							}
						} else {
							$this->setError('User not found');
						}
					} else {
						$this->setError($this->methodNotAllowed);
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function uploadPhoto()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->isPost()){
						$post = esc($this->getPost());
						$lost_id = isset($post['lost_id']) ? $post['lost_id'] : 0;

						// Check if lost person exists (optional validation)
						if ($lost_id > 0) {
							$lostPeopleModel = new LostPeopleModel();
							$lostPerson = $lostPeopleModel->findByID($lost_id);
							if (!$lostPerson) {
								$this->setError('Lost person not found.');
								return $this->response();
							}
						}

						// Check if file was uploaded
						if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
							$this->setError('Please select a valid image file.');
							return $this->response();
						}

						$file = $_FILES['photo'];
						$filetype = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
						$filename = md5(uniqid(rand(), true)) . '.' . $filetype;

						// Validate file type
						$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
						if (!in_array($filetype, $allowed_types)) {
							$this->setError('Invalid file type. Please upload JPG, PNG or GIF image.');
							return $this->response();
						}

						$upload_dir = "uploads/lost-people/";
						if (!is_dir($upload_dir)) {
							mkdir($upload_dir, 0755, true);
						}

						if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
							$image = array(
								'filename' => $filename,
								'url' => base_url($upload_dir . $filename),
								'lost_id' => $lost_id
							);

							$this->setOutput($image, 'photo');
							$this->setSuccess('Photo uploaded successfully.');
						} else {
							$this->setError('File could not be uploaded. Please try uploading it again.');
						}
					} else {
						$this->setError($this->methodNotAllowed);
					}
				} else {
					$this->setError($this->invalidToken);
				}
			} else {
				$this->setError($this->invalidApiKey);
			}
			return $this->response();
		}

		public function matchFoundProfile()
		{
			if ($this->AuthenticateApikey()) {
				if ($this->AuthenticateToken()) {
					if($this->isPost()){
						$post = esc($this->getPost());
						$filename = $post['filename'] ?? '';
						$lost_id = isset($post['lost_id']) ? $post['lost_id'] : 0;

						if (empty($filename) && $lost_id == 0) {
							$this->setError('No filename or lost_id provided.');
							return $this->response();
						}

						// Get lost person data for matching
						$lostPeopleModel = new LostPeopleModel();
						$lostPerson = null;

						if($lost_id > 0) {
							$lostPerson = $lostPeopleModel->findByID($lost_id);
						}

						if (!$lostPerson) {
							$this->setError('Lost person not found.');
							return $this->response();
						}

						// Mock matching results (in real implementation, integrate with AI service)
						$mockMatches = [
							[
								'lost_id' => '123',
								'name' => 'John Doe',
								'match_percentage' => '85',
								'last_updated' => '2 hours ago',
								'photo' => base_url('assets/images/user.png'),
								'age' => '25',
								'gender' => 'Male',
								'found_location' => 'Central Park'
							],
							[
								'lost_id' => '124',
								'name' => 'Jane Smith',
								'match_percentage' => '72',
								'last_updated' => '1 day ago',
								'photo' => base_url('assets/images/user.png'),
								'age' => '30',
								'gender' => 'Female',
								'found_location' => 'Main Street'
							]
						];

						$this->setOutput($mockMatches, 'matches');
						$this->setSuccess('Profile matching completed.');
					} else {
						$this->setError($this->methodNotAllowed);
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
