<?php namespace App\Models;
	use CodeIgniter\Model;
	class PurchaseOrderItemModel extends Model
	{
		protected $table = 'purchase_order_items';
		protected $primaryKey = 'poi_id';
		protected $returnType = 'array';
		protected $allowedFields = ['poi_id', 'po_id', 'product_id', 'quantity', 'unit_price', 'tax_amount', 'discount_amount','total_price'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.poi_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByPOID($po_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.po_id', $po_id);
			$builder->orderBy($this->table . '.poi_id', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function findByProductID($product_id, $po_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);

			if ($po_id !== null) {
				$builder->where($this->table . '.po_id', $po_id);
			}

			$builder->orderBy($this->table . '.poi_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.category, products.brand');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('products.category', $params['keywords'])
					->orLike('products.brand', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['poi_id'])) {
				$builder->where($this->table . '.poi_id', $params['poi_id']);
			}

			if (isset($params['po_id'])) {
				$builder->where($this->table . '.po_id', $params['po_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['min_quantity'])) {
				$builder->where($this->table . '.quantity >=', $params['min_quantity']);
			}

			if (isset($params['max_quantity'])) {
				$builder->where($this->table . '.quantity <=', $params['max_quantity']);
			}

			if (isset($params['min_unit_price'])) {
				$builder->where($this->table . '.unit_price >=', $params['min_unit_price']);
			}

			if (isset($params['max_unit_price'])) {
				$builder->where($this->table . '.unit_price <=', $params['max_unit_price']);
			}

			if (isset($params['min_total_price'])) {
				$builder->where($this->table . '.total_price >=', $params['min_total_price']);
			}

			if (isset($params['max_total_price'])) {
				$builder->where($this->table . '.total_price <=', $params['max_total_price']);
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
					$builder->orderBy($this->table . '.poi_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


