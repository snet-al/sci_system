<?php
trait DynamicDefinition 
{
	public function __call($name, $args) 
	{
        if (is_callable($this->$name)) {
            return call_user_func_array($this->$name, $args);
        } else {
            throw new \RuntimeException("Method {$name} does not exist");
        }
    }
    
	public function __set($name, $value) 
	{
        $this->$name = is_callable($value) ? $value->bindTo($this, $this) : $value;
    }
}

class SCI_Manager
{
    use DynamicDefinition;
    
    public $mgr;
	public $owner = null;
	public $db = null;
	
	public function transaction($process = 'start')
	{
	    switch ($process){
	        case 'start':
	            break;
	        case 'commit':
	            break;
	        case 'rollback':
	            break;
	        default:
	            return false;
	    }
	}
	
}
