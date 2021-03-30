<?php
class curlCommon
{
    private $header;
    private $url = NULL;
    private $post;
    private $post_fields;
    private $auth;
    private $auth_id;
    private $auth_pass;
    private $status;
    private $result;
    private $ch_errno;
    private $client_secret;
    private $client_id;
    private $curl_info;
    private $content_type; //for header request
    private $accept_type;
    private $data;
    private $delete;
    private $put;
    public function __construct()
    {
        if(!file_exists("./config/app.json"))
            die("Cannot find file ./config/app.json");
        $data = json_decode(file_get_contents("./config/app.json"));
        $this->data = $data;
        $this->client_secret = $data->client_secret;
        $this->client_id = $data->client_id;
        if(!$this->client_secret || !$this->client_id)
            die("no data application (client secret and client id) isnide app.json");
        $this->url = $data->url;
        $this->content_type = "application/vnd.allegro.public.v1+json";
        $this->accept_type = "application/vnd.allegro.public.v1+json";
    }
    public function set_header(string $token)
    {
        $this->header =	["Authorization: Bearer ".$token,
            "Content-Type: ".$this->content_type,
            "Accept: ".$this->accept_type,
            "Accept-Language: pl-PL"
        ];
    }
    /*
    When $custonUrl remains false, first part of url will be taken from app.json
    Then you have to send only end point as $url, else you have to send full url
    */
    public function set_url(string $url, $customUrl = false)
    {
        ($customUrl) ? $this->url = $url : $this->url = $this->data->allegro_sandbox . $url;
    }
    public function set_post(string $post_fields)
    {
        $this->post = true;
        $this->post_fields = $post_fields;
    }
    public function set_auth()
    {
        $this->auth = true;
        $this->auth_id = $this->client_id;
        $this->auth_pass = $this->client_secret;
    }
    public function set_delete_header()
    {
        $this->delete = true;
    }
    public function set_put_header()
    {
        $this->put = true;
    }
    public function execute_curl()
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$this->url);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($this->auth)
        {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERNAME, $this->auth_id);
            curl_setopt($ch, CURLOPT_PASSWORD, $this->auth_pass);
            $this->auth = false;
        }
        if($this->delete)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            $this->delete = false;
        }
        if($this->put)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            $this->put = false;
        }
        if($this->header)
        {
            curl_setopt($ch,CURLOPT_HTTPHEADER,$this->header);
            $this->header = false;
        }
        if($this->post)
        {
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $this->post_fields);
            $this->post = false;
        }
        $this->result = curl_exec($ch);
        $this->ch_errno = curl_errno($ch);
        $this->status = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        $this->curl_info = curl_getinfo($ch);
        curl_close($ch);
    }
    public function get_result()
    {
        return $this->result;
    }
    public function get_status()
    {
        return $this->status;
    }
    public function get_info()
    {
        return $this->curl_info;
    }
    public function get_url()
    {
        return $this->url;
    }
    public function get_errno()
    {
        return $this->ch_errno;
    }
    public function get_error_message_if_exist() :?array
    {
        $result = json_decode($this->result);
        $message = null;
        $error = ["message"=>null, "code"=>null];
        if(!is_null($result))
        {
            if (property_exists($result, "errors"))
                $message = $result->errors[0]->message;  //request errors

            if (property_exists($result, "error"))
                $message = $result->error_description; //authentication errors

            if(!is_null($message))
                $error = ["message"=>$message, "code"=>$this->status];

            else if($this->status!=200)
                $error = ["error"=>NULL, "code"=>$this->status];
        }
        else
        {
            $error = ["curl errno"=>$this->ch_errno, "code"=>$this->status];
        }
        return $error;
    }
    public function set_content_type($type)
    {
        switch($type)
        {
            case 1:
                $this->content_type="application/vnd.allegro.beta.v2+json";
                $this->accept_type="application/vnd.allegro.beta.v2+json";
                break;
            case 2:
                $this->content_type="application/vnd.allegro.public.v1+json";
                $this->accept_type="application/vnd.allegro.public.v1+json";
                break;
            case 3:
                $this->content_type="Content-Type: application/x-www-form-urlencoded";
                $this->accept_type="Content-Type: application/x-www-form-urlencoded";
                break;
            case 4:
                $this->content_type="application/vnd.allegro.public.v2+json";
                $this->accept_type="application/vnd.allegro.beta.v2+json";
                break;
        }
    }
    public function get_header() :array
    {
        return $this->header;
    }
 /*   public function set_header($user_access_token) :void
    {

    }*/
}

?>