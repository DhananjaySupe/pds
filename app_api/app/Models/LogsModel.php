<?php namespace App\Models;
	use CodeIgniter\Model;
	class LogsModel extends Model
	{
		protected $table = 'logs';
		protected $primaryKey = 'log_id';
		protected $returnType = 'array';
		protected $allowedFields = ['log_id', 'table_name', 'record_id', 'operation_type', 'old_data', 'new_data', 'created_by', 'created_at'];
		protected $createdField = 'created_at';
		protected $updatedField = '';

		/**
		 * Find log record by ID with user details
		 */
		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*,users.full_name as created_by_user');
			$builder->join('users as users', 'users.user_id = ' . $this->table . '.created_by', 'left');
			$builder->where($this->table . '.log_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		/**
		 * Get logs for a specific table and record
		 */
		public function getRecordLogs($table_name, $record_id)
		{
			return $this->where('table_name', $table_name)
						->where('record_id', $record_id)
						->orderBy('created_at', 'DESC')
						->findAll();
		}

		/**
		 * Get logs by operation type
		 */
		public function getLogsByOperation($operation_type, $limit = 100)
		{
			return $this->where('operation_type', $operation_type)
						->orderBy('created_at', 'DESC')
						->limit($limit)
						->findAll();
		}

		/**
		 * Get logs by user
		 */
		public function getLogsByUser($user_id, $limit = 100)
		{
			return $this->where('created_by', $user_id)
						->orderBy('created_at', 'DESC')
						->limit($limit)
						->findAll();
		}

		/**
		 * Search logs with enhanced functionality
		 */
		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name as created_by_user');
			$builder->join('users as users', 'users.user_id = ' . $this->table . '.created_by', 'left');

			// Date range filtering
			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE('.$this->table . '.created_at) >=', $params['start_date']);
				$builder->where('DATE('.$this->table . '.created_at) <=', $params['end_date']);
			}

			// Date filtering
			if (isset($params['date'])) {
				$builder->where('DATE('.$this->table . '.created_at)', $params['date']);
			}

			// Keywords search
			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.table_name', $params['keywords']);
				$builder->orLike($this->table . '.record_id', $params['keywords']);
				$builder->orLike('users.full_name', $params['keywords']);
				$builder->groupEnd();
			}

			// Table name filtering
			if (isset($params['table_name'])) {
				$builder->where($this->table . '.table_name', $params['table_name']);
			}

			// Record ID filtering
			if (isset($params['record_id'])) {
				$builder->where($this->table . '.record_id', $params['record_id']);
			}

			// Operation type filtering
			if (isset($params['operation_type'])) {
				$builder->where($this->table . '.operation_type', $params['operation_type']);
			}

			// User filtering
			if (isset($params['created_by'])) {
				$builder->where($this->table . '.created_by', $params['created_by']);
			}

			// Count or get results
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

		/**
		 * Get logs statistics
		 */
		public function getLogsStats($params = array())
		{
			$builder = $this->db->table($this->table);

			// Date range filtering
			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE(created_at) >=', $params['start_date']);
				$builder->where('DATE(created_at) <=', $params['end_date']);
			}

			// Table name filtering
			if (isset($params['table_name'])) {
				$builder->where('table_name', $params['table_name']);
			}

			// User filtering
			if (isset($params['created_by'])) {
				$builder->where('created_by', $params['created_by']);
			}

			$builder->select('operation_type, COUNT(*) as count');
			$builder->groupBy('operation_type');
			$result = $builder->get()->getResultArray();

			$stats = [
				'INSERT' => 0,
				'UPDATE' => 0,
				'DELETE' => 0,
				'total' => 0
			];

			foreach ($result as $row) {
				$stats[$row['operation_type']] = $row['count'];
				$stats['total'] += $row['count'];
			}

			return $stats;
		}

		/**
		 * Get activity timeline
		 */
		public function getActivityTimeline($table_name = null, $record_id = null, $limit = 50)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name as created_by_user');
			$builder->join('users as users', 'users.user_id = ' . $this->table . '.created_by', 'left');

			if ($table_name) {
				$builder->where($this->table . '.table_name', $table_name);
			}

			if ($record_id) {
				$builder->where($this->table . '.record_id', $record_id);
			}

			$builder->orderBy($this->table . '.created_at', 'DESC');
			$builder->limit($limit);

			return $builder->get()->getResultArray();
		}

		/**
		 * Clean old logs
		 */
		public function cleanOldLogs($days = 90)
		{
			$date = date('Y-m-d', strtotime("-{$days} days"));
			return $this->where('DATE(created_at) <', $date)->delete();
		}
	}

