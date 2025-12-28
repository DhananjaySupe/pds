<?php namespace App\Models;
	use CodeIgniter\Model;
	class ExpiryTrackerModel extends Model
	{
		protected $table = 'expiry_tracker';
		protected $primaryKey = 'expiry_id';
		protected $returnType = 'array';
		protected $allowedFields = ['expiry_id', 'qr_id', 'product_id', 'location_type', 'location_id', 'expiry_date', 'days_remaining', 'status'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.expiry_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByProductID($product_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.product_id', $product_id);
			$builder->orderBy($this->table . '.expiry_date', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function findByQRID($qr_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.qr_id', $qr_id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByLocation($location_type, $location_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.location_type', $location_type);
			$builder->where($this->table . '.location_id', $location_id);
			$builder->orderBy($this->table . '.expiry_date', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function getNearExpiry($days = 30, $location_type = null, $location_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			// Respect the $days argument (table has virtual status based on 30 days)
			$builder->where('DATEDIFF(' . $this->table . '.expiry_date, CURDATE()) BETWEEN 0 AND ' . intval($days), null, false);

			if ($location_type) {
				$builder->where($this->table . '.location_type', $location_type);
			}

			if ($location_id) {
				$builder->where($this->table . '.location_id', $location_id);
			}

			$builder->orderBy($this->table . '.expiry_date', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function getExpired($location_type = null, $location_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.status', 'expired');

			if ($location_type) {
				$builder->where($this->table . '.location_type', $location_type);
			}

			if ($location_id) {
				$builder->where($this->table . '.location_id', $location_id);
			}

			$builder->orderBy($this->table . '.expiry_date', 'DESC');
			return $builder->get()->getResultArray();
		}

		public function getFresh($location_type = null, $location_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.status', 'fresh');

			if ($location_type) {
				$builder->where($this->table . '.location_type', $location_type);
			}

			if ($location_id) {
				$builder->where($this->table . '.location_id', $location_id);
			}

			$builder->orderBy($this->table . '.expiry_date', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');

			if (isset($params['qr_id'])) {
				$builder->where($this->table . '.qr_id', $params['qr_id']);
			}

			if (isset($params['product_id'])) {
				$builder->where($this->table . '.product_id', $params['product_id']);
			}

			if (isset($params['location_type'])) {
				$builder->where($this->table . '.location_type', $params['location_type']);
			}

			if (isset($params['location_id'])) {
				$builder->where($this->table . '.location_id', $params['location_id']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
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

			if (isset($params['days_remaining_min'])) {
				$builder->where($this->table . '.days_remaining >=', $params['days_remaining_min']);
			}

			if (isset($params['days_remaining_max'])) {
				$builder->where($this->table . '.days_remaining <=', $params['days_remaining_max']);
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
					$builder->orderBy($this->table . '.expiry_date ASC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}

		public function getExpirySummary($location_type = null, $location_id = null)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.status, COUNT(*) as count');
			$builder->groupBy($this->table . '.status');

			if ($location_type) {
				$builder->where($this->table . '.location_type', $location_type);
			}

			if ($location_id) {
				$builder->where($this->table . '.location_id', $location_id);
			}

			$result = $builder->get()->getResultArray();
			$summary = ['fresh' => 0, 'near_expiry' => 0, 'expired' => 0];

			foreach ($result as $row) {
				$summary[$row['status']] = (int)$row['count'];
			}

			return $summary;
		}
	}

