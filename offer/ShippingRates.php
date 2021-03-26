<?php

class AllegroShippingRates
{
    private $curl;
    private $token;
    public function __construct(CurlCommon $curl, string $userToken)
    {
        $this->curl = $curl;
        $this->token = $userToken;
    }

    /**
     * @return string JSON [{"id":string, "name": string}]
     */
    public function getShippingRates() :string
    {
        $url = "/sale/shipping-rates";
        return $this->SendRequest($url);
    }

    /**
     * @param string $id
     * @return ShippingRates|null
     */
    public function getShippingRatesById(string $id) :?ShippingRates
    {
        $url = "/sale/shipping-rates/$id";
        $result =  json_decode($this->SendRequest($url));
        if($this->curl->get_status()!=200)
            return NULL;

        $ShippingRates = new ShippingRates();
        $ShippingRates->id = $result->id;
        $ShippingRates->name = $result->name;
        $rates = [];
        foreach ($result->rates as $value)
        {
            $holder = new Rates();
            $holder->deliveryMethod = $value -> deliveryMethod;
            $holder->maxQuantityPerPackage = $value -> maxQuantityPerPackage;
            $holder->firstItemRate = $value -> firstItemRate;
            $holder->nextItemRate = $value -> nextItemRate;
            $holder->shippingTime = $value -> shippingTime;
            $rates[] = $holder;
        }
        $ShippingRates->rates = $rates;
        return $ShippingRates;
    }
    public function location()
    {
        $url = "/points-of-service?seller.id=93973824";
        return $this->SendRequest($url);
    }
    public function SendRequest($url, $content = 2) :string
    {
        $this->curl->set_content_type(2);
        $this->curl->set_header($this->token);
        $this->curl->set_url($url);
        $this->curl->execute_curl();
        return $this->curl->get_result();
    }

}

class ShippingRates
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $name;
    /**
     * @var array
     * @schema [Rates]
     */
    public $rates;
}
class Rates
{
    /**
     * @var object
     * @schema ["id"=>string]
     */
    public $deliveryMethod;
    /**
     * @var int
     */
    public $maxQuantityPerPackage;
    /**
     * @var object
     * @schema ["amount"=>float, "currency"=>string]
     */
    public $firstItemRate;
    /**
     * @var object
     * @schema ["amount"=>float, "currency"=>string]
     */
    public $nextItemRate;
    /**
     * @var string
     * @restrictions ISO 8601 duration format, e.g. P3D for 3 days
     */
    public $shippingTime;
}

?>
