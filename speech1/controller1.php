<?php

    $asr_output = $_GET['SLUObject'];
    $tts_output="Hello. Star Wars The Force Awakens will release at the end of the year.";
    $tts = array('results' => $tts_output);
    $json = json_encode($tts);
    $callback = $_GET['callback'];
    echo $callback.'('. $json . ')';
?>
