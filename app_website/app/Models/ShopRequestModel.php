<?php namespace App\Models;
	use CodeIgniter\Model;
	class ShopRequestModel extends Model
	{
		protected $table = 'shop_requests';
		protected $primaryKey = 'request_id';
		protected $returnType = 'array';
		protected $allowedFields = ['request_id', 'request_number', 'shop_id', 'godown_id', 'total_items', 'status', 'request_date', 'required_date', 'notes', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, shops.shop_name, shops.location as shop_location, godowns.godown_name, godowns.location as godown_location');
			$builder->join('shops', 'shops.shop_id = ' . $this->table . '.shop_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->where($this->table . '.request_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByRequestNumber($request_number)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.request_number', $request_number);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByShopID($shop_id, $status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, godowns.godown_name');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->where($this->table . '.shop_id', $shop_id);
			if ($status !== null) {
				$builder->where($this->table . '.status', $status);
			}
			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function findByGodownID($godown_id, $status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, shops.shop_name');
			$builder->join('shops', 'shops.shop_id = ' . $this->table . '.shop_id', 'left');
			$builder->where($this->table . '.godown_id', $godown_id);
			if ($status !== null) {
				$builder->where($this->table . '.status', $status);
			}
			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function updateStatus($request_id, $status)
		{
			return $this->update($request_id, ['status' => $status]);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, shops.shop_name, shops.location as shop_location, godowns.godown_name, godowns.location as godown_location');
			$builder->join('shops', 'shops.shop_id = ' . $this->table . '.shop_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.request_number', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords'])
					->orLike('shops.shop_name', $params['keywords'])
					->orLike('godowns.godown_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['request_id'])) {
				$builder->where($this->table . '.request_id', $params['request_id']);
			}

			if (isset($params['request_number'])) {
				$builder->where($this->table . '.request_number', $params['request_number']);
			}

			if (isset($params['shop_id'])) {
				$builder->where($this->table . '.shop_id', $params['shop_id']);
			}

			if (isset($params['godown_id'])) {
				$builder->where($this->table . '.godown_id', $params['godown_id']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			if (isset($params['request_date'])) {
				$builder->where('DATE('.$this->table . '.request_date)', $params['request_date']);
			}

			if (isset($params['request_date_from'])) {
				$builder->where('DATE('.$this->table . '.request_date) >=', $params['request_date_from']);
			}

			if (isset($params['request_date_to'])) {
				$builder->where('DATE('.$this->table . '.request_date) <=', $params['request_date_to']);
			}

			if (isset($params['required_date'])) {
				$builder->where('DATE('.$this->table . '.required_date)', $params['required_date']);
			}

			if (isset($params['min_total_items'])) {
				$builder->where($this->table . '.total_items >=', $params['min_total_items']);
			}

			if (isset($params['max_total_items'])) {
				$builder->where($this->table . '.total_items <=', $params['max_total_items']);
			}

			if (isset($params['created_date'])) {
				$builder->where('DATE('.$this->table . '.created_at)', $params['created_date']);
			}

			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE('.$this->table . '.created_at) >=', $params['start_date']);
				$builder->where('DATE('.$this->table . '.created_at) <=', $params['end_date']);
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
					$builder->orderBy($this->table . '.created_at DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


