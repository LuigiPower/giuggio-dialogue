<?php
//ini_set('memory_limit', '-1');

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

$lm         = 'models/mymodels/conceptmodel-witten_bell-3.lm';
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
$uc_nbest = 3;


if(isset($_POST["utterance"]))
{
    $utterance = trim(strtolower($_POST["utterance"]));
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
    $state["last"] = DialogManager::$start;
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
//
$th_slu_accept = 0.87;
//$th_uc_accept = 0.93;
$th_uc_accept = 0.90;
$th_uc_reject = 0.20;
$th_slu_reject = 0.75;

$results = null;
$slu_tags = null;
$slu_conf = null;
foreach($slu_out as $res)
{
    $results = $SR->getConcepts($utterance, $res[0]);
    if(!empty($results))
    {
        $slu_tags = $res[0];
        $slu_conf = $res[1];
        break;
    }
}

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
        $dialog->setIntent($uc_class);
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
    "debug_slu" => ""
);

$response['debug_uc'] = $uc_out;
$response['debug_slu'] = $slu_out;

if (!$uc_found)
{
    //TODO ask user, keep dialogue state
    if($dialog->hasProbableIntents())
    {
        $dialog->fill($utterance);
    }

    if($uc_conf >= $th_uc_reject)
    {
        $dialog->setProbableIntents($uc_out);
    }
}

if($slu_conf >= $th_slu_accept)
{
    foreach($results as $key=>$value)
    {
        $dialog->setField($key, $value);
    }
}
else
{
    if($slu_conf < $th_slu_reject)
    {
        $dialog->fill($utterance);
    }
    else
    {
        //TODO compare nbest, ask user
        foreach($results as $key=>$value)
        {
            $dialog->setField($key, $value);
        }
    }
}
/*
else {
    $response['response'] = 'Not implemented yet!';
}
 */

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
    //$response['debug'] = $db_results;
}
else
{
    if($uc_conf < $th_uc_reject && $slu_conf < $th_slu_reject)
    {
        $response['response'] = "Sorry, I did not understand";
    }
    else
    {
        $response['response'] = $dialog->generateQuestion();
    }
}
$response['state'] = $dialog->toArray();

wrapAndShowJSON(200, true, $response);




