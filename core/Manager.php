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
	            echo 'start';
	            //start DB transactions
	            break;
	        case 'commit':
	            echo 'commit';
	            //commit DB transaction
	            break;
	        case 'rollback':
	            echo 'rollback';
	            //rollback DB transaction
	            break;
	        default:
	            return false;
	    }
	}
	
}
