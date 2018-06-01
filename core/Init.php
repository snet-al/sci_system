<?php
class SCI 
{
	public $config = null;
	public $errors = [];
	public $uri = null;
	public $router = null;
	public $db = null;
	
	public $controller_name;
	public $method_name;
	public $paramVector = array();
	
	public $controller = null;
	public $view = null;
	
	public function __construct()
	{
		$this->load_config();
		$this->load_uri();
		$this->load_router();
		$this->connect_db();
		
		$this->security();
		
		//if isset $auth_mode we are in the authorization process , we are in a different envirement or app menaged by security
		// and we dont enter in mvc
		if (! isset($GLOBALS['auth_mode']) || ! $GLOBALS['auth_mode'] || $GLOBALS['auth_mode'] != 1) {
		    $this->load_tpl();
		    $this->start_mvc();
		}
		
	}
	
	public function load_config()
	{
		require_once(BASEPATH . 'config/Config.php');
		$this->config = new SCI_Config($this);
	}

	public function load_uri()
	{
		require_once(BASEPATH . 'router/URL.php');
		$this->uri = new SCI_URI($this);
		$this->uri->fetch_url($_SERVER['REQUEST_URI']);
	}

	public function load_router()
	{
		require_once(BASEPATH.'router/Router.php');
		$this->router = new SCI_Router($this);
	}

	public function load_tpl()
	{
	    if (file_exists(BASEPATH.'template/STemplate.php')) {
	        require_once (BASEPATH . 'template/STemplate.php');
	        $this->tpl = new SCI_Template($this, BASEPATH . 'template/Mustache');
	    } else {
	        $this->tpl = null;
	    }
	}

	public function connect_db()
	{
		require_once(BASEPATH . 'database/Database.php');
		$this->db = new Database($this->config->config['database_host'], 
								$this->config->config['database_user'], 
								$this->config->config['database_password'],
								$this->config->config['database_name']);
		if ($this->db == null) {
			echo "DB::error";
			exit();
		}
	}

	public function security()
	{
	    $this->uri->rebuildRequest();
	    
	    if (isset($this->config) && isset($this->config->config['security']) && $this->config->config['security'] == 'oauth2') {
	        require_once(BASEPATH.'security/OAuth2.php');
	        $this->security = new Oauth2($this, $this->config->config['security']);
	        $this->security->check();
	    } else {
	        //default flow for ecommerce
	        //TODO: add the auth check of ecommerce with Oauth2 and the posibility to login with google and facebook
	    }
	}

	public function start_mvc()
	{
		require_once('Controller.php');
		require_once('Model.php');
		
		$this->router->fetch_controller();
		$this->router->fetch_method();
		
		if (count($this->errors) > 0) {
			echo json_encode($this->errors);
		} else {
			if (file_exists(APPPATH . 'controllers/' . $this->controller_name . '/' . $this->controller_name . '.php')) {
				require(APPPATH . 'controllers/' . $this->controller_name . '/' . $this->controller_name . '.php');
			}
			
			if (class_exists($this->controller_name)) {
				$this->controller = new $this->controller_name();
				$this->controller->owner = $this;
				$this->controller->db = $this->db;
				$this->controller->call_method($this->method_name);
			}else{
				if (!isset($this->config->config['index_controller']) || !isset($this->config->config['index_method'])) {
					echo 'bad index controller configuration';
					return false;
				}

				$this->controller_name = $this->config->config['index_controller'];
				$this->method_name=$this->config->config['index_method'];

				if (!file_exists(APPPATH . 'controllers/' . $this->controller_name . '/' . $this->controller_name . '.php')) {
					echo 'controller doesnt exist';
					return false;
				}
				require (APPPATH . 'controllers/' . $this->controller_name . '/' . $this->controller_name . '.php');
				if (class_exists($this->controller_name)) {
					$this->controller = new $this->controller_name();
					$this->controller->owner = $this;
					$this->controller->db = $this->db;
					
					$this->controller->call_method($this->method_name);
				} else {
					echo 'class with the same name as controller doesnt exist';
					return false;
				}
			}
		}
	}

}

/**
 * HELPER FUNCTIONS
 * 
 */
function dd ($data = null) 
{
    echo "\n";
    print_r($data);
    echo "\n";
    die();
}
