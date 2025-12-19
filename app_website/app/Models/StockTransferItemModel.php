<?php namespace App\Models;
	use CodeIgniter\Model;
	class StockTransferItemModel extends Model
	{
		protected $table = 'stock_transfer_items';
		protected $primaryKey = 'ti_id';
		protected $returnType = 'array';
		protected $allowedFields = ['ti_id', 'transfer_id', 'qr_id', 'product_id', 'quantity', 'source_stock_id'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' tr.transfer_number, tr.status as transfer_status,' .
				' qr.qr_code, qr.batch_number, qr.expiry_date as qr_expiry_date, qr.status as qr_status,' .
				' products.product_code, products.product_name, products.unit_type, products.base_unit,' .
				' src.location_type as source_location_type, src.location_id as source_location_id, src.stock_date as source_stock_date, src.is_available as source_is_available'
			);
			$builder->join('stock_transfers as tr', 'tr.transfer_id = ' . $this->table . '.transfer_id', 'left');
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('stock_inventory as src', 'src.stock_id = ' . $this->table . '.source_stock_id', 'left');
			$builder->where($this->table . '.ti_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByTransferID($transfer_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' qr.qr_code, qr.batch_number, qr.expiry_date as qr_expiry_date, qr.status as qr_status,' .
				' products.product_code, products.product_name, products.unit_type, products.base_unit,' .
				' src.location_type as source_location_type, src.location_id as source_location_id, src.stock_date as source_stock_date, src.is_available as source_is_available'
			);
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');
			$builder->join('stock_inventory as src', 'src.stock_id = ' . $this->table . '.source_stock_id', 'left');
			$builder->where($this->table . '.transfer_id', $transfer_id);
			$builder->orderBy($this->table . '.ti_id', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function findByQRID($qr_id, $transfer_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.qr_id', $qr_id);
			if ($transfer_id !== null) {
				$builder->where($this->table . '.transfer_id', $transfer_id);
			}
			$builder->orderBy($this->table . '.ti_id', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select(
				$this->table . '.*,' .
				' tr.transfer_number, tr.status as transfer_status,' .
				' qr.qr_code, qr.batch_number,' .
				' products.product_code, products.product_name, products.category, products.brand'
			);
			$builder->join('stock_transfers as tr', 'tr.transfer_id = ' . $this->table . '.transfer_id', 'left');
			$builder->join('qr_codes as qr', 'qr.qr_id = ' . $this->table . '.qr_id', 'left');
			$builder->join('products', 'products.product_id = ' . $this->table . '.product_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('tr.transfer_number', $params['keywords'])
					->orLike('qr.qr_code', $params['keywords'])
					->orLike('qr.batch_number', $params['keywords'])
					->orLike('products.product_name', $params['keywords'])
					->orLike('products.product_code', $params['keywords'])
					->orLike('products.category', $params['keywords'])
					->orLike('products.brand', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['ti_id'])) {
				$builder->where($this->table . '.ti_id', $params['ti_id']);
			}

			if (isset($params['transfer_id'])) {
				$builder->where($this->table . '.transfer_id', $params['transfer_id']);
			}

			if (isset($params['qr_id'])) {
				$builder->where($this->table . '.qr_id', $params['qr_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['source_stock_id'])) {
				$builder->where($this->table . '.source_stock_id', $params['source_stock_id']);
			}

			if (isset($params['transfer_status'])) {
				$builder->where('tr.status', $params['transfer_status']);
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
					$builder->orderBy($this->table . '.ti_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


