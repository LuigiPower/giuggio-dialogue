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
    public static $intent_fallback = array(
        "actor" => "actor",
        "actor_name" => "actor_name",
        "award" => "award",
        "award_category" => "award_category",
        "award_category_count" => "award_category_count",
        "award_ceremony" => "award_ceremony",
        "award_count" => "award_count",
        "birth_date " => "birth_date ",
        "budget" => "budget",
        "character" => "character",
        "composer" => "composer",
        "country" => "country",
        "date" => "date",
        "director" => "director",
        "director_name" => "director_name",
        "genre" => "genre",
        "language" => "language",
        "media" => "media",
        "movie" => "movie",
        "movie_count" => "movie_count",
        "movie_name" => "movie_name",
        "movie_other" => "movie_other",
        "organization" => "organization",
        "other" => "other",
        "person" => "person",
        "person_name" => "person_name",
        "picture" => "picture",
        "producer" => "producer",
        "producer_count" => "producer_count",
        "rating" => "rating",
        "release_date" => "release_date",
        "revenue" => "revenue",
        "review" => "review",
        "runtime" => "runtime",
        "duration" => "runtime",
        "movie_length" => "runtime",
        "star_rating" => "star_rating",
        "subjects" => "subjects",
        "synopsis" => "synopsis",
        "theater" => "theater",
        "trailer" => "trailer",
        "writer" => "writer"
    );

    public static $start = "start";
    public static $ask_intent = "ask_intent";
    public static $ask_slu = "ask_slu";
    public static $confirm_slu = "confirm_slu";

    public static $answer_limit = 3;

    private $current;

    // Intent: Contains user intent
    // Requirement to run a query
    private $intent;
    // Fields: Contains filters specified by the user
    // Requirement to run a query
    private $fields;

    // List of probable intents
    private $probableIntents;

    // List of possible SLU taggings
    private $probableFields;
    private $askedField;
    private $confirmedFields;

    private $operand;
    private $countResults;

    private $ditchedDialog;

    function toArray()
    {
        return array(
            "intent" => $this->intent,
            "fields" => $this->fields,
            "probableIntents" => $this->probableIntents,
            "probableFields" => $this->probableFields,
            "confirmedFields" => $this->confirmedFields,
            "current" => $this->current,
            "askedField" => $this->askedField,
            "operand" => $this->operand,
            "countResults" => $this->countResults,
            "ditchedDialog" => $this->ditchedDialog
        );
    }

    function __construct($state)
    {
        $this->intent = $state["intent"];
        $this->fields = $state["fields"];
        $this->probableIntents = $state["probableIntents"];
        $this->probableFields = $state["probableFields"];
        $this->confirmedFields = $state["confirmedFields"];
        $this->current = $state["current"];
        $this->askedField = $state["askedField"];
        $this->operand = $state["operand"];
        $this->countResults = $state["countResults"];

        if(isset($state["ditchedDialog"]))
        {
            $this->ditchedDialog = $state["ditchedDialog"];
        }
        else
        {
            $this->ditchedDialog = null;
        }
    }

    function clearAndStore()
    {
        $olddialog = $this->toArray();
        $olddialog['fresh'] = true;

        $this->intent = null;
        $this->fields = array();
        $this->probableIntents = array();
        $this->probableFields = array();
        $this->confirmedFields = array();
        $this->current = DialogManager::$start;
        $this->askedField = null;
        $this->operand = null;
        $this->countResults = false;

        $this->ditchedDialog = $olddialog;
    }

    function restore()
    {
        $this->intent = $this->ditchedDialog['intent'];
        $this->fields = $this->ditchedDialog['fields'];
        $this->probableIntents = $this->ditchedDialog['probableIntents'];
        $this->probableFields = $this->ditchedDialog['probableFields'];
        $this->confirmedFields = $this->ditchedDialog['confirmedFields'];
        $this->current = $this->ditchedDialog['current'];
        //$this->askedField = $this->ditchedDialog['askedField'];
        $this->askedField = null;
        $this->operand = $this->ditchedDialog['operand'];
        $this->countResults = $this->ditchedDialog['countResults'];

        $this->ditchedDialog = null;
    }

    function getDitchedDialog()
    {
        return $this->ditchedDialog;
    }

    function isIn($state)
    {
        debugEcho("I'm in ".$this->current);
        return $this->current === $state;
    }

    function getOperand()
    {
        debugEcho("Getting operand $this->operand");
        return $this->operand;
    }

    function isCountRequest()
    {
        return $this->countResults;
    }

    function areFieldsConfirmed()
    {
        $confirmed = true;
        foreach($this->probableFields as $field)
        {
            $confirmed = $confirmed && $field->confirmed;
        }
        return $confirmed;
    }

    /**
     * Fills the dialog state when the request is inside the domain, but outside
     * of the training set
     * @param $utterance phrase said by user
     */
    function fill($utterance, $force_intent = false, $force_slu = false)
    {
        debugEcho("Filling with state $this->current");

        if($this->current == DialogManager::$start)
        {
            if($this->ditchedDialog !== null)
            {
                foreach(TextManager::$affirmative as $aff)
                {
                    if($this->startswith($utterance, $aff))
                    {
                        debugEcho("Now restoring old dialog state");
                        $this->restore();
                        return;
                    }
                }

                debugEcho("Now deleting old dialog state");
                if($this->ditchedDialog['fresh'])
                {
                    $this->ditchedDialog['fresh'] = false;
                }
                else
                {
                    $this->ditchedDialog = null;
                }
            }

            foreach(TextManager::$greater as $gt)
            {
                if($this->match($utterance, $gt))
                {
                    $this->operand = ">";
                }
            }

            foreach(TextManager::$lesser as $lt)
            {
                if($this->match($utterance, $lt))
                {
                    $this->operand = "<";
                }
            }

            foreach(TextManager::$count as $count)
            {
                if($this->startswith($utterance, $count))
                {
                    $this->countResults = true;
                }
            }
        }

        if($this->current == DialogManager::$ask_intent || $force_intent)
        {
            $matches = array();
            foreach($this->probableIntents as $i)
            {
                $str = $i[0];
                $str = $this->sanitize($str);
                if (strpos(trim($utterance), trim($str)) !== false) {
                    //$this->setIntent($i[0]);
                    $matches[] = $i[0];
                    //break;
                }
            }

            if(!empty($matches))
            {
                $toset = "";
                $max = 0;
                foreach($matches as $match)
                {
                    if(strlen($match) > $max)
                    {
                        $max = strlen($match);
                        $toset = $match;
                    }
                }
                $this->setIntent($toset);
            }

            //TODO find out if this leads to a better conversation
            if($this->intent == null)
            {
                debugEcho("Going into intent fallback checking");
                foreach(DialogManager::$intent_fallback as $k=>$i)
                {
                    $str = $k;
                    $str = $this->sanitize($str);
                    debugEcho("Checking $utterance with $str");
                    if (strpos(trim($utterance), trim($str)) !== false) {
                        $this->setIntent($i);
                        break;
                    }
                }
            }
        }

        if($this->current == DialogManager::$confirm_slu)
        {
            debugEcho("Starting confirm slu with field");
            debugPrint($this->askedField);

            if($this->askedField['concept_fixed']
                && !$this->askedField['confirmed'])
            {
                $this->askedField['value'] = $utterance;
                $this->askedField['confirmed'] = true;
            }

            if(!$this->askedField['confirmed'])
                //&& !$this->askedField['negated'])
            {
                foreach(TextManager::$affirmative as $aff)
                {
                    if($this->startswith($utterance, $aff))
                    {
                        debugEcho("$utterance matches with $aff");
                        $this->askedField['concept_fixed'] = true;
                        $this->askedField['negated'] = false;
                        break;
                    }
                }

                foreach(TextManager::$negative as $neg)
                {
                    if($this->match($utterance, $neg))
                    {
                        debugEcho("$utterance matches with $neg");
                        $this->askedField['negated'] = true;
                        break;
                    }
                }

                if(!$this->askedField['negated'])
                {
                    debugEcho("Confirming because not negated and yes present");
                    $this->askedField['confirmed'] = true;
                }
            }

            if(!$this->askedField['confirmed'])
            {
                foreach(TextManager::$negative as $neg)
                {
                    if($this->startswith($utterance, $neg))
                    {
                        debugEcho("Negating because not confirmed and no present");
                        $this->askedField['negated'] = true;
                    }
                }

                foreach(TextManager::$user_answer_to_field as $concept=>$slu_concept)
                {
                    $exploded = explode('.', $concept);
                    $check = $this->sanitize($exploded[count($exploded)-1]);
                    debugEcho("testing $utterance $check");
                    if($this->match($utterance, $check))
                    {
                        debugEcho("$utterance matches $check");

                        // TODO could overwrite another probable field, maybe ask user?
                        unset($this->probableFields[$this->askedField['key']]);

                        $this->askedField['key'] = $slu_concept;
                        $this->askedField['concept_fixed'] = true;

                        $this->probableFields[$this->askedField['key']] = $this->askedField['value'];

                        //Try and fill in the value too
                        /*
                        debugEcho("Trying regexes on $utterance ($check)");
                        if($this->match($utterance, $check." ", $concept)
                            || $this->fillField($utterance, "it is a ", $concept, " ".$check)
                            || $this->fillField($utterance, "it is ", $concept, " ".$check)
                            || $this->fillField($utterance, "they are ", $concept, " ".$check))
                        {
                            debugEcho("REGEX MATCH");
                            $this->askedField['confirmed'] = true;
                        }
                         */
                        break;
                    }
                }
            }

            foreach(TextManager::$ignore as $ign)
            {
                if($this->startswith($utterance, $ign))
                {
                    debugEcho("Ignoring because $ign present");
                    $this->askedField['ignored'] = true;
                    $this->askedField['confirmed'] = false;
                }
            }

            if($this->askedField['confirmed'])
            {
                debugEcho("Confirmed field");
                unset($this->probableFields[$this->askedField['key']]);
                $this->confirmedFields[$this->askedField['key']] = $this->askedField['value'];
                $this->askedField = null;
            }
            else if($this->askedField['ignored'])
            {
                unset($this->probableFields[$this->askedField['key']]);
                $this->askedField = null;
            }

            if(empty($this->probableFields))
            {
                foreach($this->confirmedFields as $key=>$value)
                {
                    $this->fields[$key] = $value;
                }
                $this->confirmedFields = array();
            }
        }

        if($this->current == DialogManager::$ask_slu || $force_slu)
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

    function match($utterance, $text)
    {
        return strpos($utterance, $text) !== false;
    }

    function startswith($utterance, $text)
    {
        return 0 === strpos($utterance, $text);
    }

    function fillField($utterance, $prefix, $field, $suffix = "")
    {
        //if(isset($this->fields[$field])) return; // TODO is this a good idea?

        $regex = "/$prefix(?<match>([a-zA-Z1-9]+ ?)+)$suffix/m";
        debugEcho("Trying regex on [$utterance]:\n$regex");
        $matches = array();
        if(preg_match($regex, $utterance, $matches))
        {
            debugEcho("$utterance matches!");
            $this->setField($field, $matches["match"]);
            return true;
        }
        return false;
    }

    function getFields()
    {
        $newfields = array();
        foreach($this->fields as $key=>$value)
        {
            $newfields[$this->remove_id($key)] = $value;
        }
        return $newfields;
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

    function setProbableFields($fields)
    {
        $this->probableFields = $fields;
    }

    function setIntent($intent)
    {
        $this->intent = $intent;
        if($intent === "movie_count")
        {
            $this->countResults = true;
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

    function fieldDictToString($fields)
    {
        $data = "";
        $count = count($fields);
        $i = 0;
        foreach($fields as $concept=>$value)
        {
            // TODO different strings based on concept?
            if($this->match($concept, "movie.name"))
            {
                $data .= "titled $value";
            }
            else if($this->match($concept, "actor.name"))
            {
                $data .= "starring $value";
            }
            else if($this->match($concept, "movie.release_date"))
            {
                $data .= "released ";
                if(!$this->operand)
                {
                    $data .= "in ";
                }
                else if($this->operand === ">")
                {
                    $data .= "after ";
                }
                else
                {
                    $data .= "before ";
                }
                $data .= "$value";
            }
            else if($this->match($concept, "movie.language"))
            {
                $data .= "in $value";
            }
            else if($this->match($concept, "movie.duration"))
            {
                if(!$this->operand)
                {
                    $data .= "$value minutes long";
                }
                else if($this->operand === ">")
                {
                    $data .= "longer than $value minutes";
                }
                else
                {
                    $data .= "shorter than $value minutes";
                }
            }
            else
            {
                $data .= $this->sanitize($concept). " " .$value;
            }

            if($i == $count - 2)
            {
                $data .= " and ";
            }
            else if($i != $count - 1)
            {
                $data .= ", ";
            }
        }
        return $data;
    }

    function generateQuestion()
    {
        $question = "";
        if($this->intent == null)
        {
            if(empty($this->probableIntents))
            {
                //TODO Add info about the fields we currently have inside the question?
                if(empty($this->fields))
                {
                    $question .= "What are you looking for?";
                }
                else
                {
                    $question .= "What do you want to know about movies ";
                    $question .= $this->fieldDictToString($this->fields);
                    $question .= "?";
                }
                $this->current = DialogManager::$ask_intent;
            }
            else
            {
                $question .= "Did you want to know about a ";

                $numItems = min(count($this->probableIntents), DialogManager::$answer_limit);
                $loops = 0;
                foreach($this->probableIntents as $key=>$intent)
                {
                    if($loops >= DialogManager::$answer_limit)
                        break;
                    $loops++;

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
            // TODO if fields are not empty but I did not ask for each probable field it won't ask for the others
            // TODO fix that
            if(empty($this->probableFields))
            {
                $question .= "Did you look for the ".$this->sanitize($this->intent)." of what?";
                $this->current = DialogManager::$ask_slu;
            }
            else if($this->askedField == null)
            {
                $question .= "I am not sure about something. ";
                foreach($this->probableFields as $field=>$value)
                {
                    $question .= "Is $value a ".$this->sanitize($field)."?";
                    $this->askedField = array(
                        'key' => $field,
                        'value' => $value,
                        'negated' => false,
                        'confirmed' => false,
                        'ignored' => false,
                        'concept_fixed' => false
                    );
                    break; //TODO I don't know how to get the first key/value pair
                }
                $this->current = DialogManager::$confirm_slu;
            }
            else if(!$this->askedField['confirmed'])
            {
                debugEcho("Checking asked field not confirmed");
                debugPrint($this->askedField);

                if($this->askedField['negated'])
                {
                    if($this->askedField['concept_fixed'])
                    {
                        $question .= "It seems I got it wrong then. Can you tell me the correct ".$this->sanitize($this->askedField['key'])."? Or is it a mistake?";
                    }
                    else
                    {
                        $question .= "So what is ".$this->askedField['value']."? Or is it a mistake?"; // or should I ignore it?
                        //TODO maybe add "nothing" answer by the user to remove the field
                        //or similar
                    }
                }
            }
        }
        return $question;
    }

    function arrayToString($array, $con, $last, $limit)
    {
        $data = "";
        $numEls = min(count($array), $limit + 1);
        foreach($array as $key=>$s)
        {
            $data .= trim(trim($s), ' ');
            if($key == $numEls - 2 && $last)
            {
                if($numEls == $limit + 1)
                {
                    $data .= " and others";
                    break;
                }
                else
                {
                    $data .= " $con ";
                }
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
        return $this->remove_id(str_replace(".", " ", str_replace("_", " ", $concept)));
    }

    function remove_id($concept)
    {
        return strtok($concept, ':');
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
        else if(!$this->countResults && count($result) > 1 || strpos($result[0][$mappedIntent], '|') !== false)
        {
            debugEcho("Answer, multiple:");
            debugPrint($result);
            $data = "I found these ".$sanitizedIntent."s: ";

            $numRes = min(count($result), DialogManager::$answer_limit + 1);
            foreach($result as $key=>$res)
            {
                if(strpos($res[$mappedIntent], '|') !== false)
                {
                    $exploded = explode('|', $res[$mappedIntent]);
                    $data .= $this->arrayToString($exploded, "and", $key == $numRes - 1, DialogManager::$answer_limit);
                }
                else
                {
                    $data .= trim(trim($res[$mappedIntent]), ' ');
                    if($key == $numRes - 2)
                    {
                        if($numRes == DialogManager::$answer_limit + 1)
                        {
                            $data .= " and others";
                            break;
                        }
                        else
                        {
                            $data .= " and ";
                        }
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
            if($this->countResults)
            {
                $count = count($result);
                $sanitizedIntent = str_replace(" count", "", $sanitizedIntent);
                if($count == 0)
                {
                    $data .= "There are no $sanitizedIntent ";
                }
                else if($count == 1)
                {
                    $data .= "There is $count $sanitizedIntent ";
                }
                else
                {
                    $data .= "There are $count $sanitizedIntent"."s ";
                }
                $data .= $this->fieldDictToString($this->fields);
            }
            else
            {
                $txt = $this->fieldDictToString($this->fields);
                if($this->match($sanitizedIntent, "movie"))
                {
                    $data .= "Here is additional information on a movie $txt";
                }
                else
                {
                    $data = "the $sanitizedIntent of a movie $txt is ".$result[0][$mappedIntent];
                    if($this->match($sanitizedIntent, "budget"))
                    {
                        $data .= "$";
                    }
                }
            }
        }

        $answer = "$ack, $data.";

        if($this->ditchedDialog !== null)
        {

            if($this->ditchedDialog['fields'] !== null && count($this->ditchedDialog['fields']) > 0)
            {
                $oldfields = $this->fieldDictToString($this->ditchedDialog['fields']);
                $answer .= " Do you want to go back to your search involving movies $oldfields?";
            }
            else if($this->ditchedDialog['intent'] !== null)
            {
                $oldintent = $this->ditchedDialog['intent'];
                $answer .= " Do you want to go back to your search of a ".$this->sanitize($oldintent)."?";
            }
            else
            {
                $answer .= " Do you want to go back to your previous search?";
            }
            $answer .= " Otherwise just ask the next question.";
        }

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
