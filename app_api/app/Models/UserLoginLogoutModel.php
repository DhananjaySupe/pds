<?php namespace App\Models;
	use CodeIgniter\Model;
	class UserLoginLogoutModel extends Model
	{
		protected $table = 'user_login_logout';
		protected $primaryKey = 'log_id';
		protected $returnType = 'array';
		protected $allowedFields = ['log_id', 'user_id', 'action', 'date_time', 'device_ip'];
		protected $createdField = 'date_time';
		protected $updatedField = 'date_time';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*,users.full_name as user');
			$builder->join('user_login as users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.log_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}


		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name as user');
			$builder->join('user_login as users', 'users.user_id = ' . $this->table . '.user_id', 'left');

			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE('.$this->table . '.date_time) >=', $params['start_date']);
				$builder->where('DATE('.$this->table . '.date_time) <=', $params['end_date']);
			}

			if (isset($params['keywords'])) {
				$builder->like($this->table.'.action', $params['keywords']);
			}

			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}
			if (isset($params['date'])) {
				$builder->where('DATE('.$this->table . '.date_time)', $params['date']);
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
					$builder->orderBy($this->table . '.date_time DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}