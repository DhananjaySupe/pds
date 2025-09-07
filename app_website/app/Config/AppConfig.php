<?php namespace Config;

	use CodeIgniter\Config\BaseConfig;

	class AppConfig extends BaseConfig
	{
		public $appName = 'Lost and Found';
		public $appDesc = "Lost and Found";
		public $appEmails = array();
		public $defaultCSS = array();
		public $defaultJS = array();
		public $defaultMETA = array();
		public $cssVersion = '1.0.0';
		public $jsVersion = '1.0.0';
		/* App */
		public $appDownloadUrl = 'https://play.google.com/store/apps/details?id=com.kashit.lostandfound';
		public $appDownloadUrlQr = 'assets/images/appqrcode/appurl.webp';
		/* JWT */
		public $jwt_secret = 'VNyLbLP7aGg9YKZXlshZqkRFahRLgf1L';
		public $jwt_expiry = 36000; // in sec - 10 hours

		/* Session */
		public $single_login = true; // true or false

		public $imageSizes = array(
        'large' => array(800, 600),
        'thumb' => array(340, 255),
		);

		public $twoFactorAuth = array(
			'enabled' => false,
			'send' => array(
				'email' => true,
				'sms' => true,
				'whatsapp' => true,
			),
		);

		 // Bhashini API configuration
		 public $bhashiniConfig = [
			'asrEndpoint' => 'https://asr.bhashini.ai/api/v1/recognize',
			'translationEndpoint' => 'https://ntranslate.bhashini.ai/api/v1/translate',
			'apiKey' => 'MmvuwPD6BLHn-UxoTXsE9KLJtuIz5OEWZ4tFvQW8pMgGtQypRNVKC_xb5DN8tWPO'
		];

		public function __construct()
		{
			$this->appEmails = array(
			'enabled' => true,
            'admin' => 'admin@example.com'
			);

			$this->defaultCSS = array(
            'assets/css/bootstrap.min.css',
            'assets/css/icons.min.css',
            'assets/css/app.min.css',
            'assets/css/custom.min.css',
			'assets/css/toastr.css',
			'assets/libs/daterangepicker/daterangepicker.css',
			'assets/libs/datatables/datatables.min.css',
			'assets/css/style.css?v='.$this->cssVersion,
			);

			$this->defaultJS = array(
			'assets/js/layout.js',
			'assets/libs/bootstrap/js/bootstrap.bundle.min.js',
			'assets/libs/simplebar/simplebar.min.js',
			'assets/libs/node-waves/waves.min.js',
			'assets/libs/feather-icons/feather.min.js',
			'assets/js/pages/plugins/lord-icon-2.1.0.js',
			'assets/js/plugins.js',
			'assets/js/jquery-3.7.1.min.js',
			'assets/js/app.js',
            'assets/libs/bootbox/bootbox.all.min.js',
			'assets/js/toastr.min.js',
            'assets/libs/moment/min/moment.min.js',
            'assets/libs/daterangepicker/daterangepicker.js',
			'assets/libs/datatables/datatables.min.js',
			'assets/js/script.js?v='.$this->jsVersion,
			);

			$this->defaultMETA = array(
			);
		}
	}
