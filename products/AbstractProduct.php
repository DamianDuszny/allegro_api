<?php
require_once("AbstractProduct.php");
abstract class Product
{
    protected $category_id;
    abstract function getCategoryId() :array;
}

?>