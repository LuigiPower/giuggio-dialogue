<?php
//ini_set('memory_limit', '2048M');

// for SLU processing
require 'FstClassifier.php';
require 'FstSlu.php';
require 'SluResults.php';
// for DB
require 'Slu2DB.php';
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

$output = "";
$count = 0;

$uc_out  = $UC->predict($utterance, TRUE, 100);

foreach($uc_out as $res)
{
    if($res[1] > 0.01)
    {
        $count++;
    }
    else
    {
        break;
    }
}

//print_r($slu_out);
$output = rtrim($output, ',');
echo $count."\n";
