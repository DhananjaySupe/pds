<?php namespace App\Models;
	use CodeIgniter\Model;
	class QrCodeModel extends Model
	{
		protected $table = 'qr_codes';
		protected $primaryKey = 'qr_id';
		protected $returnType = 'array';
		protected $allowedFields = ['qr_id', 'qr_code', 'po_id', 'product_id', 'vendor_id', 'original_quantity', 'current_quantity', 'batch_number', 'manufacturing_date', 'expiry_date', 'purchase_price', 'mrp', 'status', 'created_at', 'last_updated'];
		protected $createdField = 'created_at';
		protected $updatedField = 'last_updated';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit, vendors.company_name as vendor_company_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->where($this->table . '.qr_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByQRCode($qr_code)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.unit_type, products.base_unit, vendors.company_name as vendor_company_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->where($this->table . '.qr_code', $qr_code);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByProductID($product_id, $status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);

			if ($status !== null) {
				$builder->where($this->table . '.status', $status);
			}

			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function findByVendorID($vendor_id, $status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.vendor_id', $vendor_id);

			if ($status !== null) {
				$builder->where($this->table . '.status', $status);
			}

			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function getExpiringSoon($days = 30, $status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, vendors.company_name as vendor_company_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->where($this->table . '.expiry_date IS NOT NULL', null, false);
			$builder->where($this->table . '.expiry_date >=', date('Y-m-d'));
			$builder->where($this->table . '.expiry_date <=', date('Y-m-d', strtotime('+' . intval($days) . ' days')));

			if ($status !== null) {
				$builder->where($this->table . '.status', $status);
			}

			$builder->orderBy($this->table . '.expiry_date', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function updateQuantity($qr_id, $new_current_quantity)
		{
			return $this->update($qr_id, ['current_quantity' => $new_current_quantity]);
		}

		public function updateStatus($qr_id, $status)
		{
			return $this->update($qr_id, ['status' => $status]);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, products.product_code, products.product_name, products.category, products.brand, vendors.company_name as vendor_company_name');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.qr_code', $params['keywords'])
					->orLike($this->table . '.batch_number', $params['keywords'])
					->orLike('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('products.category', $params['keywords'])
					->orLike('products.brand', $params['keywords'])
					->orLike('vendors.company_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['qr_id'])) {
				$builder->where($this->table . '.qr_id', $params['qr_id']);
			}

			if (isset($params['qr_code'])) {
				$builder->where($this->table . '.qr_code', $params['qr_code']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['vendor_id'])) {
				$builder->where($this->table . '.vendor_id', $params['vendor_id']);
			}

			if (isset($params['po_id'])) {
				$builder->where($this->table . '.po_id', $params['po_id']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			if (isset($params['batch_number'])) {
				$builder->where($this->table . '.batch_number', $params['batch_number']);
			}

			if (isset($params['manufacturing_date'])) {
				$builder->where('DATE('.$this->table . '.manufacturing_date)', $params['manufacturing_date']);
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

			if (isset($params['min_current_quantity'])) {
				$builder->where($this->table . '.current_quantity >=', $params['min_current_quantity']);
			}

			if (isset($params['max_current_quantity'])) {
				$builder->where($this->table . '.current_quantity <=', $params['max_current_quantity']);
			}

			if (isset($params['min_mrp'])) {
				$builder->where($this->table . '.mrp >=', $params['min_mrp']);
			}

			if (isset($params['max_mrp'])) {
				$builder->where($this->table . '.mrp <=', $params['max_mrp']);
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


