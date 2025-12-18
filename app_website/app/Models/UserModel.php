<?php namespace App\Models;
	use CodeIgniter\Model;
	class UserModel extends Model
	{
		protected $table = 'users';
		protected $primaryKey = 'user_id';
		protected $returnType = 'array';
		protected $allowedFields = ['user_id', 'user_type_id', 'full_name', 'phone', 'email', 'password_hash', 'status', 'otp', 'otp_expiry', 'otp_attempts', 'last_login_at', 'last_login_ip', 'fcm_token', 'code', 'language', 'profile_photo', 'created_by', 'created_at', 'updated_by', 'updated_at'];
		protected $createdField = 'created_at';
		protected $updatedField = 'updated_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*,user_types.type_name as user_type');
			$builder->join('user_types as user_types', 'user_types.user_type_id = ' . $this->table . '.user_type_id', 'left');
			$builder->where($this->table . '.user_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}


		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, user_types.type_name as user_type');
			$builder->join('user_types as user_types', 'user_types.user_type_id = ' . $this->table . '.user_type_id', 'left');

			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE('.$this->table . '.created_at) >=', $params['start_date']);
				$builder->where('DATE('.$this->table . '.created_at) <=', $params['end_date']);
			}

			if (isset($params['keywords'])) {
				$builder->like($this->table.'.full_name', $params['keywords'])
					->orLike($this->table.'.email', $params['keywords'])
					->orLike($this->table.'.phone', $params['keywords']);
			}

			if (isset($params['email'])) {
				$builder->where($this->table . '.email', $params['email']);
			}

			if (isset($params['phone'])) {
				$builder->where($this->table . '.phone', $params['phone']);
			}

			if (isset($params['user_type_id'])) {
				$builder->where($this->table . '.user_type_id', $params['user_type_id']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			if (isset($params['date'])) {
				$builder->where('DATE('.$this->table . '.created_at)', $params['date']);
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

		public function slug($name)
		{
			$slugify = slugify($name);
			$slug = $slugify;
			$next = 2;
			$builder = $this->db->table($this->table);
			while ($builder->select('slug')->where('slug', $slug)->get()->getFirstRow()) {
				$slug = $slugify . '--' . $next;
				$next++;
				$builder->resetQuery();
			}
			return $slug;
		}
	}