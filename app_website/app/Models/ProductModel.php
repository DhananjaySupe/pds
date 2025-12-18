<?php namespace App\Models;
	use CodeIgniter\Model;
	class ProductModel extends Model
	{
		protected $table = 'products';
		protected $primaryKey = 'product_id';
		protected $returnType = 'array';
		protected $allowedFields = ['product_id', 'product_code', 'product_name', 'description', 'category', 'brand', 'unit_type', 'base_unit', 'standard_quantity_per_unit', 'min_stock_level', 'max_stock_level', 'is_active', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByProductCode($product_code)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_code', $product_code);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function getActiveProducts()
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.product_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.product_name', $params['keywords'])
					->orLike($this->table . '.product_code', $params['keywords'])
					->orLike($this->table . '.category', $params['keywords'])
					->orLike($this->table . '.brand', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['product_code'])) {
				$builder->where($this->table . '.product_code', $params['product_code']);
			}

			if (isset($params['category'])) {
				$builder->where($this->table . '.category', $params['category']);
			}

			if (isset($params['brand'])) {
				$builder->where($this->table . '.brand', $params['brand']);
			}

			if (isset($params['unit_type'])) {
				$builder->where($this->table . '.unit_type', $params['unit_type']);
			}

			if (isset($params['is_active']) && $params['is_active'] !== '') {
				$builder->where($this->table . '.is_active', $params['is_active']);
			}

			if (isset($params['min_stock_level'])) {
				$builder->where($this->table . '.min_stock_level >=', $params['min_stock_level']);
			}

			if (isset($params['max_stock_level'])) {
				$builder->where($this->table . '.max_stock_level <=', $params['max_stock_level']);
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


