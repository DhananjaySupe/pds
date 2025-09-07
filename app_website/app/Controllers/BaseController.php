<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\SessionModel;

use App\Libraries\JwtLib;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['app','cookie','text'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
	protected $viewdata = array('path' => '', 'page' => '', 'id' => '', 'class' => '',  'title' => '',  'canonicalurl' => '', 'meta' => array(), 'css' => array(), 'js' => array(), 'flashmessage' => array());
	protected $_user = array();
	protected $_parent = array();
	protected $_output = array('success' => false);
	protected $_status = 200;
	protected $params = array();
    protected $_permission = array();
	protected $session;
	protected $AppConfig;
	protected $paramSources = array('_GET', '_POST');


    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
		$this->session = \Config\Services::session();
		$flashmessage = $this->session->getFlashdata('flashmessage');
		if ($flashmessage) {
			if (is_array($flashmessage)) {
				$flashmessage = count($flashmessage) == 1 ? array($flashmessage, 'success') : $flashmessage;
				} else {
				$flashmessage = array($flashmessage, 'success');
			}
			$this->viewdata['flashmessage'] = $flashmessage;
		}
		if ($this->session->has('user_id')) {
			$id = (int) $this->session->get('user_id');
			if ($id > 0) {
				$userModel = new UserModel();
				$userRoleModel = new UserRoleModel();
				$user = $userModel->findByID($id);
				if ($user) {
					$this->_user = array(
					'id' => $user['user_id'],
					'role' => $user['role'],
					'name' => $user['full_name'],
					'email' => $user['email'],
					'phone' => $user['phone'],
					'status' => $user['status']
					);

					$permission = $userRoleModel->findByID($user['role']);
					$this->_permission = $permission;

					$this->viewdata['_user'] = $this->_user;
				}
			}
		}

		/** */
		$this->AppConfig = new \Config\AppConfig();
		$this->viewdata['path'] = $request->getUri()->getPath();
		$this->viewdata['page'] = $request->getUri()->getSegment(1);
    }

	/**
		* Check if the user has permission to access the module
	*/
	public function CheckPermission($module = '')
    {
		//if($this->CheckPermission('settings_users')){
        $permission = false;
        if(isset($this->_permission['fullaccess']) && (bool)$this->_permission['fullaccess']){
            $permission = true;
        } else {
            if(!empty($module)&&(isset($this->_permission[$module]) && (bool)$this->_permission[$module])){
                $permission = true;
            }
        }
        return $permission;
    }

	public function isUserLoggedIn()
	{
		$language = 'en'; // default language
		if (count($this->_user) > 0 && isset($this->_user['id']) && $this->_user['id'] > 0) {
			if($this->_user['status'] == 1){
				$session_token = $this->session->get('session_token');
				$sessionModel = new SessionModel();
				$session = $sessionModel->findByToken($session_token);
				if($session){
					if($session['status'] == 1){
						$jwt = new JwtLib();
						$validated = $jwt->validateToken($session_token);
						if($validated){
							// Get language from user session
							$language = $this->session->get('user_language') ?? 'en';
							$this->request->setLocale($language);
							$this->viewdata['current_language'] = $language;
							return true;
						}
					}
				}
			}
		}

		// Get language from guest session
		$language = $this->session->get('guest_language') ?? 'en';
		$this->request->setLocale($language);
		$this->viewdata['current_language'] = $language;

		return false;
	}

	public function setSuccess($message = "")
	{
		$this->_status = 200;
		$this->_output['success'] = true;
		$this->_output['message'] = $message;
		if (empty($this->_output['message'])) {
			unset($this->_output['message']);
		}
	}

	public function setError($message = "", $status = 200)
	{
		$this->_status = $status;
		if ($this->_status != 200) {
			$this->_output = $message;
			} else {
			$this->_output['success'] = false;
			$this->_output['message'] = $message;
		}
		if (empty($this->_output['message'])) {
			unset($this->_output['message']);
		}
	}

	public function setOutput($value = "", $key = "")
	{
		if (!empty($key)) {
			$this->_output[$key] = $value;
			} else {
			$this->_output['data'] = $value;
		}
	}

	public function response($value = null)
	{
		//return $this->respond->setStatusCode($this->_status)->setJSON(!is_null($value) ? $value : $this->_output);

		$value = $value ?? $this->_output ?? [];
		return $this->response->setStatusCode($this->_status)->setJSON($value);
	}

	public function setData($name, $value)
	{
		$this->viewdata[$name] = $value;
	}

	public function bodyID($value)
	{
		$this->viewdata['id'] = $value;
	}

	public function bodyClass($value)
	{
		if(is_array($this->viewdata['class'])){
			$this->viewdata['class'][] = $value;
			} else {
			$this->viewdata['class'] = $value;
		}
	}

	public function pageName($value)
	{
		$this->viewdata['page'] = $value;
	}

	public function pageTitle($value)
	{
		$this->viewdata['title'] = $value;
	}

	public function canonicalUrl($value)
	{
		$this->viewdata['canonicalurl'] = $value;
	}

	public function pageMeta($name, $value, $nametitle = 'name', $valuetitle = 'content')
	{
		$this->viewdata['meta'][] = '<meta ' . $nametitle . '="' . $name . '" ' . $valuetitle . '="' . $value . '">';
	}

	public function pageCss($value)
	{
		if(is_array($value)){
			foreach ($value as $k => $v) {
				$this->viewdata['css'][] = $v;
			}
			} else {
			$this->viewdata['css'][] = $value;
		}
	}

	public function pageJs($value)
	{
		if(is_array($value)){
			foreach ($value as $k => $v) {
				$this->viewdata['js'][] = $v;
			}
			} else {
			$this->viewdata['js'][] = $value;
		}
	}

	public function isPost()
	{
		return ($_SERVER['REQUEST_METHOD'] == 'POST') ? true : false;
	}

	public function isGet()
	{
		return ($_SERVER['REQUEST_METHOD'] == 'GET') ? true : false;
	}

	public function isDelete()
	{
		return ($_SERVER['REQUEST_METHOD'] == 'DELETE') ? true : false;
	}

	public function isOptions()
	{
		return ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') ? true : false;
	}

	public function getPost($key = null, $default = null)
	{
		if (null === $key) {
			return $_POST;
		}
		return (isset($_POST[$key])) ? $_POST[$key] : $default;
	}

	public function getCookie($key = null, $default = null)
	{
		if (null === $key) {
			return $_COOKIE;
		}
		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
	}

	public function getParam($key, $default = null)
	{
		$paramSources = $this->getParamSources();
		if (isset($this->params[$key])) {
			return $this->params[$key];
			} elseif (in_array('_GET', $paramSources) && (isset($_GET[$key]))) {

			return $_GET[$key];
			} elseif (in_array('_POST', $paramSources) && (isset($_POST[$key]))) {
			return $_POST[$key];
		}
		return $default;
	}

	public function getParams()
	{
		$return = $this->params;
		$paramSources = $this->getParamSources();
		if (in_array('_GET', $paramSources) && isset($_GET) && is_array($_GET)) {
			$return += $_GET;
		}
		if (in_array('_POST', $paramSources) && isset($_POST) && is_array($_POST)) {
			$return += $_POST;
		}
		return $return;
	}

	private function getParamSources()
	{
		return $this->paramSources;
	}
}
