<?php namespace App\Models;
	use CodeIgniter\Model;
	class StockInventoryModel extends Model
	{
		protected $table = 'stock_inventory';
		protected $primaryKey = 'stock_id';
		protected $returnType = 'array';
		protected $allowedFields = ['stock_id', 'product_id', 'location_type', 'location_id', 'quantity', 'stock_date', 'is_available'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit, godowns.godown_name, shops.shop_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			// Conditional joins prevent wrong matches when IDs overlap across tables
			$builder->join('godowns', "godowns.godown_id = {$this->table}.location_id AND {$this->table}.location_type = 'godown'", 'left');
			$builder->join('shops', "shops.shop_id = {$this->table}.location_id AND {$this->table}.location_type = 'shop'", 'left');
			$builder->where($this->table . '.stock_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByProductID($product_id, $location_type = null, $location_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);

			if ($location_type !== null) {
				$builder->where($this->table . '.location_type', $location_type);
			}

			if ($location_id !== null) {
				$builder->where($this->table . '.location_id', $location_id);
			}

			$builder->orderBy($this->table . '.stock_date', 'DESC');
			$builder->orderBy($this->table . '.stock_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function findLatestByProductIDLocation($product_id, $location_type, $location_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);
			$builder->where($this->table . '.location_type', $location_type);
			$builder->where($this->table . '.location_id', $location_id);
			$builder->orderBy($this->table . '.stock_date', 'DESC');
			$builder->orderBy($this->table . '.stock_id', 'DESC');
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByLocation($location_type, $location_id, $is_available = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.location_type', $location_type);
			$builder->where($this->table . '.location_id', $location_id);

			if ($is_available !== null) {
				$builder->where($this->table . '.is_available', $is_available);
			}

			$builder->orderBy($this->table . '.stock_date', 'DESC');
			$builder->orderBy($this->table . '.stock_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function setAvailability($stock_id, $is_available)
		{
			return $this->update($stock_id, ['is_available' => $is_available]);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.category, products.brand, godowns.godown_name, shops.shop_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			// Conditional joins prevent wrong matches when IDs overlap across tables
			$builder->join('godowns', "godowns.godown_id = {$this->table}.location_id AND {$this->table}.location_type = 'godown'", 'left');
			$builder->join('shops', "shops.shop_id = {$this->table}.location_id AND {$this->table}.location_type = 'shop'", 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('products.product_name', $params['keywords'])
					->orLike('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('godowns.godown_name', $params['keywords'])
					->orLike('shops.shop_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['stock_id'])) {
				$builder->where($this->table . '.stock_id', $params['stock_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['location_type'])) {
				$builder->where($this->table . '.location_type', $params['location_type']);
			}

			if (isset($params['location_id'])) {
				$builder->where($this->table . '.location_id', $params['location_id']);
			}

			if (isset($params['is_available']) && $params['is_available'] !== '') {
				$builder->where($this->table . '.is_available', $params['is_available']);
			}

			if (isset($params['stock_date'])) {
				$builder->where('DATE('.$this->table . '.stock_date)', $params['stock_date']);
			}

			if (isset($params['stock_date_from'])) {
				$builder->where('DATE('.$this->table . '.stock_date) >=', $params['stock_date_from']);
			}

			if (isset($params['stock_date_to'])) {
				$builder->where('DATE('.$this->table . '.stock_date) <=', $params['stock_date_to']);
			}

			if (isset($params['min_quantity'])) {
				$builder->where($this->table . '.quantity >=', $params['min_quantity']);
			}

			if (isset($params['max_quantity'])) {
				$builder->where($this->table . '.quantity <=', $params['max_quantity']);
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
					$builder->orderBy($this->table . '.stock_date DESC');
					$builder->orderBy($this->table . '.stock_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


