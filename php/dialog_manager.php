<?php

require_once("text_manager.php");

class DialogManager
{
    public static $start = "start";
    public static $ask_intent = "ask_intent";
    public static $ask_slu = "ask_slu";
    public static $confirm_slu = "confirm_slu";
    public static $done = "done";

    private $last;

    private $intent;
    private $fields;

    private $probableIntents;

    function toArray()
    {
        return array(
            "intent" => $this->intent,
            "fields" => $this->fields,
            "probableIntents" => $this->probableIntents,
            "last" => $this->lastAnswer
        );
    }

    function __construct($state)
    {
        $this->intent = $state["intent"];
        $this->fields = $state["fields"];
        $this->probableIntents = $state["probableIntents"];
        $this->last = $state["last"];
    }

    function addField($field)
    {
        $this->fields[] = $field;
    }

    function setIntent($intent)
    {
        $this->intent = $intent;
    }

    function setProbableIntents($intentList)
    {
        $this->probableIntents = $intentList;
    }

    function generateQuestion()
    {
        $question = "";
        if($this->intent == null)
        {
            $question .= "Did you want to know about a ";

            $numItems = count($this->probableIntents);
            foreach($this->probableIntents as $key=>$intent)
            {
                $question .= $intent[0];
                if($key == $numItems - 2)
                {
                    $question .= " or ";
                }
                else if($key != $numItems - 1)
                {
                    $question .= ", ";
                }
            }

            $question .= "?";
        }
        else if(empty($this->fields))
        {
            if($this->intent != null)
            {
                $question .= "Did you look for the $this->intent of what?";
            }
        }
        return $question;
    }

    function generateAnswer($mappedIntent, $result)
    {
        $ack = TextManager::$acks[rand(0, count(TextManager::$acks) - 1)];

        $data = "";
        if(empty($result))
        {
            $data = "I found nothing";
        }
        else if(count($result) > 1)
        {
            $data = "I found these ".$this->intent."s: ";

            $numRes = count($result);
            foreach($result as $key=>$res)
            {
                $data .= trim(trim($res[$mappedIntent]), ' ');
                if($key == $numRes - 2)
                {
                    $data .= " and ";
                }
                else if($key != $numRes - 1)
                {
                    $data .= ", ";
                }
            }
        }
        else
        {
            // TODO fill in field with what was asked
            $data = "The $this->intent of field is ".$result[0][$mappedIntent];
        }

        $answer = "$ack, $data";
        return $answer;
    }

    function isReadyToSend()
    {
        return $this->intent != null && !empty($this->fields);
    }


}

?>
