<?php 
Class OAuth2 
{
    private $owner;
    private $protocol;

    public function __construct($owner, $protocol)
    {
        $this->owner = $owner;
        $this->protocol = $protocol;
    }
    
    public function check()
    {
        session_start();
        if (isset($GLOBALS['auth_mode']) && $GLOBALS['auth_mode'] == 1) {
            $this->auth();
            return false;
        }

        if (!isset($_SESSION['is_loged']) || $_SESSION['is_loged'] != 1) {
            http_response_code(300);
            header('location:oauth2.php?redirect_url='.$_SERVER['REQUEST_URI']);
            exit();
        }

        return true;
    }
    
    public function auth()
    {
        if (isset($_GET['request']) && $_GET['request'] == 'authorize') {
            header("Content-Type: application/json;charset=UTF-8");
            header("Cache-Control: no-store");
            header("Pragma: no-cache");

            if (isset($_POST['client_username']) && $_POST['client_username'] !== '') {
                $client_username = trim($_POST['client_username']);
                $result = $this->owner->db->table('oauth_users')->select("*", ['client_username' => $client_username]);
                if ($result && count($result) == 1){
                    if(isset($_POST['grant_type']) && $_POST['grant_type'] == 'authorization_code'){
                        $auth_code = bin2hex(random_bytes(32));
                        $update_auth_code = $this->owner->db->table('oauth_users')->edit(['auth_code' => $auth_code], ['client_id' => $result[0]["client_id"]]);
                        if (!$update_auth_code) {
                            echo '{error:"server_error",message:"update auth code"}';
                            exit();
                        }
                        echo '{"client_id":"'.$result[0]["client_id"].'", "authorization_code":"'.$auth_code.'" }';
                        exit();
                    }
                    
                    echo "{client_id:'".$result[0]["client_id"]."' }";
                } else {
                    echo '{error:"unauthorized_client"}';
                }
            }
        } else if (isset($_GET['request']) && $_GET['request'] == 'token') {
            if (isset($_POST['client_id']) && $_POST['client_id'] !== '') {
                $client_id = trim($_POST['client_id']);
                $result = $this->owner->db->table('oauth_users')->select("*", ['client_id' => $client_id]);
                if ($result && count($result) == 1) {
                    if (isset($_POST['code']) && $_POST['code'] !== '') {
                        if ($result[0]['auth_code'] === $_POST['code'] && $result[0]['password'] === $this->chipher($_POST['password'])) {
                            $access_token = bin2hex(random_bytes(32));
                            $update_auth_code = $this->owner->db->table('oauth_users')->edit(['access_token' => $access_token], ['client_id' => $result[0]["client_id"]]);
                            if (! $update_auth_code) {
                                echo '{"error":"server_error","message":"update auth code"}';
                                exit();
                            }
                        } else {
                            echo '{"error":"unauthorized_client"}';
                            exit();
                        }
                        echo '{"success":1,"client_id":"'.$result[0]["client_id"].'", "access_token":"'.$access_token.'" }';
                    } else {
                        if ($result[0]['password'] === $this->chipher($_POST['password'])) {
                            //this is succesfully login
                            $access_token = bin2hex(random_bytes(32));
                            $update_auth_code = $this->owner->db->table('oauth_users')->edit(['access_token'=>$access_token], ['client_id'=>$result[0]["client_id"]]);
                            if (! $update_auth_code) {
                                echo '{"error":"server_error","message":"update auth code"}';
                                exit();
                            }
                        }else{
                            echo '{"error":"unauthorized_client"}';
                            exit();
                        }
                        echo '{"success":1,"client_id":"'.$result[0]["client_id"].'", "access_token":"'.$access_token.'" }';
                    }
                }else{
                    echo '{"error":"unauthorized_client"}';
                }
            } else {
                echo '{"error: "no_client_id"}';
                return false;
            }
            
            $_SESSION['is_loged'] = 1;
            $_SESSION['access_token'] = $access_token;
        } else if (isset($_GET['request']) && $_GET['request'] == 'logout') {
            $_SESSION['is_loged'] = null;
            $_SESSION['access_token'] = null;
            unset($_SESSION['is_loged']);
            unset($_SESSION['access_token']);
            if (isset($_GET['redirect_url']) && $_GET['redirect_url'] != "") {
                header("location:" . $_GET['redirect_url']);
            }
        } else {
            require_once 'auth_resources/login.html';
        }
    }
    
    public function chipher($password)
    {
        return $password;
    }
}