<?php

class AddImage
{
    private $curl;
    private $token;
    public $imagesLocation;
    public function __construct(CurlCommon $curl, string $token)
    {
        $this->curl = $curl;
        $this->token = $token;
    }
    public function AddImageWithUrl($url) :bool
    {
        $data = ["url"=>$url];
        $this->SendRequest(json_encode($data));
        if($this->curl->get_status()!=201)
            return false;
        else
            $this->imagesLocation[] = json_decode($this->curl->get_result())->location;
            return true;
    }
    public function AddImageWithoutUrl($src):bool
    {
        $cfile = new CURLFile($src);

        return false;
    }
    public function SendRequest($data)
    {
        $url = "https://upload.allegro.pl.allegrosandbox.pl/sale/images";
        $this->curl->set_post($data);
        $this->curl->set_url($url, true);
        $this->curl->set_header($this->token);
        $this->curl->execute_curl();
    }
}

?>