<?php namespace App\Models;
	use CodeIgniter\Model;
	class ProductsModel extends Model
	{
		protected $table = 'products';
		protected $primaryKey = 'id';
		protected $returnType = 'array';
		protected $allowedFields = ['product_id', 'product_code', 'name', 'description', 'category', 'brand', 'unit_price', 'reorder_level', 'status', 'created_at'];
		protected $createdField = 'created_at';
		protected $updatedField = '';

		/**
		 * Find product by ID
		 */
		public function findByID($product_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		/**
		 * Search products with enhanced functionality
		 */
		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');

			// Date range filtering
			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE('.$this->table . '.created_at) >=', $params['start_date']);
				$builder->where('DATE('.$this->table . '.created_at) <=', $params['end_date']);
			}

			// Date filtering
			if (isset($params['date'])) {
				$builder->where('DATE('.$this->table . '.created_at)', $params['date']);
			}

			// Keywords search
			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.name', $params['keywords']);
				$builder->orLike($this->table . '.product_code', $params['keywords']);
				$builder->orLike($this->table . '.description', $params['keywords']);
				$builder->orLike($this->table . '.category', $params['keywords']);
				$builder->orLike($this->table . '.brand', $params['keywords']);
				$builder->groupEnd();
			}

			// Product code filtering
			if (isset($params['product_code'])) {
				$builder->where($this->table . '.product_code', $params['product_code']);
			}

			// Category filtering
			if (isset($params['category'])) {
				$builder->where($this->table . '.category', $params['category']);
			}

			// Brand filtering
			if (isset($params['brand'])) {
				$builder->where($this->table . '.brand', $params['brand']);
			}

			// Status filtering
			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			// Price range filtering
			if (isset($params['min_price'])) {
				$builder->where($this->table . '.unit_price >=', $params['min_price']);
			}

			if (isset($params['max_price'])) {
				$builder->where($this->table . '.unit_price <=', $params['max_price']);
			}

			// Count or get results
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
