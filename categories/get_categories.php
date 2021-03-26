<?php


class Categories
{
    private $allegro;
    private $app_token;
    private $ch;
    public function __construct(AllegroAuth $allegro,CurlCommon $curl)
    {
        $this->allegro = $allegro;
        $this->allegro->set_app_token();
        $this->app_token = $this->allegro->get_app_token();
        $this->ch = $curl;
    }
    public function GetSubCategories(string $id = "")
    {
        ($id!="") ? $query = "?parent.id=".$id : $query = "";
        $url = "/sale/categories".$query;
        return $this->execute($url);
    }
    public function GetById(string $id) :string
    {
        $url = "/sale/categories/".$id;
        return $this->execute($url);
    }
    public function GetCategoryParams(string $id) :string
    {
        $url = "/sale/categories/$id/parameters";
        return $this->execute($url);
    }
    public function GetCategoryByName(string $name) :string
    {
        $url = "/sale/matching-categories?name=".$name;
        return $this->execute($url);
    }

    /**
     * @param array $CategoriesIds
     * @return array of objects
     * {
     *  scheduledAt - date,
     * scheduledFor - date,
     *  parameter - object {id - string}
     * }
     *
     */
    public function GetLatestChanges(array $CategoriesIds) :array
    {
        $url = "/sale/category-parameters-scheduled-changes";
        $requiredChanges = [];
        $result =  json_decode($this->execute($url))->scheduledChanges;
        foreach ($result as $changes)
        {
            for($i=0; $i<count($CategoriesIds); $i++)
            {

                if($changes->type == "REQUIREMENT_CHANGE" && $CategoriesIds[$i]==$changes->category->id)
                {
                    $requiredChanges[] = $changes;
                    unset($CategoriesIds[$i]);
                    break;
                }
            }
        }
        return $requiredChanges;
    }
    public function searchForRequiredFields(array $json) :array
    {
        $required = [];
        foreach ($json["parameters"] as $key=>$value)
        {
            if(isset($value["required"]))
            {
                if($value["required"])
                {
                    $required[] = $value;
                }
            }
        }
        return $required;
    }

    /**
     * @param array $data
     * @return array
     * {
     * name
     *  {
     *     @parameter_id - string
     *     @restrictions - array
     *      for dictionary type
     *      ["multipleChoices"=>boolean]
     *      for string type
     *      ["minLength"=>int, "maxLength"=>int, allowedNumberOfValues=>int]
     *      for int type
     *       ["min"=>int, "max"=>int, "range"=>boolean]
     *     @options - array ["param_id" => string] only if param type is dictionary
     *  }
     * }
     */
    public function getNecessaryData(array $data) :array
    {
        $necessary = [];
        $i = 0;
        foreach($data as $value)
        {
            $necessary[$value["name"]] = ["parameter_id"=>$value["id"]];
            $necessary[$value["name"]]["restrictions"] = [$value["restrictions"]];
            $necessary[$value["name"]]["type"] = $value["type"];
            if(isset($value["dictionary"]))
            {
                $holder = [];
                foreach ($value["dictionary"] as $val)
                {
                    $holder[$val["id"]] = $val["value"];
                }
                $necessary[$value["name"]]["options"] = $holder;
            }
        }
        return $necessary;
    }
    public function execute($url) :string
    {
        $url  =  str_replace(" ","%20",$url);
        $this->ch->set_url($url);
        $this->ch->set_content_type(2);
        $this->ch->set_header($this->app_token);
        $this->ch->execute_curl();
        return $this->ch->get_result();
    }
}


?>