<?php namespace App\Controllers;

use App\Models\CustomerModel;

class Customers extends BaseController
{
	/**
	 * Select2 AJAX search endpoint for customers
	 * GET /customers/search?term=abc
	 */
	public function search()
	{
		if (!$this->isUserLoggedIn()) {
			return $this->response->setJSON(['results' => []]);
		}

		$term = trim((string) $this->getParam('term', $this->getParam('q', '')));
		if (mb_strlen($term) < 3) {
			return $this->response->setJSON(['results' => []]);
		}

		$customerModel = new CustomerModel();
		$params = array(
			'keywords' => $term,
			'limit' => array('length' => 20, 'offset' => 0),
			'sort' => array('column' => 'users.full_name', 'order' => 'asc'),
		);
		$customers = $customerModel->search($params);

		$results = array();
		if (!empty($customers)) {
			foreach ($customers as $c) {
				$name = $c['full_name'] ?? '';
				$phone = $c['phone'] ?? '';
				$email = $c['email'] ?? '';
				$meta = '';
				if (!empty($phone)) {
					$meta = $phone;
				} elseif (!empty($email)) {
					$meta = $email;
				}
				$text = trim($name . (!empty($meta) ? ' (' . $meta . ')' : ''));

				$results[] = array(
					'id' => (int) $c['customer_id'],
					'text' => $text !== '' ? $text : ('Customer #' . (int) $c['customer_id']),
				);
			}
		}

		return $this->response->setJSON(['results' => $results]);
	}
}


