<?php
ini_set('memory_limit', '2048M');

require_once("./utility.php");
require_once("./dialog_manager.php");
// for SLU processing
require 'FstClassifier.php';
require 'FstSlu.php';
require 'SluResults.php';
// for DB
require_once("Slu2DB.php");
require 'QueryDB.php';

// configure paths
$classifier = 'models/MAP.fst';
$cilex      = 'models/classifier.lex';
$colex      = 'models/classifier.lex';
//$lm         = 'models/slu.lm';
//$wfst       = 'models/wfst.fst';
//$sluilex    = 'models/slu.lex';
//$sluolex    = 'models/slu.lex';

$lm         = 'models/mymodels/conceptmodel-kneser_ney-9.lm';
$wfst       = 'models/mymodels/nofeats.txt.fsa';
$sluilex    = 'models/mymodels/nofeats.lex';
$sluolex    = 'models/mymodels/nofeats.lex';

$unk        = '<unk>';

$UC  = new FstClassifier($classifier, $cilex, $colex, $unk);
$SLU = new FstSlu($wfst, $lm, $sluilex, $sluolex, $unk);
$SR  = new SluResults();
$QC  = new Slu2DB();
$DB  = new QueryDB();

$slu_nbest = 3;
//$uc_nbest = 3;
// It gets a list of all possible intents,
// makes it possible to base checking user
// answers on the intents provided by the classifier
// (fallback still in place for additional things
// that can be said by the user)
$uc_nbest = 40;


if(isset($_POST["utterance"]))
{
    $utterance = trim(strtolower($_POST["utterance"]));
}

$asr_confidence = 1;
if(isset($_POST["asr_confidence"]))
{
    $asr_confidence = $_POST["asr_confidence"];
}

$state = array();
if(isset($_POST["dialog_state"]))
{
    $state = json_decode($_POST["dialog_state"], true);
}

if(!isset($_POST["dialog_state"]) || $state["current"] == DialogManager::$start)
{
    $state["intent"] = null;
    $state["fields"] = array();
    $state["probableIntents"] = array();
    $state["probableFields"] = array();
    $state["confirmedFields"] = array();
    $state["askedField"] = null;
    $state["current"] = DialogManager::$start;
}

$dialog = new DialogManager($state);


//----------------------------------------------------------------------
// Run SLU
//----------------------------------------------------------------------
// Arguments:
// - utterance
// - to get confidence or not
// - nbest number
$slu_out = $SLU->runSlu($utterance, TRUE, $slu_nbest);

//----------------------------------------------------------------------
// Run Utterance Classifier
//----------------------------------------------------------------------
// Arguments:
// - utterance
// - to get confidence or not
// - nbest number

// [0] --> class, [1] --> confidence
$uc_out = $UC->predict($utterance, TRUE, $uc_nbest);

//$slu_tags = $slu_out[0][0];
//$slu_conf = $slu_out[0][1];
//$results = $SR->getConcepts($utterance, $slu_tags);

/**
 * thresholds to test without error recovery
 */
$th_slu_accept = 0;
//$th_uc_accept = 0.93;
$th_uc_accept = 0;
$th_uc_reject = 0;
$th_slu_reject = 0;
/****************************************/
/**
 * thresholds with fstprintstrings fix
 */
$th_slu_accept = 0.87;
//$th_uc_accept = 0.93;
$th_uc_accept = 0.90;
$th_uc_reject = 0.20;
$th_slu_reject = 0.75;
/****************************************/
/**
 * thresholds with fstprintstrings fix
 * AND --nshortest set to nbest * 10
 */
//$th_slu_accept = 0.76;
//$th_uc_accept = 0.93;
//$th_uc_accept = 0.90;
//$th_uc_reject = 0.20;
//$th_slu_reject = 0.1;
/****************************************/

/**
 * Thresholds with fstprintstrings FIX
 * AND skipping paths with just O
 */
//$th_slu_accept = 0.87;
//$th_uc_accept = 0.93;
//$th_uc_accept = 0.90;
//$th_uc_reject = 0.20;
//$th_slu_reject = 0.33; //Using the old 0.33, from the threshold with the BADs
/****************************************/

$probableFields = array();
$results = null;
$slu_tags = null;
$slu_conf = null;
$slu_result = null;
foreach($slu_out as $res)
{
    $results = $SR->getConcepts($utterance, $res[0]);
    if(!empty($results) && $slu_tags == null)
    {
        $slu_tags = $res[0];
        $slu_conf = $res[1];
        $slu_result = $results;
        debugEcho("Slu result");
        debugPrint($slu_result);
    }

    if(!empty($results) && $res[1] < $th_slu_accept)
            //&& $res[1] > $th_slu_reject)
    {
        $probableFields[] = $results;
    }
}

debugPrint($probableFields);

$uc_found = false;
$uc_class = "";
$uc_conf = $uc_out[0][1];
foreach($uc_out as $uc_res)
{
    if($uc_res[1] >= $th_uc_accept)
    {
        $uc_found = true;
        $uc_class = $uc_res[0];
        $uc_conf = $uc_res[1];
        debugEcho("Found $uc_conf > $th_uc_accept and ".$dialog->isIn(DialogManager::$start));
        if($dialog->isIn(DialogManager::$start))
        {
            $dialog->setIntent($uc_class);
        }
        break;
    }
}

//----------------------------------------------------------------------
// Dialog Management & Natural Language Generation
//----------------------------------------------------------------------
$response = array(
    "response" => "",
    "db_result" => array(),
    "state" => array(),
    "debug_uc" => "",
    "debug_slu" => "",
    "query" => ""
);

$response['debug_uc'] = $uc_out;
$response['debug_slu'] = $slu_out;

if (!$uc_found)
{
    if($uc_conf >= $th_uc_reject)
    {
        $dialog->setProbableIntents($uc_out);
    }
    else
    {

    }
}

if($slu_conf >= $th_slu_accept && $dialog->isIn(DialogManager::$start))
{
    foreach($slu_result as $key=>$value)
    {
        $dialog->setField($key, $value);
    }
}
else
{
    if(count($probableFields) > 0 && !$dialog->isIn(DialogManager::$confirm_slu))
    {
        $dialog->setProbableFields($probableFields[0]);
    }
    else
    {
        // We don't have fields
    }
}
/*
else {
    $response['response'] = 'Not implemented yet!';
}
 */

$dialog->fill($utterance);

if($dialog->isReadyToSend())
{
    //------------------------------------------------------------------
    // Convert SLU results to SQL Query
    //------------------------------------------------------------------
    $query = $QC->slu2sql($dialog->getFields(), $dialog->getIntent());
    //------------------------------------------------------------------
    // Query DB
    //------------------------------------------------------------------
    $db_results = $DB->query($query);
    debugEcho($query);

    debugEcho("mapping ".$dialog->getIntent());
    $db_class = $QC->answer_mapping($dialog->getIntent());

    $response['response'] = $dialog->generateAnswer($db_class, $db_results);
    $response['db_result'] = $db_results;
    $response['query'] = $query;
    //$response['debug'] = $db_results;
}
else
{
    /*
    if($uc_conf < $th_uc_reject && $slu_conf < $th_slu_reject)
    {
        $response['response'] = "Sorry, I did not understand";
    }
     */
    $response['response'] = $dialog->generateQuestion();
}
$response['state'] = $dialog->toArray();

wrapAndShowJSON(200, true, $response);




