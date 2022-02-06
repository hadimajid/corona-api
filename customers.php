<?php
require_once 'jwt/src/BeforeValidException.php';
require_once 'jwt/src/ExpiredException.php';
require_once 'jwt/src/SignatureInvalidException.php';
require_once 'jwt/src/JWT.php';
require __DIR__ . '/database.php';
require __DIR__.'/log.php';

use \Firebase\JWT\JWT;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$data = json_decode(file_get_contents('php://input') , true);
$response = [];

//Token
$jwt="";
if(isset($_SERVER['HTTP_AUTHORIZATION']))
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $arr = explode(" ", $authHeader);

    if($arr[1]){
        $jwt = $arr[1];
    }
}



if ($_SERVER['REQUEST_METHOD'] == "POST") {
	
    switch ($data['type']) {
        case 'getAll':
            if (!empty($jwt)) {
                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array(
                            'HS256'
                        ));

                        // Access is granted.
                        $query = mysqli_query($con, "SELECT * FROM customers");
                        if (mysqli_num_rows($query) > 0) {
                            $response['status'] = 'success';
                            $response['result'] = mysqli_fetch_all($query,1);
                        }
                        else {
                            $response['status'] = 'error';
                            $response['message'] = 'Record not found.';
                        }
                    }
                    catch(Exception $e) {

                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }

            }
            else {
                http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is missing';
            }
            break;
        case 'get':
            if (!empty($jwt)) {
                if (!empty($data['id'])) {
                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array(
                            'HS256'
                        ));

                        // Access is granted.
                        $id = mysqli_real_escape_string($con, $data['id']);
                        $query = mysqli_query($con, "SELECT * FROM customers WHERE id = '$id'");
                        if (mysqli_num_rows($query) > 0) {
                            $response['status'] = 'success';
                            $response['result'] = mysqli_fetch_array($query);
                        }
                        else {
                            $response['status'] = 'error';
                            $response['message'] = 'Record not found.';
                        }
                    }
                    catch(Exception $e) {

                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }
                }
                else {
                    $response['status'] = 'error';
                    $response['message'] = 'Required information missing';
                }
            }
            else {
				http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is missing';
            }
        break;
        case 'getCustomerByEmail':
            if (!empty($data['email']) && !empty($data['birthyear'])) {
                $email = mysqli_real_escape_string($con, $data['email']);
                $birthyear = mysqli_real_escape_string($con, $data['birthyear']);

                $select_customer = mysqli_query($con, "SELECT * FROM customers WHERE email = '$email'");

                if (mysqli_num_rows($select_customer) > 0) {
                    $fetch_customer = mysqli_fetch_array($select_customer);
                    $date = DateTime::createFromFormat("Y-m-d", $fetch_customer['birthday']);
                    $user_id = $fetch_customer['id'];
                    $year = $date->format("Y");

                    if ($birthyear == $year) {
                        $response['result'] = $fetch_customer;
                        $response['status'] = 'success';
                    }
                    else {
                        $response['status'] = 'error';
                        $response['message'] = 'Information doesn\'t match with any users.';
                    }
                }
                else {
                    $response['status'] = 'error';
                    $response['message'] = 'Information doesn\'t match with any users.';
                }
            }
            else {
                $response['status'] = 'error';
                $response['message'] = 'Required information missing';
            }
        break;
        case 'create':
            if (!empty($data['first_name']) && !empty($data['last_name']) && !empty($data['email']) && !empty($data['birthday']) && !empty($data['street']) && !empty($data['nr']) && !empty($data['zip']) && !empty($data['city'])) {
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

                $check_email = mysqli_query($con, "SELECT * FROM customers WHERE email = '$email'");

                if (mysqli_num_rows($check_email) > 0) {
                    $response['status'] = 'error';
                    $response['message'] = 'User with this email already exits!';
                }
                else {
                    $insert_customer_query = "INSERT INTO customers(first_name,last_name,birthday,street,nr,zip,city,email,password,number, terms) VALUES('$first_name','$last_name','$birthday','$street','$nr','$zip','$city','$email','$password','$number', 1)";

                    if (mysqli_query($con, $insert_customer_query)) {
                        $response['status'] = 'success';
                        $response['message'] = 'Inserted Successfully';
                        $response['customer_id'] = mysqli_insert_id($con);
                    }
                    else {
                        $response['status'] = 'error';
                        $response['message'] = 'Failed to create customer. ' . $insert_customer_query;
                    }
                }
            }
            else {
                $response['status'] = 'error';
                $response['message'] = 'Required information missing';
            }
        break;
        case 'update':
            if (!empty($jwt)) {
                if (!empty($data['first_name']) && !empty($data['last_name']) && !empty($data['email']) && !empty($data['street']) && !empty($data['nr']) && !empty($data['zip']) && !empty($data['city']) && !empty($data['id'])) {

                    try {
                        $decoded = JWT::decode($jwt, $secret_key, array(
                            'HS256'
                        ));

                        // Access is granted.
                        $id = mysqli_real_escape_string($con, $data['id']);
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
                        $terms = empty($data['terms']) ? "" : mysqli_real_escape_string($con, $data['terms']);

                        $check_id = mysqli_query($con, "SELECT * FROM customers WHERE id = '$id'");

                        if (mysqli_num_rows($check_id) > 0) {
                            if (mysqli_query($con, "UPDATE customers SET first_name = '$first_name', last_name = '$last_name', email = '$email', street = '$street', nr = '$nr', city = '$city', zip='$zip', number='$number', terms='$terms' WHERE id = '$id'")) {
                                $response['status'] = 'success';
                                $response['message'] = 'Updated Successfully';
                            }
                            else {
                                $response['status'] = 'error';
                                $response['message'] = 'Failed to update data.';
                            }
                        }
                        else {
                            $response['status'] = 'error';
                            $response['message'] = 'Data incorrect';
                        }
                    }
                    catch(Exception $e) {

                        http_response_code(401);
                        $response['status'] = $e->getMessage();
                        $response['message'] = 'Access denied';
                    }
                }
                else {
                    $response['status'] = 'error';
                    $response['message'] = 'Required information missing';
                }
            }
            else {
				http_response_code(401);
                $response['status'] = 'error';
                $response['message'] = 'Token is missing';
            }

        break;
        default:
            $response['status'] = 'error';
            $response['message'] = 'Invalid request type. Currently received' . $data['type'];
    }

}
else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);

