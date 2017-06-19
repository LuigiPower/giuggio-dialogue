<?php

require_once("text_manager.php");

function debugEcho($message)
{
    if(isset($_POST["debug"]))
    {
        echo $message."\n";
    }
}

function debugPrint($object)
{
    if(isset($_POST["debug"]))
    {
        print_r($object);
    }
}

class DialogManager
{
    public static $start = "start";
    public static $ask_intent = "ask_intent";
    public static $ask_slu = "ask_slu";
    public static $confirm_slu = "confirm_slu";

    private $current;

    private $intent;
    private $fields;

    private $probableIntents;

    function toArray()
    {
        return array(
            "intent" => $this->intent,
            "fields" => $this->fields,
            "probableIntents" => $this->probableIntents,
            "current" => $this->current
        );
    }

    function __construct($state)
    {
        $this->intent = $state["intent"];
        $this->fields = $state["fields"];
        $this->probableIntents = $state["probableIntents"];
        $this->current = $state["current"];
    }

    /**
     * Fills the dialog state when the request is inside the domain, but outside
     * of the training set
     * @param $utterance phrase said by user
     */
    function fill($utterance)
    {
        if($this->current == DialogManager::$ask_intent)
        {
            foreach($this->probableIntents as $i)
            {
                $str = $i[0];
                $str = str_replace(".", " ", $str);
                if (strpos(trim($utterance), trim($str)) !== false) {
                    $this->setIntent($i[0]);
                    break;
                }
            }
        }
        else if($this->current == DialogManager::$ask_slu)
        {
            $matches = array();
            if(preg_match("/the movie (?<name>([a-zA-Z]* ?)*)/m", $utterance, $matches))
            {
                $this->setField("movie.name", $matches["name"]);
            }
            //TODO more fillin options
        }
    }

    function getFields()
    {
        return $this->fields;
    }

    function getIntent()
    {
        return $this->intent;
    }

    function setField($key, $field)
    {
        if($this->current != DialogManager::$ask_intent)
        {
            $this->fields[$key] = $field;
        }
    }

    function setIntent($intent)
    {
        if($this->current != DialogManager::$ask_slu)
        {
            $this->intent = $intent;
        }
    }

    function setProbableIntents($intentList)
    {
        $this->probableIntents = $intentList;
    }

    function hasProbableIntents()
    {
        return !empty($this->probableIntents);
    }

    function generateQuestion()
    {
        $question = "";
        if($this->intent == null)
        {
            if(empty($this->probableIntents))
            {
                $question .= "What are you looking at?";
            }
            else
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
                $this->current = DialogManager::$ask_intent;
            }
        }
        else if(empty($this->fields))
        {
            if($this->intent != null)
            {
                $question .= "Did you look for the $this->intent of what?";
            }
            $this->current = DialogManager::$ask_slu;
        }
        return $question;
    }

    function arrayToString($array, $con, $last)
    {
        $data = "";
        $numEls = count($array);
        foreach($array as $key=>$s)
        {
            $data .= trim(trim($s), ' ');
            if($key == $numEls - 2 && $last)
            {
                $data .= " $con ";
            }
            else if($key != $numEls - 1 || !$last)
            {
                $data .= ", ";
            }
        }
        return $data;
    }

    function sanitize($concept)
    {
        return str_replace(".", " ", str_replace("_", " ", $concept));
    }

    function sanitizeSelectString($select)
    {
        return explode(', ', str_replace("DISTINCT", "", $select));
    }

    function generateAnswer($mappedIntent, $result)
    {
        $ack = TextManager::$acks[rand(0, count(TextManager::$acks) - 1)];

        $data = "";
        if(empty($result))
        {
            $data = "I found nothing";
        }
        else if(count($result) > 1 || strpos($result[0][$mappedIntent], '|') !== false)
        {
            debugEcho("Answer, multiple:");
            debugPrint($result);
            $data = "I found these ".$this->intent."s: ";

            $numRes = count($result);
            foreach($result as $key=>$res)
            {
                if(strpos($res[$mappedIntent], '|') !== false)
                {
                    $exploded = explode('|', $res[$mappedIntent]);
                    $data .= $this->arrayToString($exploded, "and", $key == $numRes - 1);
                    /*
                    $numExpl = count($exploded);
                    foreach($exploded as $exKey=>$s)
                    {
                        $data .= trim(trim($s), ' ');
                        if($exKey == $numExpl - 2 && $key == $numRes - 1)
                        {
                            $data .= " and ";
                        }
                        else if($exKey != $numExpl - 1 || $key != $numRes - 1)
                        {
                            $data .= ", ";
                        }
                    }
                     */
                }
                else
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
        }
        else
        {
            // TODO fill in field with what was asked
            $txt = "";
            foreach($this->fields as $field)
            {
                $txt .= $field." ";
            }
            $txt = trim($txt);
            $data = "The $this->intent of $txt is ".$result[0][$mappedIntent];
        }

        $answer = "$ack, $data";
        $this->current = DialogManager::$start;
        return $answer;
    }

    function isIntentReady()
    {
        return $this->intent != null;
    }

    function areFieldsReady()
    {
        return !empty($this->fields);
    }

    function isReadyToSend()
    {
        return $this->isIntentReady() && $this->areFieldsReady();
    }


}

?>
