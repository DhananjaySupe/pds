<?php namespace App\Models;
	use CodeIgniter\Model;
	class ShopModel extends Model
	{
		protected $table = 'shops';
		protected $primaryKey = 'shop_id';
		protected $returnType = 'array';
		protected $allowedFields = ['shop_id', 'user_id', 'shop_name', 'location', 'contact_person', 'godown_id', 'is_active'];

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, godowns.godown_name');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->where($this->table . '.shop_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByUserID($user_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, godowns.godown_name');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->where($this->table . '.user_id', $user_id);
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.shop_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function findByGodownID($godown_id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.godown_id', $godown_id);
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.shop_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function getActiveShops()
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, godowns.godown_name');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');
			$builder->where($this->table . '.is_active', 1);
			$builder->orderBy($this->table . '.shop_name', 'ASC');
			return $builder->get()->getResultArray();
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone, godowns.godown_name');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->join('godowns', 'godowns.godown_id = ' . $this->table . '.godown_id', 'left');

			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}

			if (isset($params['godown_id'])) {
				$builder->where($this->table . '.godown_id', $params['godown_id']);
			}

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like($this->table . '.shop_name', $params['keywords'])
					->orLike($this->table . '.location', $params['keywords'])
					->orLike($this->table . '.contact_person', $params['keywords'])
					->orLike('godowns.godown_name', $params['keywords'])
					->orLike('users.full_name', $params['keywords'])
					->orLike('users.email', $params['keywords'])
					->orLike('users.phone', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['is_active']) && $params['is_active'] !== '') {
				$builder->where($this->table . '.is_active', $params['is_active']);
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
					$builder->orderBy($this->table . '.shop_name ASC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}

		public function toggleActive($shop_id)
		{
			$shop = $this->find($shop_id);
			if ($shop) {
				$new_status = $shop['is_active'] == 1 ? 0 : 1;
				return $this->update($shop_id, ['is_active' => $new_status]);
			}
			return false;
		}
	}


