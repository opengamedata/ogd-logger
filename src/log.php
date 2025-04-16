<?php
header("Access-Control-Allow-Headers: Origin, Authorization, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Origin: *");

include('config.php');
include('parser.php');
include('query_generator.php');
include('monitor.php');

$RESPONSE_BASE = "ogd-logger ".($loggerVersion ?? "")." ";

# 1. Figure out what the input schema looks like, defaulting to full OGD schema.
$APP_ID = $_REQUEST["app_id"] ?? "NO APP ID";
$REQUEST_SCHEMA = schemaFromAppID($APP_ID);

# 2. Make the db connection before we go to the trouble of generating query.
$conn = mysqli_connect($servername, $username, $password, $db);
if (!$conn) {
  die($RESPONSE_BASE."FAILURE: Could not connect to the database.\n   Error message: " . mysqli_connect_error());
}

# 3. Generate the query data from raw input data.
$data = json_decode(base64_decode($_POST["data"]));
$data = dataToArray($data);

if (count($data) > 0) {
  # 4. Send the query itself. Log errors if failed.
  $query = generateQueryString($REQUEST_SCHEMA, $APP_ID, $data, $conn);
  $result = mysqli_query($conn, $query);
  if (!$result) {
    $sql_err = "Query for ".$APP_ID." failed with error: ".mysqli_error($conn);
    error_log($sql_err);
    die($RESPONSE_BASE."FAILURE: ".$sql_err);
  }
  # 5. Send event to flask monitor after sending to db
  if ($monitorEnabled) {
    SendToMonitor($_POST, $data);
    $end_time_milliseconds = round(microtime(true) * 1000);
    }
} else {
  $_msg = "Didn't perform query, data column was empty!";
  error_log(_msg);
  die($RESPONSE_BASE."FAILURE: "._msg);
}
die($RESPONSE_BASE."SUCCESS: ".$query);
?>