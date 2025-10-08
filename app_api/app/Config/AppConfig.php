<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppConfig extends BaseConfig
{
    public $appName = "PDS";
    public $apiKey = "f3a7c1d92e4b5f89a0d3e6f7c2b8a4d1e9f5c6a8b7d0e3f2c1d9e4b5f8a7c0";
    public $appEmails = [];

    public $sms = [
        "enabled" => false,
        "url" => "",
        "username" => "",
        "password" => "",
        "sender" => "",
    ];

    public function __construct()
    {
        $this->appEmails = [
            "enabled" => false,
        ];
    }
}
