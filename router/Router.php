<?php 
class SCI_Router
{
	public $owner;
	public $uri;
	
    public function __construct($owner)
    {
		$this->owner = $owner;
		$this->uri = $this->owner->uri;	
	}
	
    public function fetchController()
    {
		if (isset($this->uri->url[1]) && $this->uri->url[1] != '') {
			$this->owner->controllerName = $this->uri->url[1];
			return true;
		}

        if (! isset($this->owner->config->config['index_controller'])) {
            $this->owner->errors[] = 'no controller found';
            return false;
        }

        $this->owner->controllerName = $this->owner->config->config['index_controller'];
    }
    
    public function fetchMethod()
    {
		if (isset($this->uri->url[2]) && $this->uri->url[2] != '') {
			$this->owner->methodName = $this->uri->url[2];
			return true;
		}
		if (!isset($this->owner->config->config['index_method'])) {
            $this->owner->errors[] = 'no index_method config found';
            $this->owner->methodName = 'error_method';
            return false;
        }

        $this->owner->methodName = $this->owner->config->config['index_method'];
    }
}
?>