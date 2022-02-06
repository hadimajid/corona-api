<?php 

require __DIR__.'/database.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');


$data = json_decode(file_get_contents('php://input'), true);

$response = [];


if($_SERVER['REQUEST_METHOD'] == "POST"){
	
	
	
	switch ($data['type']) {
		case 'get':
			if(!empty($data['id'])){
				$id = mysqli_real_escape_string($con, $data['id']);
				$query = mysqli_query($con,"SELECT * FROM testtypes WHERE id = '$id'");
				if(mysqli_num_rows($query)>0){
					$response['status'] = 'success';
					$response['result'] = mysqli_fetch_array($query);
				}else{
					$response['status'] = 'error';
					$response['message'] = 'Record not found.';
				}
			}else{
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
			break;
		default:
			$response['status'] = 'error';
			$response['message'] = 'Invalid request type. Currently received'.$data['type'];
	}
}else{
	$response['status'] = 'error';
	$response['message'] = 'Invalid request method';
}

//$response['request'] = $data;

echo json_encode($response);