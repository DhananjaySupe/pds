<?php namespace App\Models;
	use CodeIgniter\Model;
	class StockTransferModel extends Model
	{
		protected $table = 'stock_transfers';
		protected $primaryKey = 'transfer_id';
		protected $returnType = 'array';
		protected $allowedFields = ['transfer_id', 'transfer_number', 'from_location_type', 'from_location_id', 'to_location_type', 'to_location_id', 'request_id', 'total_items', 'status', 'dispatch_date', 'delivery_date', 'transporter_name', 'vehicle_number', 'notes', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' req.request_number, req.status as request_status,' .
				' from_godown.godown_name as from_godown_name, from_shop.shop_name as from_shop_name,' .
				' to_godown.godown_name as to_godown_name, to_shop.shop_name as to_shop_name'
			);
			$builder->join('shop_requests as req', 'req.request_id = ' . $this->table . '.request_id', 'left');

			// Conditional joins prevent wrong matches when IDs overlap across tables
			$builder->join('godowns as from_godown', "from_godown.godown_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'godown'", 'left');
			$builder->join('shops as from_shop', "from_shop.shop_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'shop'", 'left');
			$builder->join('godowns as to_godown', "to_godown.godown_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'godown'", 'left');
			$builder->join('shops as to_shop', "to_shop.shop_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'shop'", 'left');

			$builder->where($this->table . '.transfer_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByTransferNumber($transfer_number)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.transfer_number', $transfer_number);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByRequestID($request_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.request_id', $request_id);
			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function getByStatus($status)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.status', $status);
			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function updateStatus($transfer_id, $status)
		{
			return $this->update($transfer_id, ['status' => $status]);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' req.request_number,' .
				' from_godown.godown_name as from_godown_name, from_shop.shop_name as from_shop_name,' .
				' to_godown.godown_name as to_godown_name, to_shop.shop_name as to_shop_name'
			);
			$builder->join('shop_requests as req', 'req.request_id = ' . $this->table . '.request_id', 'left');
			$builder->join('godowns as from_godown', "from_godown.godown_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'godown'", 'left');
			$builder->join('shops as from_shop', "from_shop.shop_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'shop'", 'left');
			$builder->join('godowns as to_godown', "to_godown.godown_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'godown'", 'left');
			$builder->join('shops as to_shop', "to_shop.shop_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'shop'", 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.transfer_number', $params['keywords'])
					->orLike($this->table . '.transporter_name', $params['keywords'])
					->orLike($this->table . '.vehicle_number', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords'])
					->orLike('req.request_number', $params['keywords'])
					->orLike('from_godown.godown_name', $params['keywords'])
					->orLike('from_shop.shop_name', $params['keywords'])
					->orLike('to_godown.godown_name', $params['keywords'])
					->orLike('to_shop.shop_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['transfer_id'])) {
				$builder->where($this->table . '.transfer_id', $params['transfer_id']);
			}

			if (isset($params['transfer_number'])) {
				$builder->where($this->table . '.transfer_number', $params['transfer_number']);
			}

			if (isset($params['from_location_type'])) {
				$builder->where($this->table . '.from_location_type', $params['from_location_type']);
			}

			if (isset($params['from_location_id'])) {
				$builder->where($this->table . '.from_location_id', $params['from_location_id']);
			}

			if (isset($params['to_location_type'])) {
				$builder->where($this->table . '.to_location_type', $params['to_location_type']);
			}

			if (isset($params['to_location_id'])) {
				$builder->where($this->table . '.to_location_id', $params['to_location_id']);
			}

			if (isset($params['request_id'])) {
				$builder->where($this->table . '.request_id', $params['request_id']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			if (isset($params['dispatch_date'])) {
				$builder->where('DATE('.$this->table . '.dispatch_date)', $params['dispatch_date']);
			}

			if (isset($params['dispatch_date_from'])) {
				$builder->where('DATE('.$this->table . '.dispatch_date) >=', $params['dispatch_date_from']);
			}

			if (isset($params['dispatch_date_to'])) {
				$builder->where('DATE('.$this->table . '.dispatch_date) <=', $params['dispatch_date_to']);
			}

			if (isset($params['delivery_date'])) {
				$builder->where('DATE('.$this->table . '.delivery_date)', $params['delivery_date']);
			}

			if (isset($params['delivery_date_from'])) {
				$builder->where('DATE('.$this->table . '.delivery_date) >=', $params['delivery_date_from']);
			}

			if (isset($params['delivery_date_to'])) {
				$builder->where('DATE('.$this->table . '.delivery_date) <=', $params['delivery_date_to']);
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


