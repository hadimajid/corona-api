<?php
require_once 'jwt/src/BeforeValidException.php';
require_once 'jwt/src/ExpiredException.php';
require_once 'jwt/src/SignatureInvalidException.php';
require_once 'jwt/src/JWT.php';
require __DIR__ . '/database.php';

use \Firebase\JWT\JWT;

//Header
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$data = json_decode(file_get_contents('php://input') , true);
$response = [];

//Token
$jwt="";
if(isset($_SERVER['HTTP_AUTHORIZATION'])){
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $arr = explode(" ", $authHeader);

    if($arr[1]){
        $jwt = $arr[1];
    }
}


if($_SERVER['REQUEST_METHOD'] == "POST"){
	
	switch ($data['type']) {
		case 'get':
			if(!empty($data['id'])){
				$id = mysqli_real_escape_string($con, $data['id']);
				$query = 0;
				if(!empty($jwt)){
                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
                    
                        $query = mysqli_query($con,"SELECT * FROM testcenter WHERE id = '$id'");
                    
                    } catch (Exception $e) {
        
                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }

                }else{
                    $query = mysqli_query($con,"SELECT name FROM testcenter WHERE id = '$id'");
                }
				
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
        case 'getAll':
			$query = 0;
			if(!empty($jwt)){
                try {
                    $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
                    
                    $query = mysqli_query($con,"SELECT * FROM testcenter");
                    
                } catch (Exception $e) {
        
                    http_response_code(401);
                    $response['status'] = $e->getMessage();
                    $response['message'] = 'Access denied';
                }

            }else{
                $query = mysqli_query($con,"SELECT id, name, link FROM testcenter");
            }
				
			if(mysqli_num_rows($query)>0){
				$count = 0;
				while ($raw = mysqli_fetch_array($query)) {
						$response['result'][$count] = $raw;
						$count++;
					}
				$response['status'] = 'success';
			}else{
				$response['status'] = 'error';
				$response['message'] = 'Record not found.';
			}
            break;
        case 'create':
            if (!empty($jwt)) {
                if(!empty($data['name']) && !empty($data['street_name']) && !empty($data['street_number']) && !empty($data['zip']) && !empty($data['city']) && !empty($data['email']) && !empty($data['link'])){

                    $query = mysqli_query($con,"SELECT * FROM testcenter");
                        
                    $test_center_name = mysqli_real_escape_string($con, $data['name']);
                    $test_center_street_name = mysqli_real_escape_string($con, $data['street_name']);
                    $test_center_street_number = mysqli_real_escape_string($con, $data['street_number']);
                    $test_center_zip = mysqli_real_escape_string($con, $data['zip']);
                    $test_center_city = mysqli_real_escape_string($con, $data['city']);
                    $test_center_email = mysqli_real_escape_string($con, $data['email']);
                    $test_center_link = mysqli_real_escape_string($con, $data['link']);
                    
                    $insert_query = "INSERT INTO testcenter(
                    name,
                    street_name,
                    street_number,
                    zip,
                    city,
                    email,
                    link
                    ) VALUES(
                    '$test_center_name',
                    '$test_center_street_name',
                    '$test_center_zip',
                    '$test_center_city',
                    '$test_center_email',
                    '$test_center_link'
                    )";

                    if (mysqli_query($con, $insert_query)) {
                        $response['status'] = 'success';
                        $response['message'] = 'Inserted Successfully';
                        $response['test_id'] = mysqli_insert_id($con);
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Failed to create data.';
                    }
                }else{
                    $response['status'] = 'error';
                    $response['message'] = 'Required information missing';
                }            
            
            }else{
                http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
            }
            break;
        case 'update':    
            if (!empty($jwt)) {

                if(!empty($data['id']) && !empty($data['name']) && !empty($data['street_name']) && !empty($data['street_number']) && !empty($data['zip']) && !empty($data['city']) && !empty($data['email']) && !empty($data['link'])){
                
                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

                        $test_center_id = mysqli_real_escape_string($con, $data['id']);
                        $test_center_name = mysqli_real_escape_string($con, $data['name']);
                        $test_center_street_name = mysqli_real_escape_string($con, $data['street_name']);
                        $test_center_street_number = mysqli_real_escape_string($con, $data['street_number']);
                        $test_center_zip = mysqli_real_escape_string($con, $data['zip']);
                        $test_center_city = mysqli_real_escape_string($con, $data['city']);
                        $test_center_email = mysqli_real_escape_string($con, $data['email']);
                        $test_center_link = mysqli_real_escape_string($con, $data['link']);

                        $insert_query = "UPDATE testcenter SET 
                        name = '$test_center_name',
                        street_name = '$test_center_street_name',
                        street_number = '$test_center_street_number',
                        zip = '$test_center_zip',
                        city = '$test_center_city',
                        email = '$test_center_email',
                        link = '$test_center_link'";

                        if (mysqli_query($con, $insert_query)) {
                            $response['status'] = 'success';
                            $response['message'] = 'Updated Successfully';
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Failed to update data.';
                        }
                    } catch (Exception $e) {
                        
                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }
                }else{
                    $response['status'] = 'error';
                    $response['message'] = 'Required information missing';
                }
            }else{
                http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
            }
            break;
        case 'delete':
            if (!empty($jwt)) {
                if(!empty($data['id'])){
                
                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

                        $id = mysqli_real_escape_string($con, $data['id']);

                        if (mysqli_query($con, "DELETE FROM testcenter WHERE id = '$id'")) {
                            $response['status'] = 'success';
                            $response['message'] = 'Deleted Successfully';
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Failed to delete data.';
                        }

                    } catch (Exception $e) {
                            
                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }
                }else{
                    $response['status'] = 'error';
                    $response['message'] = 'Required information missing';
                }
            }else{
                http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
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