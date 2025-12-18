<?php namespace App\Models;
	use CodeIgniter\Model;
	class UserTypeModel extends Model
	{
		protected $table = 'user_types';
		protected $primaryKey = 'user_type_id';
		protected $returnType = 'array';
		protected $allowedFields = ['user_type_id', 'type_name', 'type_description', 'permissions_level', 'is_active', 'created_at'];
		protected $createdField = 'created_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.user_type_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByTypeName($type_name)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.type_name', $type_name);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function getActiveTypes()
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.type_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.type_name', $params['keywords'])
					->orLike($this->table . '.type_description', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['is_active']) && $params['is_active'] !== '') {
				$builder->where($this->table . '.is_active', $params['is_active']);
			}

			if (isset($params['permissions_level'])) {
				$builder->where($this->table . '.permissions_level', $params['permissions_level']);
			}

			if (isset($params['min_permissions_level'])) {
				$builder->where($this->table . '.permissions_level >=', $params['min_permissions_level']);
			}

			if (isset($params['max_permissions_level'])) {
				$builder->where($this->table . '.permissions_level <=', $params['max_permissions_level']);
			}

			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where('DATE('.$this->table . '.created_at) >=', $params['start_date']);
				$builder->where('DATE('.$this->table . '.created_at) <=', $params['end_date']);
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

		public function toggleActive($user_type_id)
		{
			$user_type = $this->find($user_type_id);
			if ($user_type) {
				$new_status = $user_type['is_active'] == 1 ? 0 : 1;
				return $this->update($user_type_id, ['is_active' => $new_status]);
			}
			return false;
		}
	}

