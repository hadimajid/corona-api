<?php
require_once 'jwt/src/BeforeValidException.php';
require_once 'jwt/src/ExpiredException.php';
require_once 'jwt/src/SignatureInvalidException.php';
require_once 'jwt/src/JWT.php';
require __DIR__ . '/database.php';

use \Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true);
$response = [];
$jwt = null;
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	switch ($data['type']) {
		case 'auth':
			if (
				!empty($data['email']) &&
				!empty($data['password']) &&
				!empty($data['TestcenterName'])
			) {

				//Get Data from post request
				$email = mysqli_real_escape_string($con, $data['email']);
				$password = md5($data['password']);
				$TestcenterName = mysqli_real_escape_string($con, $data['TestcenterName']);
				$select_user = mysqli_query($con, "SELECT id, role, email FROM users WHERE email = '$email' AND password = '$password'");
				
				
				//Is there a user with that data
				if (mysqli_num_rows($select_user) > 0) {
					$user_fetch = mysqli_fetch_array($select_user);
					$user_id = $user_fetch['id'];

					//Token
					$issuer_claim = $host; // this can be the servername
					$audience_claim = $host;
					$issuedat_claim = time(); // issued at
					$notbefore_claim = $issuedat_claim ; //not before in seconds
					$expire_claim = $issuedat_claim + 7200; // expire time in seconds (2Std)
					$token = array(
						"iss" => $issuer_claim,
						"aud" => $audience_claim,
						"iat" => $issuedat_claim,
						"nbf" => $notbefore_claim,
						"exp" => $expire_claim,
						"data" => array(
							"id" => $user_id,
							"email" => $email,
							"password" => $password
						)
					);

					$jwt = JWT::encode($token, $secret_key);


					//Get Testcenter
					$select_center = mysqli_query($con, "SELECT id, name, street_name, street_number, zip, city FROM testcenter WHERE name = '$TestcenterName'");
					$fetch_center = mysqli_fetch_array($select_center);
					$center_id = $fetch_center['id'];

					//Get user rights
					$select_rights = mysqli_query($con, "SELECT * FROM testcenter_users_rights WHERE test_center_id = '$center_id' AND user_id = '$user_id'");
					if (mysqli_num_rows($select_rights) > 0) {
						http_response_code(200);
						$response['status'] = 'success';
						$response['testcenter'] = $fetch_center;
						$response['user'] = $user_fetch;
						$response['token'] = $jwt;
						$response['expired_time'] = $expire_claim;
					} else {
						$response['status'] = 'error';
						$response['message'] = 'User doesn\'t have access to this test center';
					}
				} else {
					$response['status'] = 'error';
					$response['message'] = 'Incorrect email and/or password';
				}
			} else {
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
		break;
		case 'create':
            if (!empty($data['token'])) {
                $jwt = $data['token'];
			if (
				!empty($data['first_name']) &&
				!empty($data['last_name']) &&
				!empty($data['email']) &&
				!empty($data['password'])
			) {
                try {
                    $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
				$first_name = mysqli_real_escape_string($con, $data['first_name']);
				$last_name = mysqli_real_escape_string($con, $data['last_name']);
				$email = mysqli_real_escape_string($con, $data['email']);
				$password = md5($data['password']);
				if (mysqli_query($con, "INSERT INTO users(
									first_name,
									last_name,
									email,
									password
									) VALUES(
									'$first_name',
									'$last_name',
									'$email',
									'$password'
									)")) {
					$response['status'] = 'success';
					$response['message'] = 'User created successfully';
				} else {
					$response['status'] = 'error';
					$response['message'] = 'Failed to create users';
				}

                

                   
                }
                catch(Exception $e) {

                    http_response_code(401);
                    $response['status'] = $e->getMessage();
                    $response['message'] = 'Access denied';
                }
			} else {
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}

        }else {
                $response['status'] = 'error';
                $response['message'] = 'Token is missing';
            }
			break;
		default:
			$response['status'] = 'error';
			$response['message'] = 'Invalid request type. Currently received' . $data['type'];
	}
} else {
	$response['status'] = 'error';
	$response['message'] = 'Invalid request method';
}

//$response['request'] = $data;

echo json_encode($response);
