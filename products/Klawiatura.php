<?php


class Klawiatura extends Product
{
    public $stan;
    public $przeznaczenie;
    public $kodProducenta;
    public $producent;
    protected $category_id;

    public function __construct()
    {
        $this->category_id = ["id"=>"77803"];
    }
    public function Producent($prod)
    {
        $this->producent =
            [
                "id"=>"227265",
                "values"=>[$prod]
            ];
    }
    public function KodProducenta($kod)
    {
        $this->kodProducenta =
            [
                "id"=>"224017",
                "values"=>[$kod],
            ];
    }
    public function Stan($st)
    {
        $stan =   array (
            "parameter_id"=> "11323",
            'Nowy'=>'11323_1',
            'Używany' => '11323_2',
            'Po zwrocie' =>  '11323_238066',
            'Powystawowy' => '11323_238058',
            'Uszkodzony' =>'11323_238062'
        );
        $this->stan =
            [
                "id"=>$stan["parameter_id"],
                "valuesIds"=>[$stan[$st]]
            ];
    }
    public function Przeznaczenie($przez)
    {
        $przeznaczenie = array (
            "parameter_id"=> "18595",
            'Acer' =>'18595_1' ,
            'Apple' => '18595_652289',
            'Asus' =>'18595_2' ,
            'Dell' =>'18595_3' ,
            'Fujitsu-Siemens' =>'18595_4' ,
            'HP, Compaq' =>'18595_5' ,
            'IBM, Lenovo' =>'18595_6' ,
            'Microsoft' => '18595_652297',
            'Samsung' => '18595_652293',
            'Sony' =>'18595_7' ,
            'Toshiba' =>'18595_8' ,
            'inny producent' =>'18595_9' ,
        );
        if(!isset($przeznaczenie[$przez]))
            throw new Exception("Bad data, $przez not found");
        $this->przeznaczenie =
            [
                "id"=>$przeznaczenie["parameter_id"],
                "valuesIds"=>[$przeznaczenie[$przez]]
            ];
    }
    public function getCategoryId() :array
    {
        return $this->category_id;
    }

}