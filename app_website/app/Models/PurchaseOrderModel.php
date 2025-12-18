<?php namespace App\Models;
	use CodeIgniter\Model;
	class PurchaseOrderModel extends Model
	{
		protected $table = 'purchase_orders';
		protected $primaryKey = 'po_id';
		protected $returnType = 'array';
		protected $allowedFields = ['po_id', 'po_number', 'godown_id', 'vendor_id', 'total_amount', 'tax_amount', 'discount_amount', 'final_amount', 'status', 'order_date', 'expected_delivery_date', 'actual_delivery_date', 'notes', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, vendors.company_name as vendor_company_name');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->where($this->table . '.po_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByPONumber($po_number)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, vendors.company_name as vendor_company_name');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->where($this->table . '.po_number', $po_number);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function getByStatus($status, $godown_id = null, $vendor_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, vendors.company_name as vendor_company_name');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->where($this->table . '.status', $status);

			if ($godown_id !== null) {
				$builder->where($this->table . '.godown_id', $godown_id);
			}

			if ($vendor_id !== null) {
				$builder->where($this->table . '.vendor_id', $vendor_id);
			}

			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function updateStatus($po_id, $status)
		{
			return $this->update($po_id, ['status' => $status]);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, vendors.company_name as vendor_company_name');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.po_number', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords'])
					->orLike('vendors.company_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['po_id'])) {
				$builder->where($this->table . '.po_id', $params['po_id']);
			}

			if (isset($params['po_number'])) {
				$builder->where($this->table . '.po_number', $params['po_number']);
			}

			if (isset($params['godown_id'])) {
				$builder->where($this->table . '.godown_id', $params['godown_id']);
			}

			if (isset($params['vendor_id'])) {
				$builder->where($this->table . '.vendor_id', $params['vendor_id']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			if (isset($params['order_date'])) {
				$builder->where('DATE('.$this->table . '.order_date)', $params['order_date']);
			}

			if (isset($params['order_date_from'])) {
				$builder->where('DATE('.$this->table . '.order_date) >=', $params['order_date_from']);
			}

			if (isset($params['order_date_to'])) {
				$builder->where('DATE('.$this->table . '.order_date) <=', $params['order_date_to']);
			}

			if (isset($params['expected_delivery_date'])) {
				$builder->where('DATE('.$this->table . '.expected_delivery_date)', $params['expected_delivery_date']);
			}

			if (isset($params['actual_delivery_date'])) {
				$builder->where('DATE('.$this->table . '.actual_delivery_date)', $params['actual_delivery_date']);
			}

			if (isset($params['min_final_amount'])) {
				$builder->where($this->table . '.final_amount >=', $params['min_final_amount']);
			}

			if (isset($params['max_final_amount'])) {
				$builder->where($this->table . '.final_amount <=', $params['max_final_amount']);
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


