<?php

class OrderManagement
{
    private $curl;
    private $token;

    /**
     * Order constructor.
     * @param CurlCommon $curl
     * @param string $user_access_token
     */
    public function __construct(CurlCommon $curl, string $user_access_token)
    {
        $this->curl = $curl;
        $this->token = $user_access_token;
    }

    /**
     * @param string $lastMerge
     * @param int $limit
     * @return array|null [AllegroOrder]
     */
    public function GetOrders(string $lastMerge = "", int $limit = 100) :?array
    {
        $url = "/order/events?limit=$limit";
        if($lastMerge)
            $url .="&from=$lastMerge";

        $result = json_decode($this->SendRequest($url));
        //echo json_encode($result);
        if($this->curl->get_status()!=200)
            return NULL;

        $orders = [];
        $result = $result->events;
        foreach ($result as $event)
        {
            $handler = new AllegroOrder();
            $handler->id = $event->id;
            $handler->type = $event->type;
            $handler->occurredAt = $event->occurredAt;
            $orderDetails = new OrderDetails();
            $handler->order = $orderDetails;
            //var_dump($event);
            $event = $event->order;
            $orderDetails->seller = (Object)[
                "id"=>$event->seller->id
            ];
            $handler->order->checkoutForm = (object)[
                "id"=>$event->checkoutForm->id,
                "revision"=>$event->checkoutForm->revision
            ];
            $buyer = new Buyer();
            $buyer->id = $event->buyer->id;
            $buyer->email = $event->buyer->email;
            $buyer->guest = $event->buyer->guest;
            $buyer->login = $event->buyer->login;
            $orderDetails->buyer = $buyer;

            $items = [];
            foreach ($event->lineItems as $lineItem) {
                $lineItems = new LineItems();
                $lineItems->fill($lineItem);
                $items[] = $lineItems;
            }
            $handler->order->lineItems = $items;
            $orders[] = $handler;

        }
        return $orders;
    }
    public function GetOrderDetails(string $id) :?CheckoutForm
    {
        $url = "/order/checkout-forms/$id";
        $result = json_decode($this->SendRequest($url));
        if($this->curl->get_status()!=200)
            return NULL;
        $form = new CheckoutForm();
        $dataFields = get_object_vars($result);
        $missing = [];
        //Autofill object with data from Allegro
        foreach($dataFields as $field=>$value)
        {
            if(property_exists($form, $field))
            {
                //if data is an object and have less than 3 properties, i dont create any class for that instance
                if(!is_object($value) || count(get_object_vars($value))<3)
                $form->$field = $result->$field;
                else
                {
                    if(class_exists(ucwords($field)))
                    {
                        $class = new $field();
                        if(!method_exists($field, "fill"))
                        {
                            $missing[] = $field;
                            continue;
                        }
                        $class->fill($value);
                        $form->$field = $class;
                    }
                    else
                        $missing[] = $field;
                }
            }
        }
        return $form;
    }
    public function DeleteOrder() :bool
    {

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

class AllegroOrder
{
    /**
     * @var string $id
     */
    public $id;
    /**
     * @var OrderDetails $order
     */
    public $order;
    /**
     * @var string $type
     *
     *BOUGHT: purchase without checkout form filled in
     * FILLED_IN: checkout form filled in but payment is not completed yet so data could still change
     * READY_FOR_PROCESSING: payment completed. Purchase is ready for processing
     * BUYER_CANCELLED: purchase was cancelled by buyer
     * FULFILLMENT_STATUS_CHANGED: fulfillment status changed.
     */
    public $type;
    /**
     * @var string $occurredAt example: 2021-03-06T09:52:02.805Z
     */
    public $occurredAt;
}

class OrderDetails
{
    /**
     * @var Object $checkoutForm ["id"=>string, "revision"=>string]
     */
    public $checkoutForm;
    /**
     * @var Object $seller ["id"=>string]
     */
    public $seller;
    /**
     * @var Buyer $buyer
     */
    public $buyer;
    /**
     * @var array $lineItems [LineItems]
     */
    public $lineItems;

}

class Buyer
{
    /**
     * @var string $id
     */
    public $id;
    /**
     * @var string $email
     */
    public $email;
    /**
     * @var bool $guest;
     */
    public $guest;
    /**
     * @var string $login
     */
    public $login;
    /**
     * @var string $firstName
     */
    public $firstName;
    /**
     * @var string $lastName
     */
    public $lastName;
    /**
     * @var string $companyName
     */
    public $companyName;
    /**
     * @var string $personalIdentity
     */
    public $personalIdentity;
    /**
     * @var string $phoneNumber
     */
    public $phoneNumber;
    /**
     * @var object $address ["street"=>string, "city"=>string, "postCode"=>string, "countryCode"=>string]
     */
    public $address;
    public function fill(Object $data)
    {
        $dataFields = get_object_vars($data);
        foreach($dataFields as $field=>$value)
        {
            if(property_exists($this, $field))
            $this->$field = $data->$field;
        }
    }
}
class LineItems
{
    /**
     * @var Object ["id"=>string, "name"=>string] possible external property, i dont know for what it is
     */
    public $offer;
    /**
     * @var Object ["amount"=>string, "currency"=>string]
     */
    public $price;
    /**
     * @var string $id
     */
    public $id;
    /**
     * @var int $quantity
     */
    public $quantity;
    /**
     * @var Object ["amount"=>string, "currency"=>string]
     */
    public $originalPrice;
    /**
     * @var string $boughtAt example: 2021-03-06T09:52:02.805Z
     */
    public $boughtAt;
    public function fill(Object $data) :LineItems
    {
        $this->offer = (object)[
            "id" => $data->offer->id,
            "name" => $data->offer->name,
            "external" => $data->offer->external,
        ];
        $this->price = (object)[
            "amount" => $data->price->amount,
            "currency" => $data->price->currency,
        ];
        $this->id = $data->id;
        $this->quantity = $data->quantity;
        $this->originalPrice = (object)[
            "amount" => $data->originalPrice->amount,
            "currency" => $data->originalPrice->currency
        ];
        $this->boughtAt =$data->boughtAt;
        return $this;
    }


}


class CheckoutForm
{
    /**
     * @var string $id
     */
    public $id;
    /**
     * @var string $messageToSeller
     */
    public $messageToSeller;
    /**
     * @var Buyer $buyer
     */
    public $buyer;
    /**
     * @var Payment $payment
     */
    public $payment;
    /**
     * @var string $status
     */
    public $status;
    /**
     * @var Object $fulfillment ["status"=>string, "shipmentSummary"=>object["lineItemsSent"=>string]]
     */
    public $fulfillment;
    /**
     * @var Delivery $delivery
     */
    public $delivery;
    /**
     * @var Invoice $invoice
     */
    public $invoice;
    /**
     * @var LineItems $lineItems
     */
    public $lineItems;
    /**
     * @var Array $surcharges
     */
    public $surcharges;
    /**
     * @var Array $discounts
     */
    public $discounts;
    /**
     * @var Object $summary ["totalToPay"=>Object["amount"=>string, "currency"=>PLN]]
     */
    public $summary;
    /**
     * @var string $updatedAt example: 2021-03-06T09:52:16.848Z
     */
    public $updatedAt;
    /**
     * @var string $revision
     */
    public $revision;
}

class Payment
{
    /**
     * @var string $id
     */
    public $id;
    /**
     * @var string $type
     */
    public $type;
    /**
     * @var string $provider
     */
    public $provider;
    /**
     * @var string $finishedAt
     */
    public $finishedAt;
    /**
     * @var Object $paidAmount ["amount"=>string, "currency"=>string]
     */
    public $paidAmount;
    public function fill(Object $data)
    {
        $dataFields = get_object_vars($data);
        foreach($dataFields as $field=>$value)
        {
            if(property_exists($this, $field))
                $this->$field = $data->$field;
        }
    }
}


class Delivery
{
    /**
     * @var Address $address
     */
    public $address;
    /**
     * @var Object $method ["id"=>string, "name"=>string] ID of existing delivery method in allegro db
     */
    public $method;
    /**
     * @var  $pickupPoint
     */
    public $pickupPoint;
    /**
     * @var Object $cost {amount, currency}
     */
    public $cost;
    /**
     * @var boolean $smart
     */
    public $smart;
    /**
     * @var object $time {guaranteed - boolean}
     */
    public $time;
    /**
     * @var int $calculatedNumberOfPackages
     */
    public $calculatedNumberOfPackages;
    public function fill(Object $data)
    {
        $dataFields = get_object_vars($data);
        foreach($dataFields as $field=>$value)
        {
            if(property_exists($this, $field))
                $this->$field = $data->$field;
        }
    }
}

/**
 * Class Address all properties are string type
 */
class Address
{
    public $firstName;
    public $lastName;
    public $street;
    public $city;
    public $zipCode;
    public $countryCode;
    public $companyName;
    public $phoneNumber;
    public $modifiedAt;
    public $naturalPerson;
    public function fill(Object $data)
    {
        $dataFields = get_object_vars($data);
        foreach($dataFields as $field=>$value)
        {
            if(property_exists($this, $field))
                $this->$field = $data->$field;
        }
    }
}
class Invoice
{
    /**
     * @var bool $required
     */
    public $required;
    /**
     * @var Address $address;
     */
    public $address;
    public function fill(Object $data)
    {
        $dataFields = get_object_vars($data);
        foreach($dataFields as $field=>$value)
        {
            if(property_exists($this, $field))
                $this->$field = $data->$field;
        }
    }
}
?>