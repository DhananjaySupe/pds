<?php namespace App\Models;
	use CodeIgniter\Model;
	class GodownModel extends Model
	{
		protected $table = 'godowns';
		protected $primaryKey = 'godown_id';
		protected $returnType = 'array';
		protected $allowedFields = ['godown_id', 'user_id', 'godown_name', 'location', 'capacity_sqft', 'contact_person', 'is_active'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.godown_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByUserID($user_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.user_id', $user_id);
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.godown_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function getActiveGodowns()
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.godown_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');

			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.godown_name', $params['keywords'])
					->orLike($this->table . '.location', $params['keywords'])
					->orLike($this->table . '.contact_person', $params['keywords'])
					->orLike('users.full_name', $params['keywords'])
					->orLike('users.email', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['is_active']) && $params['is_active'] !== '') {
				$builder->where($this->table . '.is_active', $params['is_active']);
			}

			if (isset($params['min_capacity'])) {
				$builder->where($this->table . '.capacity_sqft >=', $params['min_capacity']);
			}

			if (isset($params['max_capacity'])) {
				$builder->where($this->table . '.capacity_sqft <=', $params['max_capacity']);
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
					$builder->orderBy($this->table . '.godown_name ASC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}

		public function toggleActive($godown_id)
		{
			$godown = $this->find($godown_id);
			if ($godown) {
				$new_status = $godown['is_active'] == 1 ? 0 : 1;
				return $this->update($godown_id, ['is_active' => $new_status]);
			}
			return false;
		}
	}

