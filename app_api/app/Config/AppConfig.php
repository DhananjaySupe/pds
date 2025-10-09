<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppConfig extends BaseConfig
{
    public $appName = "PDS";
    public $appEmails = [];
    /** */
    public $apiKey = "f3a7c1d92e4b5f89a0d3e6f7c2b8a4d1e9f5c6a8b7d0e3f2c1d9e4b5f8a7c0";
    public $AppCurrentVersion = '1.0.0';
    public $appForceUpdate = '1';
    public $appDownloadUrl = 'https://play.google.com/store/apps/details?id=com.kashit.lostandfound';
    /* JWT */
    public $jwt_secret = 'VNyLbLP7aGg9YKZXlshZqkRFahRLgf1L';
    public $jwt_expiry = 36000; // in sec - 10 hours

    /** Two Factor Auth   */
    public $twoFactorAuth = array(
        'enabled' => false,
        'send' => array(
            'email' => true,
            'sms' => true,
            'whatsapp' => true,
        ),
    );

    /** SMS */
    public $sms = [
        "enabled" => false,
        "url" => "",
        "username" => "",
        "password" => "",
        "sender" => "",
    ];
    /** */
    public function __construct()
    {
        $this->appEmails = [
            "enabled" => false,
        ];
    }
}
