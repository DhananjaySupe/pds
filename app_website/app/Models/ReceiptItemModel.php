<?php namespace App\Models;
	use CodeIgniter\Model;
	class ReceiptItemModel extends Model
	{
		protected $table = 'receipt_items';
		protected $primaryKey = 'ri_id';
		protected $returnType = 'array';
		protected $allowedFields = ['ri_id', 'receipt_id', 'qr_id', 'product_id', 'quantity', 'unit_price', 'expiry_date', 'batch_number'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, qr.qr_code, qr.status as qr_status, products.product_code, products.product_name, products.unit_type, products.base_unit');
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.ri_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByReceiptID($receipt_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, qr.qr_code, qr.status as qr_status, products.product_code, products.product_name, products.unit_type, products.base_unit');
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->where($this->table . '.receipt_id', $receipt_id);
			$builder->orderBy($this->table . '.ri_id', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function findByQRID($qr_id, $receipt_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.qr_id', $qr_id);

			if ($receipt_id !== null) {
				$builder->where($this->table . '.receipt_id', $receipt_id);
			}

			$builder->orderBy($this->table . '.ri_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, qr.qr_code, products.product_code, products.product_name, products.category, products.brand');
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('qr.qr_code', $params['keywords'])
					->orLike($this->table . '.batch_number', $params['keywords'])
					->orLike('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('products.category', $params['keywords'])
					->orLike('products.brand', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['ri_id'])) {
				$builder->where($this->table . '.ri_id', $params['ri_id']);
			}

			if (isset($params['receipt_id'])) {
				$builder->where($this->table . '.receipt_id', $params['receipt_id']);
			}

			if (isset($params['qr_id'])) {
				$builder->where($this->table . '.qr_id', $params['qr_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['batch_number'])) {
				$builder->where($this->table . '.batch_number', $params['batch_number']);
			}

			if (isset($params['expiry_date'])) {
				$builder->where('DATE('.$this->table . '.expiry_date)', $params['expiry_date']);
			}

			if (isset($params['expiry_date_from'])) {
				$builder->where('DATE('.$this->table . '.expiry_date) >=', $params['expiry_date_from']);
			}

			if (isset($params['expiry_date_to'])) {
				$builder->where('DATE('.$this->table . '.expiry_date) <=', $params['expiry_date_to']);
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

			if (isset($params['count']) && $params['count']) {
				return $builder->countAllResults();
			} else {
				if (isset($params['limit'])) {
					$builder->limit($params['limit']['length'], $params['limit']['offset']);
				}

				if(isset($params['sort']['column']) && !empty($params['sort']['column']) && isset($params['sort']['order']) && !empty($params['sort']['order'])){
					$builder->orderBy($params['sort']['column'], $params['sort']['order']);
				} else {
					$builder->orderBy($this->table . '.ri_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


