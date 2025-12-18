<?php namespace App\Models;
	use CodeIgniter\Model;
	class ShopRequestItemModel extends Model
	{
		protected $table = 'shop_request_items';
		protected $primaryKey = 'sri_id';
		protected $returnType = 'array';
		protected $allowedFields = ['sri_id', 'request_id', 'product_id', 'quantity', 'fulfilled_quantity', 'priority'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.sri_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByRequestID($request_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.request_id', $request_id);
			$builder->orderBy($this->table . '.priority', 'DESC');
			$builder->orderBy($this->table . '.sri_id', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function findByProductID($product_id, $request_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);
			if ($request_id !== null) {
				$builder->where($this->table . '.request_id', $request_id);
			}
			$builder->orderBy($this->table . '.sri_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function updateFulfilledQuantity($sri_id, $fulfilled_quantity)
		{
			return $this->update($sri_id, ['fulfilled_quantity' => $fulfilled_quantity]);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, req.request_number, req.status as request_status, products.product_code, products.product_name, products.category, products.brand');
			$builder->join('shop_requests as req', 'req.request_id = ' . $this->table . '.request_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('req.request_number', $params['keywords'])
					->orLike('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('products.category', $params['keywords'])
					->orLike('products.brand', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['sri_id'])) {
				$builder->where($this->table . '.sri_id', $params['sri_id']);
			}

			if (isset($params['request_id'])) {
				$builder->where($this->table . '.request_id', $params['request_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['request_status'])) {
				$builder->where('req.status', $params['request_status']);
			}

			if (isset($params['min_quantity'])) {
				$builder->where($this->table . '.quantity >=', $params['min_quantity']);
			}

			if (isset($params['max_quantity'])) {
				$builder->where($this->table . '.quantity <=', $params['max_quantity']);
			}

			if (isset($params['min_fulfilled_quantity'])) {
				$builder->where($this->table . '.fulfilled_quantity >=', $params['min_fulfilled_quantity']);
			}

			if (isset($params['max_fulfilled_quantity'])) {
				$builder->where($this->table . '.fulfilled_quantity <=', $params['max_fulfilled_quantity']);
			}

			if (isset($params['priority'])) {
				$builder->where($this->table . '.priority', $params['priority']);
			}

			if (isset($params['min_priority'])) {
				$builder->where($this->table . '.priority >=', $params['min_priority']);
			}

			if (isset($params['max_priority'])) {
				$builder->where($this->table . '.priority <=', $params['max_priority']);
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
					$builder->orderBy($this->table . '.priority DESC');
					$builder->orderBy($this->table . '.sri_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


