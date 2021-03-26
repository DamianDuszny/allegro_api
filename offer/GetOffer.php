<?php
declare(strict_types=1);
class GetOffer
{
    private $curl;
    private $token;
    public function __construct(CurlCommon $curl, string $userToken)
    {
        $this->curl = $curl;
        $this->token = $userToken;
    }

    public function getAllOffers() :string
    {
        $url = "/sale/offers";
        $result = $this->SendRequest($url);
        return $this->curl->get_result();
    }
    public function getOfferById(string $id) :string
    {
        $url = "/sale/offers/$id";
        $result = $this->SendRequest($url);
        return $this->curl->get_result();
    }
    public function getMissingRequiredOfferData($id) :array
    {
        $offer = json_decode($this->getOfferById($id), true);
        $missing = [];
        if(isset($offer["validation"]))
        {
            if(isset($offer["validation"]["errors"]))
                foreach($offer["validation"]["errors"] as $value)
                {
                    $missing[] = $value["path"];
                }
        }
        return $missing;
    }

    public function SendRequest($url, $content = 2) :string
    {
        $this->curl->set_content_type($content);
        $this->curl->set_header($this->token);
        $this->curl->set_url($url);
        $this->curl->execute_curl();
        return $this->curl->get_result();
    }
}

?>