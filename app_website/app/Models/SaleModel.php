<?php namespace App\Models;
	use CodeIgniter\Model;
	class SaleModel extends Model
	{
		protected $table = 'sales';
		protected $primaryKey = 'sale_id';
		protected $returnType = 'array';
		protected $allowedFields = ['sale_id', 'invoice_number', 'location_type', 'location_id', 'customer_id', 'total_amount', 'tax_amount', 'discount_amount', 'final_amount', 'payment_method', 'payment_status', 'sale_date', 'sold_by', 'notes', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, customers.user_id as customer_user_id, cust_user.full_name as customer_name, cust_user.email as customer_email, cust_user.phone as customer_phone, sold_user.full_name as sold_by_name');
			$builder->join('customers', 'customers.customer_id = ' . $this->table . '.customer_id', 'left');
			$builder->join('users as cust_user', 'cust_user.user_id = customers.user_id', 'left');
			$builder->join('users as sold_user', 'sold_user.user_id = ' . $this->table . '.sold_by', 'left');
			$builder->where($this->table . '.sale_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByInvoiceNumber($invoice_number)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.invoice_number', $invoice_number);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function getByCustomerID($customer_id, $payment_status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.customer_id', $customer_id);
			if ($payment_status !== null) {
				$builder->where($this->table . '.payment_status', $payment_status);
			}
			$builder->orderBy($this->table . '.sale_date', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function getByLocation($location_type, $location_id, $payment_status = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.location_type', $location_type);
			$builder->where($this->table . '.location_id', $location_id);
			if ($payment_status !== null) {
				$builder->where($this->table . '.payment_status', $payment_status);
			}
			$builder->orderBy($this->table . '.sale_date', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function updatePaymentStatus($sale_id, $payment_status, $payment_method = null)
		{
			$data = ['payment_status' => $payment_status];
			if ($payment_method !== null) {
				$data['payment_method'] = $payment_method;
			}
			return $this->update($sale_id, $data);
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, customers.user_id as customer_user_id, cust_user.full_name as customer_name, cust_user.email as customer_email, cust_user.phone as customer_phone, sold_user.full_name as sold_by_name');
			$builder->join('customers', 'customers.customer_id = ' . $this->table . '.customer_id', 'left');
			$builder->join('users as cust_user', 'cust_user.user_id = customers.user_id', 'left');
			$builder->join('users as sold_user', 'sold_user.user_id = ' . $this->table . '.sold_by', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.invoice_number', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords'])
					->orLike('cust_user.full_name', $params['keywords'])
					->orLike('cust_user.email', $params['keywords'])
					->orLike('cust_user.phone', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['sale_id'])) {
				$builder->where($this->table . '.sale_id', $params['sale_id']);
			}

			if (isset($params['invoice_number'])) {
				$builder->where($this->table . '.invoice_number', $params['invoice_number']);
			}

			if (isset($params['location_type'])) {
				$builder->where($this->table . '.location_type', $params['location_type']);
			}

			if (isset($params['location_id'])) {
				$builder->where($this->table . '.location_id', $params['location_id']);
			}

			if (isset($params['customer_id'])) {
				$builder->where($this->table . '.customer_id', $params['customer_id']);
			}

			if (isset($params['sold_by'])) {
				$builder->where($this->table . '.sold_by', $params['sold_by']);
			}

			if (isset($params['payment_method'])) {
				$builder->where($this->table . '.payment_method', $params['payment_method']);
			}

			if (isset($params['payment_status'])) {
				$builder->where($this->table . '.payment_status', $params['payment_status']);
			}

			if (isset($params['sale_date'])) {
				$builder->where('DATE('.$this->table . '.sale_date)', $params['sale_date']);
			}

			if (isset($params['sale_date_from'])) {
				$builder->where('DATE('.$this->table . '.sale_date) >=', $params['sale_date_from']);
			}

			if (isset($params['sale_date_to'])) {
				$builder->where('DATE('.$this->table . '.sale_date) <=', $params['sale_date_to']);
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
					$builder->orderBy($this->table . '.sale_date DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


