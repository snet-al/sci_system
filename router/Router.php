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
	
    public function fetch_controller()
    {
		if (isset($this->uri->url[1])) {
			$this->owner->controller_name = $this->uri->url[1];
		}else{
			$this->owner->errors[] = 'no controller found';
			$this->owner->controller_name = $this->uri->url_request;
		}
    }
    
    public function fetch_method()
    {
		if (isset($this->uri->url[2])) {
			$this->owner->method_name = $this->uri->url[2];
		} else {
			$this->owner->errors[] = 'no method found';
			$this->owner->method_name = 'error_method';
		}
    }
    
    public function fetch_params()
    {
		if (count($this->uri->url) == 4) {
			//define params of {p=1&t=4&gsd=nmsdf}
		} else {
			$this->owner->errors[]="Fetch params not posible url not formated";
		}
	}
}
?>