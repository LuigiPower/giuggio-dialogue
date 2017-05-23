<?php
/*****
 * Example script to do:
 *
 * (1) convert input string to fst
 * (2) apply utterance classification (Naive Bayes as FST) &
 *     get expected answer type/concept
 * (3) apply SLU model &
 *     get associative array of concepts and spans
 * (4) convert SLU results to SQL
 * (5) Query DB
 */

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

//----------------------------------------------------------------------
// Run SLU
//----------------------------------------------------------------------
// Arguments:
// - utterance
// - to get confidence or not
// - nbest number

$slu_out = $SLU->runSlu($utterance, TRUE, 3);
//print_r($SLU->runSlu($utterance));
//print_r($SLU->runSlu($utterance, TRUE));
//print_r($SLU->runSlu($utterance, FALSE, 3));
print_r($slu_out);

//----------------------------------------------------------------------
// Run Utterance Classifier
//----------------------------------------------------------------------
// Arguments:
// - utterance
// - to get confidence or not
// - nbest number

$uc_out  = $UC->predict($utterance, TRUE, 3);
//print_r($UC->predict($utterance));
//print_r($UC->predict($utterance, TRUE));
//print_r($UC->predict($utterance, FALSE, 5));
print_r($uc_out);

// CHANGE THIS TO DESIRED version
$slu_tags = $slu_out[0][0];
$slu_conf = $slu_out[0][1];
$results = $SR->getConcepts($utterance, $slu_tags);

$uc_class = $uc_out[0][0];
$uc_conf  = $uc_out[0][1];


echo 'SLU Concepts and Values: ' . "\n";
print_r($results);
echo 'SLU Confidence: ' . $slu_conf. "\n";

echo 'Requested concept: ' . $uc_class . "\n";
echo 'Requested concept confidence: ' . $uc_conf . "\n";


//----------------------------------------------------------------------
// Dialog Management & Natural Language Generation
//----------------------------------------------------------------------
// DEVELOP THIS PART!
// Example
$th_accept = 0.9;
$th_reject = 0.4;

if ($slu_conf >= $th_accept && $uc_conf >= $th_accept) {
	//------------------------------------------------------------------
	// Convert SLU results to SQL Query
	//------------------------------------------------------------------
	$query = $QC->slu2sql($results, $uc_class);
	echo 'SQL: ' . $query . "\n";

	//------------------------------------------------------------------
	// Query DB
	//------------------------------------------------------------------
	$db_results = $DB->query($query);

	echo 'DB Results: ' ."\n";
	print_r($db_results);

	$response = $db_results[0][$uc_class];
}
elseif ($slu_conf < $th_reject || $uc_conf < $th_reject) {
	$response = 'Sorry, I did not understand!';
}
else {
	$response = 'Not implemented yet!';
}

echo 'System Response: ' . $response . "\n";




