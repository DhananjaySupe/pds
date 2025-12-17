<?php namespace App\Models;
	use CodeIgniter\Model;
	class UserRoleModel extends Model
	{
		protected $table = 'user_roles';
		protected $primaryKey = 'role_id';
		protected $returnType = 'array';
		protected $allowedFields = ['role_id', 'name', 'description'];
		protected $createdField = 'created_at';
		protected $updatedField = 'updated_at';

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.role_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');

			if (isset($params['keywords'])) {
				$builder->like($this->table.'.description', $params['keywords']);
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
					$builder->orderBy($this->table . '.role_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}

	}