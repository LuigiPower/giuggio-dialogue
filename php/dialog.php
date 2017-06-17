<?php
require_once("./dialog_manager.php");
// for SLU processing
require 'FstClassifier.php';
require 'FstSlu.php';
require 'SluResults.php';
// for DB
require_once("Slu2DB.php");
require 'QueryDB.php';

function setupHeader($type)
{
    header("Content-Type: $type");
}

function setResponseCode($responseCode)
{
    http_response_code($responseCode);
}

function wrapAndShowJSON($resultcode, $success, $result, $numpages = 0)
{
    setupHeader("application/json");
    setResponseCode($resultcode);
    $toecho = array(
        'resultcode' => $resultcode,
        'success' => $success,
        'result' => $result,
        'pages' => $numpages
    );

    echo json_encode($toecho, JSON_FORCE_OBJECT);
}

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

//----------------------------------------------------------------------
// For testing from command line
//----------------------------------------------------------------------
$args = getopt('u:');

if (isset($args['u'])) {
    $utterance = trim(strtolower($args['u']));
}
else {
    // Example Utterance
    $utterance = 'who directed avatar';
}

if(isset($_POST["utterance"]))
{
    $utterance = $_POST["utterance"];
}

$slu_nbest = 3;
$uc_nbest = 3;

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

//----------------------------------------------------------------------
// Dialog Management & Natural Language Generation
//----------------------------------------------------------------------

$state = array();
if(isset($_POST["dialog_state"]))
{
    $state = json_decode($_POST["dialog_state"], true);
}
else
{
    $state["intent"] = null;
    $state["fields"] = array();
    $state["probableIntents"] = array();
    $state["last"] = DialogManager::$start;
}
$dialog = new DialogManager($state);

$th_slu_accept = 0.87;
$th_uc_accept = 0.93;
//$th_uc_accept = 0.80;
$th_slu_reject = 0.75;

$response = array(
    "response" => "",
    "state" => array(),
    "debug" => ""
);

$uc_found = false;
$uc_class = "";
$uc_conf = 0;
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

if (!$uc_found)
{
    //TODO ask user, keep dialogue state
    $dialog->setProbableIntents($uc_out);
}

if($slu_conf >= $th_slu_accept)
{
    foreach($results as $res)
    {
        $dialog->addField($res);
    }
}

if ($slu_conf < $th_slu_reject)
{
    //TODO ask user, keep dialogue state
}
else {
    $response['response'] = 'Not implemented yet!';
}


if($dialog->isReadyToSend())
{
    //------------------------------------------------------------------
    // Convert SLU results to SQL Query
    //------------------------------------------------------------------
    $query = $QC->slu2sql($results, $uc_class);

    //------------------------------------------------------------------
    // Query DB
    //------------------------------------------------------------------
    $db_results = $DB->query($query);

    $db_class = $QC->db_mapping($uc_class);

    $response['response'] = $dialog->generateAnswer($db_class, $db_results);
    $response['debug'] = $db_results;
}
else
{
    $response['response'] = $dialog->generateQuestion();
}
$response['state'] = $dialog->toArray();

wrapAndShowJSON(200, true, $response);




