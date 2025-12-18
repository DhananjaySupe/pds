<?php namespace App\Models;
	use CodeIgniter\Model;
	class UserAttendanceModel extends Model
	{
		protected $table = 'user_attendance';
		protected $primaryKey = 'attendance_id';
		protected $returnType = 'array';
		protected $allowedFields = [
			'attendance_id',
			'user_id', 'year', 'month',
			'day1_in', 'day1_out', 'day2_in', 'day2_out', 'day3_in', 'day3_out', 'day4_in', 'day4_out', 'day5_in', 'day5_out',
			'day6_in', 'day6_out', 'day7_in', 'day7_out', 'day8_in', 'day8_out', 'day9_in', 'day9_out', 'day10_in', 'day10_out',
			'day11_in', 'day11_out', 'day12_in', 'day12_out', 'day13_in', 'day13_out', 'day14_in', 'day14_out', 'day15_in', 'day15_out',
			'day16_in', 'day16_out', 'day17_in', 'day17_out', 'day18_in', 'day18_out', 'day19_in', 'day19_out', 'day20_in', 'day20_out',
			'day21_in', 'day21_out', 'day22_in', 'day22_out', 'day23_in', 'day23_out', 'day24_in', 'day24_out', 'day25_in', 'day25_out',
			'day26_in', 'day26_out', 'day27_in', 'day27_out', 'day28_in', 'day28_out', 'day29_in', 'day29_out', 'day30_in', 'day30_out',
			'day31_in', 'day31_out',
			'total_hours', 'status', 'notes'
		];

		public function recordLogin(int $userId, ?string $date = null): bool
		{
			helper('app');

			$date = $date ?: date('Y-m-d');
			$ym = attendance_year_month($date, 2025);
			$day = (int) date('j', strtotime($date));

			// Auto-close previous day if it has an open "in" without "out"
			$this->autoClosePreviousDayIfOpen($userId, $date);

			$attendance = $this->findByUserMonthYear($userId, (int) $ym['year'], (string) $ym['month']);
			if (!$attendance) {
				$insertData = [
					'user_id' => $userId,
					'year'    => (int) $ym['year'],
					'month'   => (string) $ym['month'],
					'status'  => 1,
				];
				$attendanceId = $this->insert($insertData, true);
				$attendance = $attendanceId ? $this->find($attendanceId) : null;
			}

			if (!$attendance || !isset($attendance['attendance_id'])) {
				return false;
			}

			$inField = 'day' . $day . '_in';
			if (empty($attendance[$inField])) {
				return (bool) $this->update((int) $attendance['attendance_id'], [
					$inField => date('Y-m-d H:i:s'),
				]);
			}

			return true;
		}

		public function recordLogout(int $userId, ?string $date = null): bool
		{
			helper('app');

			$date = $date ?: date('Y-m-d');
			$ym = attendance_year_month($date, 2025);
			$day = (int) date('j', strtotime($date));

			$attendance = $this->findByUserMonthYear($userId, (int) $ym['year'], (string) $ym['month']);
			if (!$attendance) {
				$insertData = [
					'user_id' => $userId,
					'year'    => (int) $ym['year'],
					'month'   => (string) $ym['month'],
					'status'  => 1,
				];
				$attendanceId = $this->insert($insertData, true);
				$attendance = $attendanceId ? $this->find($attendanceId) : null;
			}

			if (!$attendance || !isset($attendance['attendance_id'])) {
				return false;
			}

			$inField  = 'day' . $day . '_in';
			$outField = 'day' . $day . '_out';

			$update = [
				$outField => date('Y-m-d H:i:s'),
			];

			// If there is no "in" time, set it (fallback) so the record isn't half-empty.
			if (empty($attendance[$inField])) {
				$update[$inField] = date('Y-m-d H:i:s');
			}

			return (bool) $this->update((int) $attendance['attendance_id'], $update);
		}

		private function autoClosePreviousDayIfOpen(int $userId, string $todayDate): void
		{
			helper('app');

			$todayTs = strtotime($todayDate);
			if ($todayTs === false) {
				return;
			}

			$yesterdayDate = date('Y-m-d', strtotime('-1 day', $todayTs));
			$ym = attendance_year_month($yesterdayDate, 2025);
			$day = (int) date('j', strtotime($yesterdayDate));

			$attendance = $this->findByUserMonthYear($userId, (int) $ym['year'], (string) $ym['month']);
			if (!$attendance || !isset($attendance['attendance_id'])) {
				return;
			}

			$inField  = 'day' . $day . '_in';
			$outField = 'day' . $day . '_out';

			// Only auto-close if "in" exists and "out" is empty
			if (!empty($attendance[$inField]) && empty($attendance[$outField])) {
				$this->update((int) $attendance['attendance_id'], [
					$outField => $yesterdayDate . ' 23:59:59',
				]);
			}
		}

		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.attendance_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function findByUserMonthYear($user_id, $year, $month)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*');
			$builder->where($this->table . '.user_id', $user_id);
			$builder->where($this->table . '.year', $year);
			$builder->where($this->table . '.month', $month);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name, users.email, users.phone');
			$builder->join('users', 'users.user_id = ' . $this->table . '.user_id', 'left');

			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('users.full_name', $params['keywords'])
					->orLike('users.email', $params['keywords'])
					->orLike('users.phone', $params['keywords'])
					->orLike($this->table . '.status', $params['keywords'])
					->orLike($this->table . '.notes', $params['keywords']);
				$builder->groupEnd();
			}

			if (isset($params['attendance_id'])) {
				$builder->where($this->table . '.attendance_id', $params['attendance_id']);
			}

			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}

			if (isset($params['year'])) {
				$builder->where($this->table . '.year', $params['year']);
			}

			if (isset($params['month'])) {
				$builder->where($this->table . '.month', $params['month']);
			}

			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
			}

			if (isset($params['min_total_hours'])) {
				$builder->where($this->table . '.total_hours >=', $params['min_total_hours']);
			}

			if (isset($params['max_total_hours'])) {
				$builder->where($this->table . '.total_hours <=', $params['max_total_hours']);
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
					$builder->orderBy($this->table . '.year DESC');
					$builder->orderBy($this->table . '.month DESC');
					$builder->orderBy($this->table . '.attendance_id DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}
	}


