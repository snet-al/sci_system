<?php 
class SCI_Config
{
	public $config = [];
	public $_config_paths = [];
	
	public function __construct($owner)
	{
		$this->owner = $owner;
		$this->get_config();
	}
	
	public function get_config()
	{
		global $database;
		$this->config['index_controller'] = 'home';
		$this->config['index_method'] = 'index';
		
		if (isset($database) && gettype($database) == 'array') {
			$this->config['database_host'] = $database['host'];
			$this->config['database_user'] = $database['user'];
			$this->config['database_password'] = $database['password'];
			$this->config['database_name'] = $database['database'];
		} else {
			$this->config['database_host'] = 'localhost';
			$this->config['database_user'] = 'root';
			$this->config['database_password'] = '';
			$this->config['database_name'] = 'mysql';
		}
		
		if (isset($GLOBALS['security']) && gettype($GLOBALS['security']) == 'string') {
		    $this->config['security'] = $GLOBALS['security'];
		}
	}
}
?>
