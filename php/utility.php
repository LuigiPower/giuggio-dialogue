<?php

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

?>
