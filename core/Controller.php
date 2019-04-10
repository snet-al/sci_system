<?php
require_once 'Manager.php';
class SCI_Controller
{
    public $owner = null;
    public $db = null;

    public function __construct()
    {
        foreach ($this as $k => $v) {
            if($k !== 'response' && $k !== 'request' && $k !== 'db' && $k !== 'owner') {
                unset($this->{$k});
            }
        }
    }

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
            call_user_func_array([$this, $methodName], $this->owner->paramVector);
        } else {
            $this->errorMethod($methodName);
        }

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
        if (file_exists(PATH . $file)) {
            require(PATH . $file);
        }
    }

    public function library($folder, $nameOfFile)
    {
        require_once(APPPATH . 'libraries/' . $folder . "/" . $nameOfFile . ".php");
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
        $manager->contoller = $this;

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


    public function model($modelObject)
    {
        $modelObject->controller = $this;
        $modelObject->db = $this->db;
        $modelObject->owner = $this->owner;

        return $modelObject;
    }


    public function __get($name)
    {
        if (isset($this->{$name})) {
            return $this->{$name};
        }

        $rc = new ReflectionClass(get_class($this));
        $constructorParameters = $rc->getConstructor()->getParameters();
        foreach ($constructorParameters as $constructorParameter) {
            $className = ($constructorParameter->getClass()->getName());
            if ($name === $constructorParameter->name) {
                $newObject = new $className;
                if ($newObject instanceof SCI_Manager) {
                    $this->{$name} = $this->manager($newObject);
                } else if ($newObject instanceof SCI_Model) {
                    $this->{$name} = $this->model($newObject);
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
