<?php
header("Access-Control-Allow-Headers: Origin, Authorization, X-Requested-With, Content-Type, Accept");
header("Access-Control-Allow-Origin: *");

include('config.php');

$conn = mysqli_connect($servername, $username, $password, $db);
if(!$conn) die("Connection failed: " . mysqli_connect_error());

//per dump
if(isset($_REQUEST["app_id"]))                $app_id                = mysqli_real_escape_string($conn,$_REQUEST["app_id"]);                       else return;
if(isset($_REQUEST["app_version"]))           $app_version           = filter_var($_REQUEST["app_version"],           FILTER_SANITIZE_NUMBER_INT); else return;
if(isset($_REQUEST["session_id"]))            $session_id            = filter_var($_REQUEST["session_id"],            FILTER_SANITIZE_NUMBER_INT); else return;
if(isset($_REQUEST["persistent_session_id"])) $persistent_session_id = filter_var($_REQUEST["persistent_session_id"], FILTER_SANITIZE_NUMBER_INT); else return;
if(isset($_REQUEST["req_id"]))                $req_id                = filter_var($_REQUEST["req_id"], FILTER_SANITIZE_NUMBER_INT); else return;
$http_user_agent = mysqli_real_escape_string($conn,$_SERVER["HTTP_USER_AGENT"]);

$query = "INSERT INTO log (".
  "app_id,".
  "app_id_fast,".
  "app_version,".
  "session_id,".
  "persistent_session_id,".
  "level,".
  "event,".
  "event_custom,".
  "event_data_simple,".
  "event_data_complex,".
  "client_time,".
  "server_time,".
  "req_id,".
  "http_user_agent".
  ") VALUES";

$data = json_decode($_POST["data"]);
if(!is_array($data)) { $d = $data; $data = array(); array_push($data,$d); }
$n_rows = count($data);
for($i = 0; $i < $n_rows; $i++)
{
  $datum = $data[$i];
  $level = 0;
  $event = "UNDEFINED";
  $event_custom = 0;
  $event_data_simple = 0;
  $event_data_complex = NULL;
  $client_time = date("M d Y H:i:s");

  if(isset($datum->level))              $level              = filter_var($datum->level,             FILTER_SANITIZE_NUMBER_INT);
  if(isset($datum->event))              $event              = mysqli_real_escape_string($conn,$datum->event);
  //optional
  if(isset($datum->event_custom))       $event_custom       = filter_var($datum->event_custom,      FILTER_SANITIZE_NUMBER_INT);
  if(isset($datum->event_data_simple))  $event_data_simple  = filter_var($datum->event_data_simple, FILTER_SANITIZE_NUMBER_INT);
  if(isset($datum->event_data_complex)) $event_data_complex = mysqli_real_escape_string($conn,$datum->event_data_complex);
  if(isset($datum->client_time))        $client_time        = mysqli_real_escape_string($conn,$datum->client_time);

  $query .=
    "(".
    "\"".$app_id."\",".
    "\"".$app_id."\",".
    "\"".$app_version."\",".
    "\"".$session_id."\",".
    "\"".$persistent_session_id."\",".
    "\"".$level."\",".
    "\"".$event."\",".
    "\"".$event_custom."\",".
    "\"".$event_data_simple."\",".
    (!is_null($event_data_complex) ? "\"".$event_data_complex."\"," : "NULL,").
    "\"".$client_time."\",".
    "CURRENT_TIMESTAMP,".
    "\"".$req_id."\",".
    "\"".$http_user_agent."\"".
    ")";
  if($i < $n_rows-1) $query .= ",";
}

if($n_rows > 0) mysqli_query($conn,$query);

echo $query;
?>
