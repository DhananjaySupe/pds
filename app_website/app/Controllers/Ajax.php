<?php namespace App\Controllers;

	use App\Models\CenterModel;
	use App\Models\LostPeopleModel;
	use App\Models\FoundPeopleModel;
	use App\Libraries\ImageResize;

	class Ajax extends BaseController
	{
		public function centers()
		{
			if ($this->request->isAJAX()) {
				if ($this->isGet()) {
					$data = array();
					$centerModel = new CenterModel();
					$centers = $centerModel->orderBy('center_name ASC')->findAll();
					$data = array();
					foreach ($centers as $k => $center) {
						$data[] = array(
						'id' => $center['center_id'],
						'name' => $center['center_name'],
						);
					}
					$this->setOutput($data, 'centers');
					$this->setSuccess();
				} else {
					$this->setError('Invalid Method.');
				}
			} else {
				$this->setError('Invalid Method.');
			}
			return $this->response();
		}

		public function state()
		{
			if ($this->request->isAJAX()) {
				if ($this->isGet()) {
					$country = $this->getParam('country', '');
					$data = array();
					if ($country > 0) {
						//Read json and get data
						$states = json_decode(file_get_contents(FCPATH . 'assets/json/states.json'), true);
						$data = array();
						foreach ($states as $k => $state) {
							if ($state['country'] == $country) {
								$data[] = array(
								'id' => $state['state_id'],
								'name_en' => $state['state_name_en'],
								'name' => $state['state_name_'.$this->session->get('user_language')],
								);
							}
						}
					}
					return $this->response($data);
				}
			}
		}

		public function district()
		{
			if ($this->request->isAJAX()) {
				if ($this->isGet()) {
					$state = $this->getParam('state', '');
					$data = array();
					if ($state > 0) {
						//Read json and get data
						$districts = json_decode(file_get_contents(FCPATH . 'assets/json/districts.json'), true);
						$data = array();
						foreach ($districts as $k => $district) {
							if ($district['state'] == $state) {
								$data[] = array(
								'id' => $district['district_id'],
								'name_en' => $district['district_name_en'],
								'name' => $district['district_name_'.$this->session->get('user_language')],
								);
							}
						}
					}
					return $this->response($data);
				}
			}
		}


		public function lostpeopledetails()
		{
			if ($this->request->isAJAX()) {
				if ($this->isPost()) {
					$lost_id = $this->getPost('complaint_number');
					$lostPeopleModel = new LostPeopleModel();
					$centerModel = new CenterModel();
					$lostPeople = $lostPeopleModel->where('lost_id', $lost_id)->first();
					if ($lostPeople) {
						$lostPeople['center_name'] = $centerModel->where('center_id', $lostPeople['center_id'])->first()['center_name'];
						$lostPeople['image_url'] = ($lostPeople['photo'] != '') ? base_url('uploads/lost-people/thumb/' . $lostPeople['photo']) : base_url('assets/images/user.png');
						$this->setOutput($lostPeople, 'lostPeople');
						$this->setSuccess();
					} else {
						$this->setError('Complaint not found.');
					}
				} else {
					$this->setError('Invalid Method.');
				}
			} else {
				$this->setError('Invalid Method.');
			}
			return $this->response();
		}

		public function foundpeopledetails()
		{
			if ($this->request->isAJAX()) {
				if ($this->isPost()) {
					$found_id = $this->getPost('complaint_number');
					$foundPeopleModel = new FoundPeopleModel();
					$centerModel = new CenterModel();
					$foundPeople = $foundPeopleModel->where('found_id', $found_id)->first();
					if ($foundPeople) {
						$foundPeople['center_name'] = $centerModel->where('center_id', $foundPeople['center_id'])->first()['center_name'];
						$foundPeople['image_url'] = ($foundPeople['photo'] != '') ? base_url('uploads/found-people/thumb/' . $foundPeople['photo']) : base_url('assets/images/user.png');
						$this->setOutput($foundPeople, 'foundPeople');
						$this->setSuccess();
					} else {
						$this->setError('Complaint not found.');
					}
				} else {
					$this->setError('Invalid Method.');
				}
			} else {
				$this->setError('Invalid Method.');
			}
			return $this->response();
		}
	}