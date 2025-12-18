<?php namespace App\Models;
	use CodeIgniter\Model;
	class CustomerModel extends Model
	{
		protected $table = 'customers';
		protected $primaryKey = 'customer_id';
		protected $returnType = 'array';
		protected $allowedFields = ['customer_id', 'user_id', 'loyalty_points', 'total_purchases'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, users.status');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.customer_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByUserID($user_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, users.status');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.user_id', $user_id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, users.status');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');

			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('users.full_name', $params['keywords'])
					->orLike('users.email', $params['keywords'])
					->orLike('users.phone', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['min_loyalty_points'])) {
				$builder->where($this->table . '.loyalty_points >=', $params['min_loyalty_points']);
			}

			if (isset($params['max_loyalty_points'])) {
				$builder->where($this->table . '.loyalty_points <=', $params['max_loyalty_points']);
			}

			if (isset($params['min_total_purchases'])) {
				$builder->where($this->table . '.total_purchases >=', $params['min_total_purchases']);
			}

			if (isset($params['max_total_purchases'])) {
				$builder->where($this->table . '.total_purchases <=', $params['max_total_purchases']);
			}

			if (isset($params['count']) && $params['count']) {
				return $builder->countAllResults();
			} else {
				if (isset($params['limit'])) {
					$builder->limit($params['limit']['length'], $params['limit']['offset']);
				}

				if(isset($params['sort']['column']) && !empty($params['sort']['column']) && isset($params['sort']['order']) && !empty($params['sort']['order'])){
					$builder->orderBy($params['sort']['column'], $params['sort']['order']);
				} else {
					$builder->orderBy($this->table . '.customer_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}

		public function updateLoyaltyPoints($customer_id, $points)
		{
			$customer = $this->find($customer_id);
			if ($customer) {
				$new_points = floatval($customer['loyalty_points']) + floatval($points);
				return $this->update($customer_id, ['loyalty_points' => $new_points]);
			}
			return false;
		}

		public function updateTotalPurchases($customer_id, $amount)
		{
			$customer = $this->find($customer_id);
			if ($customer) {
				$new_total = floatval($customer['total_purchases']) + floatval($amount);
				return $this->update($customer_id, ['total_purchases' => $new_total]);
			}
			return false;
		}
	}

