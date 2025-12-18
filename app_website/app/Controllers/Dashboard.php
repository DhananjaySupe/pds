<?php namespace App\Controllers;

	use App\Models\UserModel;
	use App\Models\UserTypeModel;
	use App\Models\UserPublicModel;
	use App\Models\UserAttendanceModel;
	use App\Models\SessionModel;

	use App\Libraries\JwtLib;

	class Dashboard extends BaseController
	{
		public function index()
		{
			if (!$this->isUserLoggedIn()) {
				return redirect()->route('login');
			}
			$this->viewdata['title'] = 'Dashboard';
			return view('dashboard/index', $this->viewdata);
		}
	}