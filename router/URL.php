<?php
require_once BASEPATH.'security/Escape.php';
class SCI_URI
{
    public $owner;
    public $url_request = '';
    public $url = [];

    public function __construct($owner)
    {
        $this->owner = $owner;
    }

    public function fetchUrl($request)
    {
        if (isset($request) && $request !== "") {
            $v = explode("?", $request);

            if (isset($v) && count($v) == 0) {
                $this->url_request = '';
                $this->url[0] = "";
                $this->url[1] = "";
                $this->url[2] = "";

            } else if(isset($v) && count($v) == 1 ) {
                $this->url_request = '';
                $this->url[0] = "";
                $this->url[1] = "";
                $this->url[2] = "";

            } else if(isset($v) && count($v) == 2) {
                $tr = $this->url_request = $v[1];
                $trv = explode("/", $tr);
                if (isset($trv) && count($trv) == 0) {
                    $this->owner->errors[]="no controller";
                } else if (isset($trv) && count($trv) == 1) {
                    $this->url[0] = $trv[0];
                    $this->url[1] = "";
                    $this->url[2] = "";
                } else if (isset($trv) && count($trv) == 2) {
                    $this->url[0]  =$trv[0];
                    $this->url[1] = $trv[1];
                    $this->url[2] = "";
                } elseif (isset($trv) && count($trv) > 2){
                    for ($i = 0; $i < count($trv); $i++) {
                        $this->url[$i] = $trv[$i];
                        if ($i > 2) {
                            $this->owner->paramVector[] = $trv[$i];
                        }
                    }
                } else {
                    $this->owner->errors[] = "url not formated";
                }
            } else {
                $this->owner->errors[] = "url not formated";
            }
        }
    }

    public function rebuildRequest()
    {
        $request = new ArrayObject();
        $request->setFlags(ArrayObject::STD_PROP_LIST|ArrayObject::ARRAY_AS_PROPS);
        foreach ($_GET as $key => $value) {
            $request[$key]  = $value;
        }
        foreach ($_POST as $key => $value) {
            $request[$key] = $value;
        }
        return $request;
    }
}
