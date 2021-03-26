<?php

/**
 * Class Offer is used for create draft offer
 */
class Offer
{
    /**
     * @var string $id
     */
    public $id;
    /**
     * @var string $category
     * @restrictions product->category_id
     */
    public $category;
    /**
     * @var string $name
     */
    public $name;
    /**
     * @var object $stock
     * @schema ["amount"=>int, "unit"=>enum {UNIT, PAIR, SET}]
     */
    public $stock;
    /**
     * @var Description $description
     */
    public $description;
    /**
    * @var Array $images Array of objects, this property should be set via AddImages method
     */
    public $images;
    /**
     * @var object $payments
     * @schema ["invoice"=>enum]
     * @enum  VAT, VAT_MARGIN, WITHOUT_VAT, NO_INVOICE
     */
    public $payments;
    /**
     * @var sellingMode $sellingMode
     */
    public $sellingMode;
    /**
     * @var Object $delivery
     * @schema ["shippingRates"=>["id"=>string], "handlingTime"=>@enum $handlingTime];
     * @enum $handlingTime = {PT0S for immediately, PT24H, P2D, P3D, P4D, P5D, P7D, P10D, P14D, P21D, P30D, P60D}
     */
    public $delivery;
    /**
     * @var Array $parameters
     * array of objects from product
     * this property should be set by SetParameters method
     */
    public $parameters;
    /**
     * @var object $location
     */
    public $location;
    public function SetParameters(Product $product) :void
    {
        $product_params = get_object_vars($product);
        $this->parameters = [];
        foreach ($product_params as $value)
        {

            $this->parameters[] = (object)$value;
        }
    }

    /**
     * @param array $url must point to an image in the allegro database, you can upload image to their database with AddImage class
     * @return bool
     *
     */
    public function AddImages(Array $url) :bool
    {
        foreach($url as $image)
        {
            $this->images[] = (object)["url"=>$image];
        }
        return false;
    }
}

class SellingMode
{
    /**
     * @var string $format
     * @enum "BUY_NOW" "AUCTION" "ADVERTISEMENT"
     */
    public $format;
    /**
    * @var object $price
     * @schema ["amount"=>string, "currency"=>string]
     */
    public $price;
}

class Description
{
    /**
     * @var object $description
     *  this property should be set by SetDescription method
     */
    public $description;
    public function SetDescription($desc)
    {
        $sections = [];
        $items = [];
        $items[] = (object)["type"=>"TEXT", "content"=>"<p>$desc</p>"];
        $sections[] = (object)["items"=>$items];
        $this->description = ["sections"=>$sections];
    }
}
class Location
{
    /**
     * @var string $city
     */
    public $city;
    /**
     * @var string $countryCode
     */
    public $countryCode;
    /**
     * @var string $postCode
     */
    public $postCode;
    /**
     * @var enum $province
     * @enum {DOLNOSLASKIE, KUJAWSKO_POMORSKIE, LUBELSKIE, LUBUSKIE, LODZKIE, MALOPOLSKIE, MAZOWIECKIE, OPOLSKIE, PODKARPACKIE, PODLASKIE, POMORSKIE, SLASKIE, SWIETOKRZYSKIE, WARMINSKO_MAZURSKIE, WIELKOPOLSKIE, ZACHODNIOPOMORSKIE.}
     */
    public $province;

}
?>