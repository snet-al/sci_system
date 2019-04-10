<?php
class SCI_Model
{
    public function __construct()
    {
        foreach ($this as $k => $v) {
            if ($k !== 'request' && $k !== 'controller'){
                unset($this->{$k});
            }
        }
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
                    $this->{$name} = $this->controller->manager($newObject);
                } else if ($newObject instanceof SCI_Model) {
                    $this->{$name} = $this->controller->model($newObject);
                } else {
                    $this->{$name} = $newObject;
                }
            }
        }
        return $this->{$name};
    }

    public static $regexRules = [
        'required' => '',
        'numeric'  => ''
    ];

    public function validate($validations = null)
    {
        if (($validations === null)) {
            return true;
        }

        foreach ($validations as $fieldName => $rules) {
            foreach ($rules as $rule){
                if (isset(self::$regexRules[$rule])) {
                    $this->controller->request[$fieldName];
                } else {
                    if (!isset($this->controller->request[$fieldName])) {
                        $this->controller->error([
                            'validation' => $fieldName
                        ]);
                    }
                    if (!preg_match($rule, $this->controller->request[$fieldName])) {
                        $this->controller->error([
                            'validation' => $fieldName
                        ]);
                    }
                }
            }

        }

        return true;
    }
}