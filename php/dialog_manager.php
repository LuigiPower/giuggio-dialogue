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
    private static $intent_fallback = array(
        "actor",
        "actor_name",
        "award",
        "award_category",
        "award_category_count",
        "award_ceremony",
        "award_count",
        "birth_date ",
        "budget",
        "character",
        "composer",
        "country",
        "date",
        "director",
        "director_name",
        "genre",
        "language",
        "media",
        "movie",
        "movie_count",
        "movie_name",
        "movie_other",
        "organization",
        "other",
        "person",
        "person_name",
        "picture",
        "producer",
        "producer_count",
        "rating",
        "release_date",
        "revenue",
        "review",
        "runtime",
        "star_rating",
        "subjects",
        "synopsis",
        "theater",
        "trailer",
        "writer"
    );

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
    function fill($utterance, $force_intent = false, $force_slu = false)
    {
        if($this->current == DialogManager::$ask_intent || $force_intent)
        {
            foreach($this->probableIntents as $i)
            {
                $str = $i[0];
                $str = $this->sanitize($str);
                if (strpos(trim($utterance), trim($str)) !== false) {
                    $this->setIntent($i[0]);
                    break;
                }
            }

            //TODO find out if this leads to a better conversation
            if($this->intent == null)
            {
                debugEcho("Going into intent fallback checking");
                foreach($this->intent_fallback as $i)
                {
                    $str = $i[0];
                    $str = $this->sanitize($str);
                    if (strpos(trim($utterance), trim($str)) !== false) {
                        $this->setIntent($i[0]);
                        break;
                    }
                }
            }
        }
        else if($this->current == DialogManager::$ask_slu || $force_slu)
        {
            $this->fillField($utterance, "the movie ", "movie.name");
            $this->fillField($utterance, "the director ", "director.name");
            $this->fillField($utterance, "the actor ", "actor");
            $this->fillField($utterance, "the genre ", "genres");
            $this->fillField($utterance, "the country ", "movie.location"); //probable actor too...
            $this->fillField($utterance, "the year ", "movie.release_date");
            $this->fillField($utterance, "the release date ", "movie.release_date");
            $this->fillField($utterance, "the date ", "movie.release_date");
            $this->fillField($utterance, "the language ", "movie.language");
            $this->fillField($utterance, "in ", "movie.language", " language");
            $this->fillField($utterance, "", "movie.language", " language");
            $this->fillField($utterance, "in the ", "movie.language", " language");
            $this->fillField($utterance, "^in ", "movie.language", "");
            $this->fillField($utterance, "long ", "movie", " minutes");
            $this->fillField($utterance, "the duration ", "movie.duration", " minutes");
            //$this->fillField($utterance, "in color ", "color"); doesn't need full regex, just match
            $this->fillField($utterance, "with a budget of ", "movie.budget", " dollars");
            $this->fillField($utterance, "the budget of ", "movie.budget", " dollars");
            $this->fillField($utterance, "the budget for ", "movie.name");
            $this->fillField($utterance, "the keyword ", "movie.keywords");
            $this->fillField($utterance, "the revenue ", "movie.gross_revenue", " dollars");
            $this->fillField($utterance, "earned ", "movie.gross_revenue", " dollars");
            $this->fillField($utterance, "earned ", "movie.gross_revenue");
            $this->fillField($utterance, "the score ", "movie.star_rating");
            //$this->fillField($utterance, "", "movie.star_rating", "stars");
            $this->fillField($utterance, "", "movie.likes", " likes");

            $this->fillField($utterance, "starring ", "actor");
            $this->fillField($utterance, "the actors ", "actor");
            $this->fillField($utterance, "the genres ", "movie.genre");
            $this->fillField($utterance, "the keywords ", "movie.keywords");
            //TODO more fillin options
        }
    }

    function fillField($utterance, $prefix, $field, $suffix = "$")
    {
        if(isset($this->fields[$field])) return; // TODO is this a good idea?

        $matches = array();
        if(preg_match("/$prefix(?<match>([a-zA-Z]* ?)*)$suffix/m", $utterance, $matches))
        {
            $this->setField($field, $matches["match"]);
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
                    $question .= $this->sanitize($intent[0]);
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
        $exploded = explode(', ', reg_replace("/[A-Z]*\(([a-zA-Z]*)\)/m", "$1", $select));
        return $exploded;
    }

    function generateAnswer($mappedIntent, $result)
    {
        debugEcho("Answer is getting generated with mapped intent $mappedIntent");

        //$sanitizedIntent = $this->sanitize($mappedIntent);
        $sanitizedIntent = $this->sanitize($this->intent);
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
            $data = "I found these ".$sanitizedIntent."s: ";

            $numRes = count($result);
            foreach($result as $key=>$res)
            {
                if(strpos($res[$mappedIntent], '|') !== false)
                {
                    $exploded = explode('|', $res[$mappedIntent]);
                    $data .= $this->arrayToString($exploded, "and", $key == $numRes - 1);
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
            $data = "the $sanitizedIntent of $txt is ".$result[0][$mappedIntent];
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
