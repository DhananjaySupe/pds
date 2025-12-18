<?php namespace App\Models;
	use CodeIgniter\Model;
	class StockMovementModel extends Model
	{
		protected $table = 'stock_movements';
		protected $primaryKey = 'movement_id';
		protected $returnType = 'array';
		protected $allowedFields = ['movement_id', 'qr_id', 'product_id', 'movement_type', 'from_location_type', 'from_location_id', 'to_location_type', 'to_location_id', 'quantity', 'reference_id', 'reference_type', 'movement_date', 'moved_by', 'notes', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' qr.qr_code, qr.batch_number, qr.expiry_date as qr_expiry_date, qr.status as qr_status,' .
				' products.product_code, products.product_name, products.unit_type, products.base_unit,' .
				' moved_user.full_name as moved_by_name,' .
				' from_godown.godown_name as from_godown_name, from_shop.shop_name as from_shop_name,' .
				' to_godown.godown_name as to_godown_name, to_shop.shop_name as to_shop_name'
			);
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('users as moved_user', 'moved_user.user_id = ' . $this->table . '.moved_by', 'left');

			// Conditional joins to avoid wrong name matches when IDs overlap across tables
			$builder->join('godowns as from_godown', "from_godown.godown_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'godown'", 'left');
			$builder->join('shops as from_shop', "from_shop.shop_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'shop'", 'left');
			$builder->join('godowns as to_godown', "to_godown.godown_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'godown'", 'left');
			$builder->join('shops as to_shop', "to_shop.shop_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'shop'", 'left');

			$builder->where($this->table . '.movement_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByReference($reference_type, $reference_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.reference_type', $reference_type);
			$builder->where($this->table . '.reference_id', $reference_id);
			$builder->orderBy($this->table . '.movement_date', 'DESC');
			$builder->orderBy($this->table . '.movement_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function findByQRID($qr_id, $movement_type = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.qr_id', $qr_id);
			if ($movement_type !== null) {
				$builder->where($this->table . '.movement_type', $movement_type);
			}
			$builder->orderBy($this->table . '.movement_date', 'DESC');
			$builder->orderBy($this->table . '.movement_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function findByProductID($product_id, $movement_type = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);
			if ($movement_type !== null) {
				$builder->where($this->table . '.movement_type', $movement_type);
			}
			$builder->orderBy($this->table . '.movement_date', 'DESC');
			$builder->orderBy($this->table . '.movement_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' qr.qr_code, qr.batch_number,' .
				' products.product_code, products.product_name, products.category, products.brand,' .
				' moved_user.full_name as moved_by_name,' .
				' from_godown.godown_name as from_godown_name, from_shop.shop_name as from_shop_name,' .
				' to_godown.godown_name as to_godown_name, to_shop.shop_name as to_shop_name'
			);
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('users as moved_user', 'moved_user.user_id = ' . $this->table . '.moved_by', 'left');
			$builder->join('godowns as from_godown', "from_godown.godown_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'godown'", 'left');
			$builder->join('shops as from_shop', "from_shop.shop_id = {$this->table}.from_location_id AND {$this->table}.from_location_type = 'shop'", 'left');
			$builder->join('godowns as to_godown', "to_godown.godown_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'godown'", 'left');
			$builder->join('shops as to_shop', "to_shop.shop_id = {$this->table}.to_location_id AND {$this->table}.to_location_type = 'shop'", 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('qr.qr_code', $params['keywords'])
					->orLike('qr.batch_number', $params['keywords'])
					->orLike('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('products.category', $params['keywords'])
					->orLike('products.brand', $params['keywords'])
					->orLike($this->table . '.reference_type', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords'])
					->orLike('moved_user.full_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['movement_id'])) {
				$builder->where($this->table . '.movement_id', $params['movement_id']);
			}

			if (isset($params['qr_id'])) {
				$builder->where($this->table . '.qr_id', $params['qr_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['movement_type'])) {
				$builder->where($this->table . '.movement_type', $params['movement_type']);
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

			if (isset($params['reference_type'])) {
				$builder->where($this->table . '.reference_type', $params['reference_type']);
			}

			if (isset($params['reference_id'])) {
				$builder->where($this->table . '.reference_id', $params['reference_id']);
			}

			if (isset($params['moved_by'])) {
				$builder->where($this->table . '.moved_by', $params['moved_by']);
			}

			if (isset($params['movement_date'])) {
				$builder->where('DATE('.$this->table . '.movement_date)', $params['movement_date']);
			}

			if (isset($params['movement_date_from'])) {
				$builder->where('DATE('.$this->table . '.movement_date) >=', $params['movement_date_from']);
			}

			if (isset($params['movement_date_to'])) {
				$builder->where('DATE('.$this->table . '.movement_date) <=', $params['movement_date_to']);
			}

			if (isset($params['min_quantity'])) {
				$builder->where($this->table . '.quantity >=', $params['min_quantity']);
			}

			if (isset($params['max_quantity'])) {
				$builder->where($this->table . '.quantity <=', $params['max_quantity']);
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
					$builder->orderBy($this->table . '.movement_date DESC');
					$builder->orderBy($this->table . '.movement_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


