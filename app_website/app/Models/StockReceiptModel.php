<?php namespace App\Models;
	use CodeIgniter\Model;
	class StockReceiptModel extends Model
	{
		protected $table = 'stock_receipts';
		protected $primaryKey = 'receipt_id';
		protected $returnType = 'array';
		protected $allowedFields = ['receipt_id', 'receipt_number', 'po_id', 'godown_id', 'vendor_id', 'receipt_date', 'total_items', 'received_by', 'notes', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, po.po_number, godowns.godown_name, vendors.company_name as vendor_company_name, received_user.full_name as received_by_name');
			$builder->join('purchase_orders as po', 'po.po_id = ' . $this->table . '.po_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->join('users as received_user', 'received_user.user_id = ' . $this->table . '.received_by', 'left');
			$builder->where($this->table . '.receipt_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByReceiptNumber($receipt_number)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.receipt_number', $receipt_number);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByPOID($po_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.po_id', $po_id);
			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function findByGodownID($godown_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.godown_id', $godown_id);
			$builder->orderBy($this->table . '.created_at', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, po.po_number, godowns.godown_name, vendors.company_name as vendor_company_name, received_user.full_name as received_by_name');
			$builder->join('purchase_orders as po', 'po.po_id = ' . $this->table . '.po_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->join('vendors', 'vendors.vendor_id = ' . $this->table . '.vendor_id', 'left');
			$builder->join('users as received_user', 'received_user.user_id = ' . $this->table . '.received_by', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.receipt_number', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords'])
					->orLike('po.po_number', $params['keywords'])
					->orLike('godowns.godown_name', $params['keywords'])
					->orLike('vendors.company_name', $params['keywords'])
					->orLike('received_user.full_name', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['receipt_id'])) {
				$builder->where($this->table . '.receipt_id', $params['receipt_id']);
			}

			if (isset($params['receipt_number'])) {
				$builder->where($this->table . '.receipt_number', $params['receipt_number']);
			}

			if (isset($params['po_id'])) {
				$builder->where($this->table . '.po_id', $params['po_id']);
			}

			if (isset($params['godown_id'])) {
				$builder->where($this->table . '.godown_id', $params['godown_id']);
			}

			if (isset($params['vendor_id'])) {
				$builder->where($this->table . '.vendor_id', $params['vendor_id']);
			}

			if (isset($params['received_by'])) {
				$builder->where($this->table . '.received_by', $params['received_by']);
			}

			if (isset($params['receipt_date'])) {
				$builder->where('DATE('.$this->table . '.receipt_date)', $params['receipt_date']);
			}

			if (isset($params['receipt_date_from'])) {
				$builder->where('DATE('.$this->table . '.receipt_date) >=', $params['receipt_date_from']);
			}

			if (isset($params['receipt_date_to'])) {
				$builder->where('DATE('.$this->table . '.receipt_date) <=', $params['receipt_date_to']);
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


