<?php 
require_once BASEPATH.'security/Escape.php';
class SCI_URI
{
	public $owner;
	public $urlRequest = '';
	public $url = [
	    0 => '',
        1 => '',
        2 => '',
    ];
	
    public function __construct($owner)
    {
		$this->owner = $owner;
	}
	
    public function fetchUrl($request)
    {
        if (!isset($request) || $request === "") {
            return false;
        }

        $urlParamsPart = explode("?", $request);

        if (!isset($urlParamsPart) || (isset($urlParamsPart) && (count($urlParamsPart) === 0 || count($urlParamsPart) == 1))) {
            return false;
        }

        if (count($urlParamsPart) !== 2) {
            $this->owner->errors[] = "url not formated";
            return false;
        }

        $this->urlRequest = $urlParamsPart[1];
        $route = explode("/", $this->urlRequest);

        if (!isset($route) || (isset($route) && ($routeLength = count($route)) === 0)) {
            $this->owner->errors[] = "no controller";
            return false;
        }
        if ($routeLength === 1) {
            $this->url[0] = $route[0];
            $this->url[1] = "";
            $this->url[2] = "";
        } else if ($routeLength === 2) {
            $this->url[0] = $route[0];
            $this->url[1] = $route[1];
            $this->url[2] = "";
        } elseif ($routeLength > 2){
            for ($i = 0; $i < $routeLength; $i++) {
                $this->url[$i] = $route[$i];
                if ($i > 2) {
                    $this->owner->paramVector[] = $route[$i];
                }
            }
        } else {
            $this->owner->errors[] = "url not formated";
        }

	}
	
    public function rebuildRequest()
    {
	    foreach ($_GET as $key => $value) {
	        $_GET[$key] = SCI_Escape::escape($value, $this->owner);
	    }
	    foreach ($_POST as $key => $value) {
	        $_POST[$key] = SCI_Escape::escape($value, $this->owner);
	    }
	}
}