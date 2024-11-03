<?php
/**
 * Module for forwarding data to the monitor service.
 */

// connect to flask app with flask api url
// send json package to flask app
function SendToMonitor($request, $data_array)
{
    global $monitorURL, $monitorTimeout;
    global $loggerversion;

    // 1. Set general options, that will stay the same for all events in the package.
    $ch = curl_init('https://'.$monitorURL.'/log/event');
    curl_setopt($ch, CURLOPT_PORT, 443);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPackage);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 50); // DEPLOYMENT CHANGE
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, $monitorTimeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // 2. Perform forwarding loop - if we decide to split here. For now, just foward the whole damn thing.
    // syslog(LOG_NOTICE, 'Sending packets to monitor API at '.$monitorURL.' : ' . $jsonPackage );
    $start_time_milliseconds = round(microtime(true) * 1000);
    // syslog(LOG_NOTICE, "Sending data to monitor, beforeTime:".$start_time_milliseconds);
    // error_log("Repeat message with error_log: Sending data to monitor, beforeTime:".$start_time_milliseconds);
    $jsonPackage = combineParamsAndBody($request, $data_array);
    $jsonPackage["ogd_logger_version"] = $loggerversion;
    $headers = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonPackage)
    );
    $response = curl_exec($ch);
    // check for cURL errors
    if (curl_errno($ch)) {
        error_log( 'cURL error when attempting to communicate with Monitor API: ' . curl_error($ch) );
    }
    // } else {
        // TODO : Comment this out once we have the thing working.
        // syslog(LOG_NOTICE, 'Response from Monitor API: ' . $response );
    // }

    // 3. Clean up 
    // close cURL session
    curl_close($ch);
    // syslog(LOG_NOTICE, "Sent data to monitor, timedelta:".($end_time_milliseconds - $start_time_milliseconds));
    // error_log("Repeat message with error_log: Sent data to monitor, timedelta:".($end_time_milliseconds - $start_time_milliseconds));
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
    // unset($ret_val["data"]); // contains long string of encoded data
    unset($ret_val["remote_addr"]);
    unset($ret_val["http_user_agent"]);

    return json_encode($ret_val);
}
?>