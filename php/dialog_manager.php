<?php

class DialogManager
{
    private $intent;
    private $fields;

    function __construct($state)
    {
        $intent = $state["intent"];
        $fields = $state["fields"];
    }

    function addField($field)
    {
        $this->fields[] = $field;
    }

    function setIntent($intent)
    {
        $this->$intent = $intent;
    }

    function toJson()
    {
        return json_encode(array(
            "intent" => $this->$intent,
            "fields" => $this->$fields
        ));
    }

}

?>
