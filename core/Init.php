<?php
class SCI
{
    public $config = null;
    public $errors = [];
    public $uri = null;
    public $router = null;
    public $db = null;
    public $request = [];

    public $controllerName;
    public $methodName;
    public $paramVector = array();

    public $controller = null;
    public $view = null;

    public function __construct()
    {
        $this->loadConfig();
        $this->loadUri();
        $this->loadRouter();
        $this->connectDb();

        $this->security();

        //if isset $auth_mode we are in the authorization process , we are in a different envirement or app menaged by security
        // and we dont enter in mvc
        if (! isset($GLOBALS['auth_mode']) || ! $GLOBALS['auth_mode'] || $GLOBALS['auth_mode'] != 1) {
            $this->startMVC();
        }

    }

    public function loadConfig()
    {
        require_once(BASEPATH . 'config/Config.php');
        $this->config = new SCI_Config($this);
    }

    public function loadUri()
    {
        require_once(BASEPATH . 'router/URL.php');
        $this->uri = new SCI_URI($this);
        $this->uri->fetchUrl($_SERVER['REQUEST_URI']);
    }

    public function loadRouter()
    {
        require_once(BASEPATH.'router/Router.php');
        $this->router = new SCI_Router($this);
    }

    public function connectDb()
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
        $this->request = $this->uri->rebuildRequest();

        if (isset($this->config) && isset($this->config->config['security']) && $this->config->config['security'] == 'oauth2') {
            require_once(BASEPATH.'security/OAuth2.php');
            $this->security = new Oauth2($this, $this->config->config['security']);
            $this->security->check();
        } else {
            //default flow for ecommerce
            //TODO: add the auth check of ecommerce with Oauth2 and the posibility to login with google and facebook
        }
    }

    public function startMVC()
    {
        require_once('Controller.php');
        require_once('Model.php');

        $this->router->fetchController();
        $this->router->fetchMethod();

        if (count($this->errors) > 0) {
            echo json_encode($this->errors);
            return false;
        }

        if (!file_exists(APPPATH . 'controllers/' . $this->controllerName . '/' . $this->controllerName . '.php')) {
            $this->errors[] = "Controller file not found";
            echo json_encode($this->errors);
            return false;
        }

        require(APPPATH . 'controllers/' . $this->controllerName . '/' . $this->controllerName . '.php');

        if (!class_exists($this->controllerName)) {
            $this->errors[] = "Controller Class not found";
            echo json_encode($this->errors);
            return false;
        }

        $this->controller = new $this->controllerName();
        $this->controller->owner = $this;
        $this->controller->db = $this->db;
        $this->controller->request = $this->request;
        $this->controller->callMethod($this->methodName);
    }
}