<?php
require_once 'Manager.php';
class SCI_Controller
{
	public $owner = null;
	public $extenders = [];
	public $db = null;
	
	public function addExtender($array_names,$controller)
	{
		foreach ($array_names as $name_of_file) {
			require_once(APPPATH . 'controllers/' . $controller . "/" . $name_of_file . ".php");
			if (class_exists($name_of_file)) {
				$this->extenders[] = new $name_of_file;
			}
		}
	}
	public function call_method($method_name)
	{
		if ($method_name !== '') {
			if (method_exists($this, $method_name)) {
				$method = &$this->$method_name;
				call_user_func_array(array($this, $method_name), $this->owner->paramVector);
			} else {
				$found = false;
				$i_of_obj = -1;
				if (count($this->extenders) > 0) {
					foreach ($this->extenders as $i => $extender) {
						if (method_exists($extender, $method_name)) {
							$i_of_obj = $i;
							$found = true;
							break;
					 	}
				 	}
				}
				
				if (!$found) {
					$this->error_method($method_name);
				} else {
					call_user_func_array(array($this->extenders[$i_of_obj], $method_name), $this->owner->paramVector);
				}
			}
		} else {
			$this->index($this->owner->paramVector);
		}
	}
	
	public function error_method($method_name)
	{
		echo '<br>error in method name';
	}

	public function index()
	{
		echo '<br>this is index method which cun be overriden';
	}

	/**
	 * DEPRECATED function
	 *
	 * @param [type] $file
	 * @param [type] $data
	 * @return void
	 */
	public function view($file, $data = null)
	{
		if (file_exists(APPPATH . 'views/' . $this->owner->controller_name . '/' . $file)) {
			$html = file_get_contents(APPPATH . 'views/' . $this->owner->controller_name . '/' . $file);
		} else {
			$html = "";
		}
		if (!isset($data)) {
			$data = [];
		}
		echo $this->owner->tpl->load($html,$data);
	}
	
	public function view_public($path_inside_public_html = 'index.html', $data=[])
	{
	    if (file_exists(APPPATH . 'views/' . $path_inside_public_html)) {
	        require(APPPATH . 'views/' . $path_inside_public_html);
	    } else {
	        $html = "";
	    }
	}

	public function view_php($file, $data = null)
	{
		if (file_exists(APPPATH . 'views/' . $this->owner->controller_name . '/' . $file)) {
			require(APPPATH . 'views/' . $this->owner->controller_name . '/' . $file);
		} else {
			$html = "";
		}
	}

	public function view_file($file, $data = null)
	{
		if (file_exists(APPPATH . 'views/' . $this->owner->controller_name . '/' . $file)) {
			$html = file_get_contents(APPPATH . 'views/' . $this->owner->controller_name . '/' . $file);
		} else {
			$html = "";
		}
		if (!isset($data)) {
			$data = [];
		}
		echo $html;
	}

	public function library($folder, $name_of_file)
	{
		require_once(APPPATH . 'libraries/' . $folder . "/" . $name_of_file . ".php");
	}
	
	public function manager($manager)
	{    
	    require_once APPPATH . 'managers/' . $manager.'.php';
	    $mgr = new $manager();
	    $methodsOfManager = get_class_methods($mgr);
	    $manager = new SCI_Manager();
	    $manager->mgr = $mgr;
	    $manager->db = $this->db;
	    $manager->owner = $this->owner;
	    
	    foreach ($methodsOfManager as $methodName) {
	        $manager->{$methodName} = function() use ($methodName){
	            $this->transaction('start');
	            $result = call_user_func_array([$this->mgr, $methodName], func_get_args());
	            if ($result) {
	                $this->transaction('commit');
	            } else {
	                $this->transaction('rollback');
	            }
	            return $result;
	        };
	    }
	    return $manager;
	}
}