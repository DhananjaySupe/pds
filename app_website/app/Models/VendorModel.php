<?php namespace App\Models;
	use CodeIgniter\Model;
	class VendorModel extends Model
	{
		protected $table = 'vendors';
		protected $primaryKey = 'vendor_id';
		protected $returnType = 'array';
		protected $allowedFields = ['vendor_id', 'user_id', 'company_name', 'gst_number', 'pan_number', 'bank_account_details', 'payment_terms', 'rating'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, users.status');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.vendor_id', $id);
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

		public function findByCompanyName($company_name)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.company_name', $company_name);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, users.status');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.company_name', $params['keywords'])
					->orLike($this->table . '.gst_number', $params['keywords'])
					->orLike($this->table . '.pan_number', $params['keywords'])
					->orLike($this->table . '.payment_terms', $params['keywords'])
					->orLike('users.full_name', $params['keywords'])
					->orLike('users.email', $params['keywords'])
					->orLike('users.phone', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['vendor_id'])) {
				$builder->where($this->table . '.vendor_id', $params['vendor_id']);
			}

			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}

			if (isset($params['company_name'])) {
				$builder->where($this->table . '.company_name', $params['company_name']);
			}

			if (isset($params['gst_number'])) {
				$builder->where($this->table . '.gst_number', $params['gst_number']);
			}

			if (isset($params['pan_number'])) {
				$builder->where($this->table . '.pan_number', $params['pan_number']);
			}

			if (isset($params['min_rating'])) {
				$builder->where($this->table . '.rating >=', $params['min_rating']);
			}

			if (isset($params['max_rating'])) {
				$builder->where($this->table . '.rating <=', $params['max_rating']);
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
					$builder->orderBy($this->table . '.vendor_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


