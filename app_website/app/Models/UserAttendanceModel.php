<?php namespace App\Models;
	use CodeIgniter\Model;
	class UserAttendanceModel extends Model
	{
		protected $table = 'user_attendance';
		protected $primaryKey = 'attendance_id';
		protected $returnType = 'array';
		protected $allowedFields = ['attendance_id', 'user_id', 'year', 'month', 'day1_in', 'day1_out', 'day2_in', 'day2_out', 'day3_in', 'day3_out', 'day4_in', 'day4_out', 'day5_in', 'day5_out', 'day6_in', 'day6_out', 'day7_in', 'day7_out', 'day8_in', 'day8_out', 'day9_in', 'day9_out', 'day10_in', 'day10_out', 'day11_in', 'day11_out', 'day12_in', 'day12_out', 'day13_in', 'day13_out', 'day14_in', 'day14_out', 'day15_in', 'day15_out', 'day16_in', 'day16_out', 'day17_in', 'day17_out', 'day18_in', 'day18_out', 'day19_in', 'day19_out', 'day20_in', 'day20_out', 'day21_in', 'day21_out', 'day22_in', 'day22_out', 'day23_in', 'day23_out', 'day24_in', 'day24_out', 'day25_in', 'day25_out', 'day26_in', 'day26_out', 'day27_in', 'day27_out', 'day28_in', 'day28_out', 'day29_in', 'day29_out', 'day30_in', 'day30_out', 'day31_in', 'day31_out', 'total_hours', 'status', 'notes'];
		protected $createdField = 'year';
		protected $updatedField = 'year';

		/**
		 * Find attendance record by ID with user details
		 */
		public function findByID($id)
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*,users.full_name as user');
			$builder->join('user_login as users', 'users.user_id = ' . $this->table . '.user_id', 'left');
			$builder->where($this->table . '.attendance_id', $id);
			$builder->limit(1);
			$result = $builder->get()->getResultArray();
			return $result ? $result[0] : null;
		}

		/**
		 * Get monthly attendance for a specific user and month
		 */
		public function getMonthlyAttendance($user_id, $year, $month)
		{
			return $this->where('user_id', $user_id)
						->where('year', $year)
						->where('month', $month)
						->first();
		}

		/**
		 * Get or create monthly attendance record
		 */
		public function getOrCreateMonthlyAttendance($user_id, $year, $month)
		{
			$attendance = $this->getMonthlyAttendance($user_id, $year, $month);

			if (!$attendance) {
				$attendance = [
					'user_id' => $user_id,
					'year' => $year,
					'month' => $month,
					'total_hours' => 0,
					'status' => 'active',
					'notes' => ''
				];
				$attendance_id = $this->insert($attendance);
				$attendance['attendance_id'] = $attendance_id;
			}

			return $attendance;
		}

		/**
		 * Record login time for a specific day
		 */
		public function recordLogin($user_id, $date, $time = null)
		{
			if (!$time) {
				$time = date('H:i:s');
			}

			$year = date('Y', strtotime($date));
			$month = date('n', strtotime($date));
			$day = date('j', strtotime($date));

			$attendance = $this->getOrCreateMonthlyAttendance($user_id, $year, $month);
			$day_field = 'day' . $day . '_in';

			// Only update if not already recorded
			if (empty($attendance[$day_field])) {
				$update_data = [$day_field => $time];
				$this->update($attendance['attendance_id'], $update_data);
			}

			return $attendance['attendance_id'];
		}

		/**
		 * Record logout time for a specific day
		 */
		public function recordLogout($user_id, $date, $time = null)
		{
			if (!$time) {
				$time = date('H:i:s');
			}

			$year = date('Y', strtotime($date));
			$month = date('n', strtotime($date));
			$day = date('j', strtotime($date));

			$attendance = $this->getOrCreateMonthlyAttendance($user_id, $year, $month);
			$day_field = 'day' . $day . '_out';

			$update_data = [$day_field => $time];
			$this->update($attendance['attendance_id'], $update_data);

			// Calculate and update total hours for the day
			$this->calculateDayHours($attendance['attendance_id'], $day);

			return $attendance['attendance_id'];
		}

		/**
		 * Calculate hours worked for a specific day
		 */
		public function calculateDayHours($attendance_id, $day)
		{
			$attendance = $this->find($attendance_id);
			$in_field = 'day' . $day . '_in';
			$out_field = 'day' . $day . '_out';

			if (!empty($attendance[$in_field]) && !empty($attendance[$out_field])) {
				$in_time = strtotime($attendance[$in_field]);
				$out_time = strtotime($attendance[$out_field]);

				if ($out_time > $in_time) {
					$hours = ($out_time - $in_time) / 3600;
					return round($hours, 2);
				}
			}

			return 0;
		}

		/**
		 * Calculate total hours for the month
		 */
		public function calculateMonthlyHours($attendance_id)
		{
			$attendance = $this->find($attendance_id);
			$total_hours = 0;

			for ($day = 1; $day <= 31; $day++) {
				$in_field = 'day' . $day . '_in';
				$out_field = 'day' . $day . '_out';

				if (!empty($attendance[$in_field]) && !empty($attendance[$out_field])) {
					$in_time = strtotime($attendance[$in_field]);
					$out_time = strtotime($attendance[$out_field]);

					if ($out_time > $in_time) {
						$hours = ($out_time - $in_time) / 3600;
						$total_hours += $hours;
					}
				}
			}

			$this->update($attendance_id, ['total_hours' => round($total_hours, 2)]);
			return round($total_hours, 2);
		}

		/**
		 * Get attendance summary for a user
		 */
		public function getAttendanceSummary($user_id, $year = null, $month = null)
		{
			if (!$year) $year = date('Y');
			if (!$month) $month = date('n');

			$attendance = $this->getMonthlyAttendance($user_id, $year, $month);

			if (!$attendance) {
				return [
					'total_days' => 0,
					'total_hours' => 0,
					'present_days' => 0,
					'absent_days' => 0
				];
			}

			$present_days = 0;
			$total_hours = 0;

			for ($day = 1; $day <= 31; $day++) {
				$in_field = 'day' . $day . '_in';
				$out_field = 'day' . $day . '_out';

				if (!empty($attendance[$in_field])) {
					$present_days++;

					if (!empty($attendance[$out_field])) {
						$in_time = strtotime($attendance[$in_field]);
						$out_time = strtotime($attendance[$out_field]);

						if ($out_time > $in_time) {
							$hours = ($out_time - $in_time) / 3600;
							$total_hours += $hours;
						}
					}
				}
			}

			$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));

			return [
				'total_days' => $days_in_month,
				'total_hours' => round($total_hours, 2),
				'present_days' => $present_days,
				'absent_days' => $days_in_month - $present_days
			];
		}

		/**
		 * Search attendance records with enhanced functionality
		 */
		public function search($params = array())
		{
			$builder = $this->db->table($this->table);
			$builder->select($this->table . '.*, users.full_name as user');
			$builder->join('user_login as users', 'users.user_id = ' . $this->table . '.user_id', 'left');

			// Date range filtering
			if (isset($params['start_date']) && isset($params['end_date'])) {
				$builder->where($this->table . '.year >=', date('Y', strtotime($params['start_date'])));
				$builder->where($this->table . '.month >=', date('n', strtotime($params['start_date'])));
				$builder->where($this->table . '.year <=', date('Y', strtotime($params['end_date'])));
				$builder->where($this->table . '.month <=', date('n', strtotime($params['end_date'])));
			}

			// Year and month filtering
			if (isset($params['year'])) {
				$builder->where($this->table . '.year', $params['year']);
			}
			if (isset($params['month'])) {
				$builder->where($this->table . '.month', $params['month']);
			}

			// Keywords search
			if (isset($params['keywords'])) {
				$builder->groupStart();
				$builder->like('users.full_name', $params['keywords']);
				$builder->orLike($this->table . '.notes', $params['keywords']);
				$builder->groupEnd();
			}

			// User filtering
			if (isset($params['user_id'])) {
				$builder->where($this->table . '.user_id', $params['user_id']);
			}

			// Status filtering
			if (isset($params['status'])) {
				$builder->where($this->table . '.status', $params['status']);
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
					$builder->orderBy($this->table . '.year DESC, ' . $this->table . '.month DESC');
				}

				$query = $builder->get();
				return $query->getResultArray();
			}
		}

		/**
		 * Get attendance statistics for reporting
		 */
		public function getAttendanceStats($user_id = null, $year = null, $month = null)
		{
			if (!$year) $year = date('Y');
			if (!$month) $month = date('n');

			$builder = $this->db->table($this->table);
			$builder->select('COUNT(*) as total_records, SUM(total_hours) as total_hours, AVG(total_hours) as avg_hours');
			$builder->where('year', $year);
			$builder->where('month', $month);

			if ($user_id) {
				$builder->where('user_id', $user_id);
			}

			$result = $builder->get()->getRowArray();

			return [
				'total_records' => $result['total_records'] ?? 0,
				'total_hours' => round($result['total_hours'] ?? 0, 2),
				'avg_hours' => round($result['avg_hours'] ?? 0, 2)
			];
		}
	}