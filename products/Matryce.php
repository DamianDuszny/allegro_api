<?php
require_once("AbstractProduct.php");
class Matryce extends Product
{
    /**
     *
     */
    public $stan;
    /**
     * @var string
     * @custom=true
     */
    /**
     * @var float
     * @custom=true
     */
    public $przekatna;
    /**
     * @var array
     * @custom=false
     */
    public $rozdzielczosc;
    /**
     * @var array
     * @custom=false
     */
    public $powlokaMatrycy;
    /**
     * @var array
     * @custom=true
     */
    public $kodProducenta;
    /**
     * @var array
     * @custom=true
     */
    public $producent;
    protected $category_id;
    /**
     * Matryce constructor.
     * @param float $przekatna
     * @param string $rozdzielczoscIndex
     * @param string $powlokaIndex
     * @param string $kodProducenta
     * @param string $producent
     * @param string $stanIndex
     * @throws Exception
     */
    public function __construct(float $przekatna, string $rozdzielczoscIndex, string $powlokaIndex, string $kodProducenta, string $producent,
    string $stanIndex)
    {
        $this->category_id = ["id"=>"310033"];

        $this->przekatna =
            [
                "id"=>"213354",
                "values"=>[number_format((float) $przekatna, 1, '.', '')]
            ]; ;
        /*
 * Allegro needs to use his own options
 * */
        $rozdzielczosc =
            [
            "parameter_id"=>"4474",
            "4474_492685"=>"1024 x 600",
            "4474_211453"=>"1280 x 800",
            "4474_60"=>"1366 x 768",
            "4474_90"=>"1440 x 900"
        ];

        if(!isset($rozdzielczosc["$rozdzielczoscIndex"]))
            throw new Exception("Nie poprawny index rozdzielczosci");
        $this->rozdzielczosc =
            [
                "id"=>$rozdzielczosc["parameter_id"],
                "valuesIds"=>[$rozdzielczoscIndex]
            ];

        /*
         * Allegro needs to use his own options
         * */
        $powlokaMatrycy =
            [
            "parameter_id"=>"200917",
            "200917_1685"=>"błyszcząca antyrefleksyjna",
            "200917_1689"=>"błyszcząca",
            "200917_1693"=>"matowa"
        ];
        if(!isset($rozdzielczosc["$rozdzielczoscIndex"]))
            throw new Exception("Nie poprawny index powloki");
        $this->powlokaMatrycy =
            [
            "id"=>$powlokaMatrycy["parameter_id"],
            "valuesIds"=>[$powlokaIndex]
        ];


        if(strlen($kodProducenta)>50 || strlen($kodProducenta)<2)
            throw new Exception("Niepoprawna dlugosc kodu producenta");
        $this->kodProducenta =
            [
            "id"=>"224017",
            "values"=>[$kodProducenta],
            ];


        if(strlen($producent)>25 || strlen($producent)<2)
            throw new Exception("Niepoprawna dlugosc nazwy producenta");
        $this->producent =
            [
                "id"=>"227265",
                "values"=>[$producent],
            ];
        /*
        * Allegro needs to use his own options
        */
        $stan =
            [
                "parameter_id"=>"11323",
                "11323_1"=>"Nowy",
                "11323_2"=>"Używany",
                "11323_238066"=>"Po zwrocie",
                "11323_238058"=>"Powystawowy",
                "11323_238062"=>"Uszkodzony",
            ];
        $this->stan =
            [
                "id"=>$stan["parameter_id"],
                "valuesIds"=>[$stanIndex]
            ];
    }
    public function getCategoryId(): array
    {
        return $this->category_id;
    }
    public function getLatestChanges()
    {

    }
}

?>