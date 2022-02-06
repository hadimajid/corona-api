<?php
require_once 'jwt/src/BeforeValidException.php';
require_once 'jwt/src/ExpiredException.php';
require_once 'jwt/src/SignatureInvalidException.php';
require_once 'jwt/src/JWT.php';
require __DIR__ . '/database.php';
require 'helper/helper.php';
use \Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Connection: keep-alive');
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


if ($_SERVER['REQUEST_METHOD'] == "POST") {
	switch ($data['type']) {
		case 'create':
			//Create request	
    if(!empty($jwt)){
			if (
				!empty($data['customer_id']) &&
				!empty($data['test_types_id']) &&
				!empty($data['test_center_id'])
			) {
				$customer_id = mysqli_real_escape_string($con, $data['customer_id']);
				$test_types_id = mysqli_real_escape_string($con, $data['test_types_id']);
				$test_center_id = mysqli_real_escape_string($con, $data['test_center_id']);
				if (!empty($data['test_time']))
					$test_time = mysqli_real_escape_string($con, $data['test_time']);
				else
				$test_time = date("Y-m-d H:i:s");
//				$result = mysqli_real_escape_string($con, $data['result']);



				$insert_query = "INSERT INTO tests(
				customer_id,
				test_types_id,
				test_center_id,
				test_time
				) VALUES(
				'$customer_id',
				'$test_types_id',
				'$test_center_id',
				'$test_time'
				)";
				if (mysqli_query($con, $insert_query)) {
					$response['status'] = 'success';
					$response['message'] = 'Inserted Successfully';
					$response['test_id'] = mysqli_insert_id($con);
				} else {
					$response['status'] = 'error';
					$response['message'] = 'Failed to create data.';
				}
			} else {
                $response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
    } else {
        http_response_code(401);
        $response['status'] = 'error';
        $response['message'] = 'Token is required';
    }
			break;
		case 'createBulk':
			//Create request
            if(!empty($jwt)){
			if (
				!empty($data['customer_id']) &&
				!empty($data['test_types_id']) &&
				!empty($data['test_center_id'])
			) {
			    $test_id=[];
			    foreach ($data['customer_id'] as $c_id){
                    $customer_id = mysqli_real_escape_string($con, $c_id);
                    $test_types_id = mysqli_real_escape_string($con, $data['test_types_id']);
                    $test_center_id = mysqli_real_escape_string($con, $data['test_center_id']);
                    if (!empty($data['test_time']))
                        $test_time = mysqli_real_escape_string($con, $data['test_time']);
                    else
                        $test_time = date("Y-m-d H:i:s");



                    $insert_query = "INSERT INTO tests(
				customer_id,
				test_types_id,
				test_center_id,
				test_time
				) VALUES(
				'$customer_id',
				'$test_types_id',
				'$test_center_id',
				'$test_time'
				)";
                    if (mysqli_query($con, $insert_query)) {

                        $test_id[]=mysqli_insert_id($con);

                    }
                }
			    if(count($test_id)>0){
			        foreach ($test_id as $item){
//Get test
                        $test_query = mysqli_query($con, "SELECT * FROM tests WHERE id = '$item'");
                        $test_sql_result =  mysqli_num_rows($test_query);

                        $fetch_test = mysqli_fetch_array($test_query,1);

                        //Get Filepath
                        $url_path_query = mysqli_query($con, "SELECT url_pdf FROM tests WHERE id = '$item'");
                        $url_path_sql_result =  mysqli_num_rows($url_path_query);

                        if ($url_path_sql_result >= 0) {

                            $url_pdf = mysqli_fetch_array($url_path_query,1);
                            if($url_pdf['url_pdf']){
                                $file_path =  explode("api/", $url_pdf[0])[1];
                            }else{
                                $file_path="";
                            }
                        }

                        //Get customer
                        $customer_query = mysqli_query($con, "SELECT * FROM customers WHERE id = '{$fetch_test['customer_id']}'");
                        $customer_sql_result =  mysqli_num_rows($customer_query);

                        if ($customer_sql_result> 0) {

                            $fetch_customer = mysqli_fetch_array($customer_query);

                            //Customer
                            $subject_customer = "Corona Schnelltest Ergebnis";
                            $msg_customer = emailMsgForCustomer($fetch_customer[1],$fetch_customer[2]);
                            if(isset($data['sendEmail']) && $data['sendEmail']){
                                $statusEmail = sendEmail($subject_customer, $fetch_customer[8], $file_path, $msg_customer);
                            }
                        }

                    }
                    $response['status'] = 'success';
                    $response['message'] = 'Inserted Successfully';
                    $response['test_id'] = $test_id;
                }else{
                    $response['status'] = 'error';
                    $response['message'] = 'Failed to create data.';
                }
                }else {
                $response['status'] = 'error';
                $response['message'] = 'Required information missing';
            }
            } else {
                http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
            }
			break;
            case 'update':
                if (!empty($jwt)) {
                    if (
                        !empty($data['id']) &&
                        !empty($data['user_id'])
                    ) {
                    
                        try {
                            $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
        
                            $id = mysqli_real_escape_string($con, $data['id']);
                            $result = mysqli_real_escape_string($con, $data['result']);
                            $user_id = mysqli_real_escape_string($con, $data['user_id']);
                            if (mysqli_query($con, "UPDATE tests SET result = '$result',user_id = '$user_id' WHERE id = '$id'")) {
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
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Required information missing';
                    }
                } else {
					http_response_code(401);
                    $response['status'] = 'error';
                    $response['message'] = 'Token is required';
                }
			break;
		case 'delete':
            if (!empty($jwt)) {

                try {
                    $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

                    if (!empty($data['id'])) {
                        $id = mysqli_real_escape_string($con, $data['id']);
                        if (mysqli_query($con, "DELETE FROM tests WHERE id = '$id'")) {
                            $response['status'] = 'success';
                            $response['message'] = 'Deleted Successfully';
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Failed to delete data.';
                        }
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = 'Required information missing';
                    }

                } catch (Exception $e) {

                    http_response_code(401);
                    $response['status'] = $e->getMessage();
                    $response['message'] = 'Access denied';
                }

            } else {
				http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
            }
			break;
		case 'get':
            if (!empty($jwt)) {
                if (!empty($data['id'])) {
                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

                        
                        $id = mysqli_real_escape_string($con, $data['id']);
                        $query = mysqli_query($con, "SELECT * FROM tests WHERE id = '$id'");
                        if (mysqli_num_rows($query) > 0) {
                            $response['status'] = 'success';
                            $response['result'] = mysqli_fetch_array($query);
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'Record not found.';
                        }
                        

                    } catch (Exception $e) {

                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Required information missing';
                }

            } else {
				http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
            }

			
			break;
		case 'getTestsForTestCenter':
		 if (!empty($jwt)) {
			if (
				!empty($data['test_center_id'])
			) {
				try {
                        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
					
				$test_center_id = mysqli_real_escape_string($con, $data['test_center_id']);
				$result=null;
				if(isset($data['result'])){
                    $result = mysqli_real_escape_string($con, $data['result']);
                }
				if ($result == null) {
					$query = mysqli_query($con, "SELECT * FROM tests WHERE test_center_id = '$test_center_id' AND result IS NULL");
				} else {
					$query = mysqli_query($con, "SELECT * FROM tests WHERE test_center_id = '$test_center_id' AND result = '$result'");
				}
				
				$result =  mysqli_num_rows($query);
				
				if ($result > 0) {

					$count = 0;
					while ($raw = mysqli_fetch_array($query)) {
						$response['result'][$count] = $raw;
						$count++;
					}

					$response['status'] = 'success';
				} else {
					$response['status'] = 'error';
					$response['message'] = 'No record found.';
				}
				
				} catch (Exception $e) {

                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }
			} else {
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
		} else {
				http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is required';
        }
			break;
		case 'checkin':
			if (
				!empty($data['first_name']) &&
				!empty($data['last_name']) &&
				!empty($data['email']) &&
				!empty($data['birthday']) &&
				!empty($data['street']) &&
				!empty($data['nr']) &&
				!empty($data['zip']) &&
				!empty($data['city']) &&
				!empty($data['TestcenterId']) &&
				!empty($data['TesttypeId'])
			) {
				$first_name = mysqli_real_escape_string($con, $data['first_name']);
				$last_name = mysqli_real_escape_string($con, $data['last_name']);
				$email = mysqli_real_escape_string($con, $data['email']);
				$birthday = mysqli_real_escape_string($con, $data['birthday']);
				$street = mysqli_real_escape_string($con, $data['street']);
				$nr = mysqli_real_escape_string($con, $data['nr']);
				$zip = mysqli_real_escape_string($con, $data['zip']);
				$city = mysqli_real_escape_string($con, $data['city']);
				$password = empty($data['password']) ? "" : md5($data['password']);
				$number = empty($data['number']) ? "" : mysqli_real_escape_string($con, $data['number']);
				$TestcenterId = mysqli_real_escape_string($con, $data['TestcenterId']);
				$TesttypeId = mysqli_real_escape_string($con, $data['TesttypeId']);

				$check_email = mysqli_query($con, "SELECT * FROM customers WHERE email = '$email'");
				$valid = true;
				$user_id;
				if (mysqli_num_rows($check_email) > 0) {
					$fetch_email = mysqli_fetch_array($check_email);
					if (
						$fetch_email['first_name'] == $first_name &&
						$fetch_email['last_name'] == $last_name &&
						$fetch_email['birthday'] == $birthday &&
						$fetch_email['street'] == $street &&
						$fetch_email['nr'] == $nr &&
						$fetch_email['zip'] == $zip &&
						$fetch_email['city'] == $city
					) {
						$user_id = $fetch_email['id'];
					} else {
						$valid = false;
					}
				} else {
					$insert_customer_query = "INSERT INTO customers(first_name,last_name,birthday,street,nr,zip,city,email,password,number, terms) VALUES('$first_name','$last_name','$birthday','$street','$nr','$zip','$city','$email','$password','$number', 1)";
					if (mysqli_query($con, $insert_customer_query)) {
						$user_id = mysqli_insert_id($con);
					} else {
						$response['status'] = 'error';
						$response['message'] = 'Failed to create customer. ' . $insert_customer_query;
					}
				}


				if ($valid) {
					$now = date("Y-m-d H:i:s");
					if (mysqli_query(
						$con,
						"INSERT INTO tests(
															customer_id,
															test_types_id,
															test_center_id,
															test_time
														)VALUES(
															'$user_id',
															'$TesttypeId',
															'$TestcenterId',
															'$now'
													)"
					)) {
						$response['status'] = 'success';
						$response['test_id'] = mysqli_insert_id($con);
						$response['message'] = 'Test Created Successfully';
					} else {
						$response['status'] = 'error';
						$response['message'] = 'Invalid Data provided for tests';
					}
				} else {
					$response['status'] = 'error';
					$response['message'] = 'Email Already Exists';
				}
			} else {
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
			break;
		case 'getResult':
			if (
				!empty($data['email']) &&
				!empty($data['birthyear'])
			) {
				$email = mysqli_real_escape_string($con, $data['email']);
				$birthyear = mysqli_real_escape_string($con, $data['birthyear']);

				$select_customer = mysqli_query($con, "SELECT * FROM customers WHERE email = '$email'");

				if (mysqli_num_rows($select_customer) > 0) {
					$fetch_customer = mysqli_fetch_array($select_customer);
					$date = DateTime::createFromFormat("Y-m-d", $fetch_customer['birthday']);
					$user_id = $fetch_customer['id'];
					$year = $date->format("Y");

					if ($birthyear == $year) {
						$select_tests = mysqli_query($con, "SELECT id, test_time, test_types_id, result, customer_id FROM tests WHERE customer_id = '$user_id'");
						if (mysqli_num_rows($select_tests) > 0) {
							$count = 0;
							while ($raw = mysqli_fetch_array($select_tests)) {
								$response['result'][$count] = $raw;
								$count++;
							}
							$response['status'] = 'success';
						} else {
							$response['status'] = 'error';
							$response['message'] = 'No Tests found.';
						}
					} else {
						$response['status'] = 'error';
						$response['message'] = 'Information doesn\'t match with any users.';
					}
				} else {
					$response['status'] = 'error';
					$response['message'] = 'Information doesn\'t match with any users.';
				}
			} else {
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
			break;
		case 'GetTestBetween':

		if (!empty($jwt)) {
			
			if (
				!empty($data['date1']) &&
				!empty($data['date2'])
			) {
				$date1 = mysqli_real_escape_string($con, $data['date1']);
				$date2 = mysqli_real_escape_string($con, $data['date2']);

				try {
					$response['token']=$jwt;
					$decoded = JWT::decode($jwt, $secret_key, array('HS256'));

					$select_tests = mysqli_query($con, "SELECT tests.id as Testnummer, tests.url_pdf as PDF, customers.id as customer_id ,customers.first_name, customers.last_name, customers.email, customers.birthday, customers.terms, customers.street, customers.nr, customers.zip, customers.city, tests.result , tests.test_time, testcenter.street_name from tests INNER JOIN customers on tests.customer_id = customers.id INNER JOIN testtypes on tests.test_types_id = testtypes.id INNER JOIN testcenter on tests.test_center_id = testcenter.id WHERE test_time >= '$date1' AND test_time <= '$date2' ORDER BY tests.id DESC");

					if (mysqli_num_rows($select_tests) > 0) {

						$count = 0;
						while ($raw = mysqli_fetch_array($select_tests)) {
							$response['result'][$count] = $raw;
							$count++;
						}
						$response['status'] = 'success';
					} else {
						$response['status'] = 'error';
						$response['message'] = 'No tests found in the given range.';
					}
				} catch (Exception $e) {

					http_response_code(401);
					$response['status'] = $e->getMessage();
					$response['message'] = 'Access denied';
				}
			} else {
				$response['status'] = 'error';
				$response['message'] = 'Required information missing';
			}
			} else {
				http_response_code(401);
				$response['status'] = 'error';
				$response['message'] = 'Token is required';
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
