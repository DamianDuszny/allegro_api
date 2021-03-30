<?php
session_start();
require_once("./curlCommon.php");

require_once("./categories/get_categories.php");
require_once("./main/allegro_auth.php");

require_once ("./products/Matryce.php");
require_once ("./products/Klawiatura.php");


require_once("./offer/OfferManagement.php");
require_once("./offer/GetOffer.php");
require_once("./offer/offer.php");
require_once("./offer/Add_Image.php");

require_once("./offer/ShippingRates.php");
require_once("./order/OrderClass.php");
$auth = new AllegroAuth();
if(file_exists("./main/tokens.json")) {
    $tokens = json_decode(file_get_contents("./main/tokens.json"));
    $user = $tokens->user_access_token;
    $refresh = $tokens->user_refresh_token;
    if ($user->expire < time() && $auth->refreshUserToken($refresh->token))
    {
        $auth->saveTokens("file");
    }
    $_SESSION["user_access_token"] = $tokens->user_access_token->token;
}
else
{
    $auth = $auth->setVerificationUri("https://google.com");
    if($auth)
    {
        print($auth->getVerificationUri());
        $_SESSION["device_code"] = $auth->getDeviceCode();
    }
    else
    {
        print("something went wrong");
    }
    die();
}
header('Content-Type: application/json', true, 200); //just for testing
$curl = new CurlCommon();

/*
 * GENERATE UUID FUNCTION
 * */
function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
$uuid = guidv4();
/*
 * authorization
 * */

/*

*/

/*
 * end auth section
 * */

//----get parameters for class
//var_dump(json_decode($categories->GetSubCategories("77804")));
//var_dump(json_decode($categories->GetCategoryParams("310033"), true));
//77801 komputery
//147905 baterie
//77803 klawiatury
//310033 matryce

/**images*/

//$images = new AddImage($curl, $_SESSION["user_access_token"]);
//$result = $images->AddImageWithUrl("https://images.pexels.com/photos/255379/pexels-photo-255379.jpeg?fit=crop&h=1000&mark=https%3A%2F%2Fassets.imgix.net%2F~text%3Fbg%3D80000000%26txt%3DFree%2BStock%2BPhotos%26txtalign%3Dcenter%26txtclr%3Dfff%26txtfont%3DAvenir-Heavy%26txtpad%3D20%26txtsize%3D120%26w%3D1300&markalign=center%2Cmiddle&txt=pexels.com&txtalign=center&txtclr=eeffffff&txtfont=Avenir-Heavy&txtshad=10&txtsize=60&w=1500");
//if($result)
//{
//    var_dump($images->imagesLocation);
//}
//else
//{
//    var_dump($curl->get_result());
//}


try
{

    $product = new Matryce(17.8, "4474_492685", "200917_1685", "00711", "nieznany", "11323_2");
    $product = new Klawiatura();
    //"11323_1", "18595_1", "000000", "Damian"
    $product->Producent("Damian Set Test");
    $product->KodProducenta("000001");
    $product->Przeznaczenie("Acer");
    $product->Stan("Uszkodzony");

}
catch (Exception $e)
{
    die($e->getMessage());
}

/*
 * offer managemenet
 * */
if(!isset($_SESSION["user_access_token"]))
    die("Wymagana autoryzacja");
//shipping-rates
$rates = new AllegroShippingRates($curl, $_SESSION["user_access_token"]);
$shipping_rates =  json_decode($rates->getShippingRates())->shippingRates;
//----- location doesnt works
//$location = $rates->location();
//echo $account_rates;
// ------create offer draft

$OfferManagement = new OfferManagement( $_SESSION["user_access_token"], $curl);
$offer = new Offer();
$offer->category = $product->getCategoryId();
$offer->name = "testowy draft 15";
$offer->stock = (object)["available"=>5, "unit"=>"UNIT"];

$desc = new Description();
$desc->SetDescription("opis");
$offer->description = $desc->description;

$offer->payments = (object)["invoice"=>"VAT"];

$sellingMode = new SellingMode();
$sellingMode->format = "BUY_NOW";
$sellingMode->price = (object)["amount"=>"123.45", "currency"=>"PLN"];
$offer->sellingMode = $sellingMode;

$offer->delivery = (object)["shippingRates"=>["id"=>$shipping_rates[1]->id], "handlingTime"=>"PT24H"];

$offer->SetParameters($product);

$location = new Location();
$location->city="Ostrów Mazowiecka";
$location->countryCode ="PL";
$location->postCode = "00-000";
$location->province = "MAZOWIECKIE";
$offer->location = $location;

$offer->AddImages(["https://a.allegroimg.allegrosandbox.pl/original/113230/015aace14c6cb2fe40234ea22d5c"]);

//1b8b2586-ca42-440b-a48b-3ce7373b090y
//$OfferManagement->Prepare($offer);
//try {
//    $result = $OfferManagement->CreateDraftOffer();
//    if($result)
//    {
//        $uuid = guidv4();
//        $OfferManagement->PublishOffer($OfferManagement->ids[0], "$uuid");
//        echo $OfferManagement->last_result;
//    }
//    else
//    {
//       echo $curl->get_result();
//    }
//}
//catch (Exception $e)
//{
//    echo $e->getMessage();
//}
$OfferManagement->changeProductAmount("7680128289", 10);

//$delete = $OfferManagement->DeleteOffer("7680099227");
//if(!$delete)
//{
//    echo json_encode($curl->get_error_message_if_exist());
//}


$uuid = guidv4();
//$OfferManagement->UnpublishOffer("7680128295", $uuid);
//18ac9cfa-58b6-47a9-8a2b-7c3e6cc3380d

//echo $result;

//user id: 93973824
//endpoint: https://allegro.pl.allegrosandbox.pl/login/user || /me


//----get offer by id
$get_offer = new GetOffer($curl, $_SESSION["user_access_token"]);
echo $get_offer->getAllOffers();
//echo json_encode($get_offer->getMissingRequiredOfferData("7680127987"));
$OfferManagement->DeleteAllInavctiveOffers($get_offer->getAllOffers());
//$offerResult = $get_offer->getOfferById("7680087725");
//$missing = $get_offer->getMissingRequiredOfferData("7680087594");
//echo json_encode($missing);


//  Required params for category @id
 $categories = new Categories($auth, $curl);
$usedCategories = json_decode(file_get_contents("products/UsedCategories.json"));
 //print_r($categories->GetLatestChanges($usedCategories->used));
  $required = $categories->searchForRequiredFields(json_decode($categories->GetCategoryParams("77803"), true));
  //echo json_encode($required);
  $necessary = $categories->getNecessaryData($required);
//echo json_encode($necessary);


//------orders--------
$order = new OrderManagement($curl, $_SESSION["user_access_token"]);
$orders = $order->GetOrders();
//echo $orders;
//$orderDetails = $order->GetOrderDetails("581d8830-7e61-11eb-81fc-f7a8b8fc0703");
//echo json_encode($orderDetails);
//echo json_encode($orders);

?>