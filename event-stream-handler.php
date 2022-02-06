<?php
header('Access-Control-Allow-Origin: *');
header('Connection: keep-alive');
header('Content-Type: application/json; charset=utf-8');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');



function sendMsg($id , $msg) {
  echo "id: $id" . PHP_EOL;
  echo "data: {\n";
  echo "data: \"msg\": \"$msg\", \n";
  echo "data: \"id\": $id\n";
  echo "data: }\n";
  echo PHP_EOL;
  ob_flush();
  flush();
}



if ($_SERVER['HTTP_ACCEPT'] === 'text/event-stream') {
    
	//send the Content-Type header
    header('Content-Type: text/event-stream');
    
	//its recommended to prevent caching of event data
    header('Cache-Control: no-cache');

	$startedAt = time();
	sendMsg($startedAt , time());

}



?>