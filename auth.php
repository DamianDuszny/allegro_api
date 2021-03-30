<?php
session_start();
require_once("./main/allegro_auth.php");
$auth = new AllegroAuth();
/*
 * you have to wait 5 seconds before sending the query, otherwise the allegro server will return an error
 * */
do {
    sleep(5);
    $result = $auth->setUserToken($_SESSION["device_code"]);
}while($auth->getLastStatusCode()==205);
//$result = $auth->refreshUserToken($_COOKIE["user_refresh_token"]);
$auth->saveTokens("file");
?>