<?php
declare(strict_types = 1);

class AllegroAuth
{
    private $TOKEN_URI = 'https://allegro.pl/auth/oauth/token';
    private $AUTHORIZATION_URI = 'https://allegro.pl/auth/oauth/authorize';
    private $client_id = "de08b3767f7347bb8294cda67987a71a";
    private $client_secret = "t41O4TB6j6JxvtgSOaBbaDKVt7SYKQWrJwXDz3syhAXrsfQhSc18QEPYqRK8dQRV";
    private $client_credentials_url = "https://allegro.pl.allegrosandbox.pl/auth/oauth/token?grant_type=client_credentials";
    private $verification_uri;
    private $ch;
    private $app_token;
    private $user_token;
    private $refresh_token;
    private $device_code;
    private $result;
    public function __construct()
    {
        $this->ch = new curlCommon();
        /*
         * token scopes can be added if necessary
         * */
    }
    public function set_app_token() :bool
    {
        $this->ch->set_url($this->client_credentials_url, true);
        $this->ch->set_auth();
        $this->ch->execute_curl();
        if($this->ch->get_status()!=200)
            return false;
        $this->app_token = json_decode($this->ch->get_result())->access_token;
            return true;
    }
    public function get_app_token(): ?string
    {
        return $this->app_token;
    }
    public function getError(): ?array
    {
        return $this->ch->get_error_message_if_exist();
    }

    /**
     * @param string $redirect url to which the allegro will send request after authorization --not tested
     * @return bool
     * at this link, the user can grant permissions
     */
    public function setVerificationUri(string $redirect) :bool
    {
        $data = "client_id=".$this->client_id;
        $this->ch->set_url("https://allegro.pl.allegrosandbox.pl/auth/oauth/device", true);
        $this->ch->set_content_type(3);
        $this->ch->set_post($data);
        $this->ch->set_auth();
        $this->ch->execute_curl();
        $result = json_decode($this->ch->get_result());
        if(property_exists($result, "device_code"))
        {
            $this->user_code = $result->user_code;
            $this->device_code = $result->device_code;
            $this->verification_uri =
                "https://allegro.pl.allegrosandbox.pl/skojarz-aplikacje?code=".$result->user_code."&redirect_uri=".$redirect;
            return true;
        }
        else
            return false;
    }
    /**
    * Returns verification uri, user should click it and give permissions to application
     *
     */
    public function getVerificationUri(): ?string
    {
        return $this->verification_uri;
    }
    public function getDeviceCode(): ?string
    {
        return $this->device_code;
    }

    /**
     * after the user has granted permissions this method should be used
     * @param string $device_code
     * @param string $method
     * @return int
     */
    public function setUserToken(string $device_code,string $method = "") :bool
    {
        $user_token_uri = "https://allegro.pl.allegrosandbox.pl/auth/oauth/token?grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Adevice_code&device_code=".$device_code;
        $ch = $this->ch;
        $ch->set_url($user_token_uri, true);
        $ch->set_auth();
        $ch->execute_curl();
        if($this->ch->get_status()!=200)
            return false;
        $result = json_decode($ch->get_result());
        $this->result = $result;
            return true;
    }
    public function getUserToken(): ?string
    {
        return $this->user_token;
    }
    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }
    public function refreshUserToken($refresh_token): bool
    {
        $url = "https://allegro.pl.allegrosandbox.pl/auth/oauth/token?grant_type=refresh_token&refresh_token=".$refresh_token;
        $ch = $this->ch;
        $ch->set_url($url, true);
        $ch->set_auth();
        $ch->execute_curl();
        if($this->ch->get_status()!=200)
            return false;
        $result = json_decode($ch->get_result());
        $this->result = $result;
            return true;

    }
    public function saveTokens($method) :bool
    {
        if(isset($this->result->access_token))
        {
            $user_token = $this->result->access_token;
            $refresh_token = $this->result->refresh_token;
            switch($method)
            {
                case "session":
                {
                    $_SESSION["user_access_token"] = $user_token;
                    $_SESSION["user_refresh_token"] = $refresh_token;
                }
                case "cookie":
                {
                    /**
                     * WARNING: this data should only be used with HTTPS
                     * */
                    setcookie("user_refresh_token",  $refresh_token, time() + 7889231, "/", "", true);
                    setcookie("user_access_token",  $user_token, time() + $this->result->expires_in, "/", "", true);
                }
                case "file":
                {
                    $file = fopen(__DIR__."\\tokens.json", "w");
                    $content = [
                        "user_refresh_token"=>["token"=>$refresh_token, "expire"=>time() + 7889231],
                        "user_access_token"=>["token"=>$user_token, "expire"=>time() + $this->result->expires_in]
                    ];
                    fwrite($file, json_encode($content));
                    fclose($file);
                    break;
                }
            }
            return true;
        }
        return false;
    }
    public function getLastStatusCode(): ?int
    {
        return $this->ch->get_status();
    }
    public function getLastError()
    {
        return $this->ch->get_error_message_if_exist();
    }

}

?>