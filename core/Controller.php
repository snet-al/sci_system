<?php
require_once 'Manager.php';
class SCI_Controller
{
    public $owner = null;
    public $extenders = [];
    public $db = null;

    public function addExtender($extenders, $controller)
    {
        foreach ($extenders as $nameOfFile) {
            require_once(APPPATH . 'controllers/' . $controller . "/" . $nameOfFile . ".php");
            if (class_exists($nameOfFile)) {
                $this->extenders[] = new $nameOfFile;
            }
        }
    }

    public function callMethod($methodName)
    {
        if ($methodName == '') {
            return $this->index($this->owner->paramVector);
        }
        if (method_exists($this, $methodName)) {
            return call_user_func_array([$this, $methodName], $this->owner->paramVector);
        }

        $found = false;
        $indexOfExtender = -1;
        if (count($this->extenders) > 0) {
            foreach ($this->extenders as $i => $extender) {
                if (method_exists($extender, $methodName)) {
                    $indexOfExtender = $i;
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            return $this->errorMethod($methodName);
        }

        return call_user_func_array([$this->extenders[$indexOfExtender], $methodName], $this->owner->paramVector);
    }

    public function manager($mgr)
    {
        $methodsOfManager = get_class_methods($mgr);
        $manager = new SCI_Manager();
        foreach ($mgr as $propery => $value) {
            $manager->{$propery} = $value;
        }
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


    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        $rc = new \ReflectionClass(get_class($this));
        $constructorParameters = $rc->getConstructor()->getParameters();
        foreach ($constructorParameters as $constructorParameter) {
            $className = $constructorParameter->getClass()->getName();
            if ($name === $constructorParameter->name) {
                $newObject = new $className;
                if ($newObject instanceof SCI_Manager) {
                    $this->{$name} = $this->manager($newObject);
                } else {
                    $this->{$name} = $newObject;
                }
            }
        }
        return $this->{$name};
    }


    public function errorMethod($methodName = '')
    {
        echo '<br>error in method name : ' . $methodName;
    }

    public function index()
    {
        echo '<br>this is index method which should be overridden';
    }
}