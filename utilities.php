<?php

// connect to flask app with flask api url
// send json package to flask app
function sendToMonitor($jsonPackage)
{
    include('config.php');
    $ch = curl_init($flaskApiUrl . '/log/event');
    $headers = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonPackage)
    );
    curl_setopt($ch, CURLOPT_PORT, 5022);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPackage);
    // $timeout = 0.1;
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 50); // DEPLOYMENT CHANGE
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
    $response = curl_exec($ch);

    // check for cURL errors
    if (curl_errno($ch)) {
        error_log( 'cURL error when attempting to communicate with Monitor API: ' . curl_error($ch) );
    } else {
        // TODO : Comment this out once we have the thing working.
        error_log( 'Response from Monitor API: ' . $response );
    }

    // close cURL session
    curl_close($ch);
}

// given <parameter array from $_REQUEST> AND <body object from $data>
// iterate these two and return a combined <jsonPackage> including each item of both
function combineParamsAndBody($paramArray, $bodyObject)
{
    $ret_val = array();

    foreach ($paramArray as $key => $value) {
        $ret_val[$key] = $value;
    }

    foreach ($bodyObject as $key => $value) {
        $ret_val[$key] = $value;
    }

    // remove the elements you do not want to send through log.php to flaskapp
    unset($ret_val["data"]); // contains long string of encoded data
    unset($ret_val["remote_addr"]);
    unset($ret_val["http_user_agent"]);

    return json_encode($ret_val);
}
?>