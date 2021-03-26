<?php

require_once("./products/AbstractProduct.php");
require_once("GetOffer.php");
class OfferManagement
{
    public $id;
    public $name;
    public $offer;
    public $last_result;
    public $missingOfferFields;
    public $ids;

    private $ch;
    private $token;
    /**
     * PostOffer constructor.
     * @param string $userToken
     * @param CurlCommon $curl
     */
    public function __construct(string $userToken, CurlCommon $curl)
    {
        $this->ch = $curl;
        $this->token = $userToken;
    }
    /**
     * @param Offer $offer
     * @return bool
     */
    public function Prepare(Offer $offer) :bool
    {
//        $fields = get_object_vars($offer);
//        foreach ($fields as $fieldName => $field)
//        {
//            if(is_null($field))
//            {
//                $this->missingOfferFields[] = $fieldName;
//            }
//        }
//        if($this->missingOfferFields)
//            return false;

        $this->offer = $offer;
        return true;
    }
    /**
     * @param array $params ["name", ["values"]]
     */
    public function add_custom_parameters(array $params)
    {

    }
    public function PublishOffer($id, $commandId) :bool
    {
        $url = "/sale/offer-publication-commands/$commandId";
        $data = [
            "offerCriteria"=>[["offers"=>[["id"=>"$id"]], "type"=>"CONTAINS_OFFERS"]],
            "publication"=>["action"=>"ACTIVATE"]];
        $this->ch->set_put_header();
        $this->ch->set_post(json_encode($data));
        $this->SendRequest($url);
        if($this->ch->get_status()!=201)
            return false;
        $this->last_result = $this->OfferPublicationResult($commandId);
            return true;
    }
    public function UnpublishOffer(string $id, string $commandId) :bool
    {
        $url = "/sale/offer-publication-commands/$commandId";
        $data = [
            "offerCriteria"=>[["offers"=>[["id"=>"$id"]], "type"=>"CONTAINS_OFFERS"]],
            "publication"=>["action"=>"END"]];
        $this->ch->set_put_header();
        $this->ch->set_post(json_encode($data));
        $this->SendRequest($url);
        if($this->ch->get_status()!=201)
            return false;
        else
            return true;
    }
    //Only inactive offers can be deleted
    //acvite offers should be unpublished by method UnpublishOffer
    public function DeleteOffer(string $id) :bool
    {
        $url = "/sale/offers/$id";
        $this->ch->set_delete_header();
        $this->sendRequest($url);
        if($this->ch->get_status()!=204)
            return false;
        else
            return true;
    }
    public function DeleteAllInavctiveOffers(string $offers) :bool
    {
        $offers = json_decode($offers);
        //TODO GET /sale/offers?publication.status=INACTIVE
        foreach($offers->offers as $offer)
        {
            $this->DeleteOffer($offer->id);
        }
        return true;
    }
    public function CreateDraftOffer() :bool
    {
        $data = json_encode($this->offer);

        if(!isset($data))
            throw new Exception("You must prepare offer");
        $this->ch->set_post($data);
        $this->ch->set_url("/sale/offers");
        $this->ch->set_header($this->token);
        $this->ch->execute_curl();
        if($this->ch->get_status()!=200 && $this->ch->get_status()!=201)
            return false;
        else
        $this->ids[] = json_decode($this->ch->get_result())->id;
            return true;
    }
    public function OfferPublicationResult($uuid) :?OfferPublicationResult
    {
        $url = "/sale/offer-publication-commands/$uuid/tasks";
        $result = json_decode($this->SendRequest($url))->tasks[0];
        $result->uuid = $uuid;
        $OfferPublicationResult = new OfferPublicationResult();
        foreach ($result as $fieldName => $val)
        {
            if(property_exists("OfferPublicationResult", $fieldName))
                $OfferPublicationResult->$fieldName =  $val;
        }
        return $OfferPublicationResult;
    }
    public function changeProductAmount(string $id,int $amount) :bool
    {
        $offer = new GetOffer($this->ch, $this->token);
        $offer = json_decode($offer->getOfferById($id));
        $url = "/sale/offers/$id";
        if(property_exists($offer, "errors"))
        {
            return false;
        }
        $offer->stock->available = $amount;
        $this->ch->set_put_header();
        $this->ch->set_post(json_encode($offer));
        $this->SendRequest($url);
        if($this->ch->get_status()!=200)
            return false;
        return true;
    }
    public function SendRequest($url, $content = 2) :string
    {
        $this->ch->set_content_type(2);
        $this->ch->set_header($this->token);
        $this->ch->set_url($url);
        $this->ch->execute_curl();
        return $this->ch->get_result();
    }



}

class OfferPublicationResult
{
    /**
     * @var object $offer ["id"=>"string"]
     */
    public $offer;
    /**
     * @var string $message
     */
    public $message;
    /**
     * @var string $status
     */
    public $status;
    /**
     * @var string $scheduledAt date, example - 2021-03-26T08:37:32.423Z
     */
    public $scheduledAt;
    /**
     * @var string $finishedAt date, example - 2021-03-26T08:37:32.423Z
     */
    public $finishedAt;
    /**
     * @var string $field
     */
    public $field;
    /**
     * @var array $errors
     */
    public $errors;
    /**
     * @var string $uuid
     */
    public $uuid;
}

?>