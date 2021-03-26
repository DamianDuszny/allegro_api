<?php
session_start();
require_once("./main/allegro_auth.php");
$auth = new AllegroAuth();
$result = $auth->setUserToken($_SESSION["device_code"]);
//$result = $auth->refreshUserToken($_COOKIE["user_refresh_token"]);
$auth->saveTokens("file");
?>