<?php
require __DIR__ . '/database.php';
require 'helper/helper.php';

//include 'mailer/email.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input') , true);
$response = [];

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if (empty($data['type'])) {
        $response['status'] = 'error';
        $response['message'] = 'Required information missing or wrong request type.';
        echo json_encode($response);
        return;
    }
    switch ($data['type']) {
        case 'sendEmailToCustomer':
            if (!empty($data['test_id'])) {

                $test_id = mysqli_real_escape_string($con, $data['test_id']);
				
                //Get test
                $test_query = mysqli_query($con, "SELECT * FROM tests WHERE id = '$test_id'");
                $test_sql_result =  mysqli_num_rows($test_query);
				
                if ($test_sql_result == 0) {
                    $response['status'] = 'error';
                    $response['message'] = 'No test found.';
                    echo json_encode($response);
                    return;
                }
								
				$fetch_test = mysqli_fetch_array($test_query);
				
                //Get Filepath
                $url_path_query = mysqli_query($con, "SELECT url_pdf FROM tests WHERE id = '$test_id'");
                $url_path_sql_result =  mysqli_num_rows($url_path_query);

                if ($url_path_sql_result == 0) {
                    $response['status'] = 'error';
                    $response['message'] = 'No url path found.';
                    echo json_encode($response);
                    return;
                }
				
				$url_pdf = mysqli_fetch_array($url_path_query);
				$file_path =  explode("api/", $url_pdf[0])[1];
				
                //Get customer
                $customer_query = mysqli_query($con, "SELECT * FROM customers WHERE id = '$fetch_test[1]'");
                $customer_sql_result =  mysqli_num_rows($customer_query);
				
                if ($customer_sql_result == 0) {
                    $response['status'] = 'error';
                    $response['message'] = 'No customer found.';
                    echo json_encode($response);
                    return;
                }
								
				$fetch_customer = mysqli_fetch_array($customer_query);

                //Customer
                $subject_customer = "Corona Schnelltest Ergebnis";
                $msg_customer = emailMsgForCustomer($fetch_customer[1],$fetch_customer[2]);
				
                $statusEmail = sendEmail($subject_customer, $fetch_customer[8], $file_path, $msg_customer);
			
                if ($statusEmail) {
                    $response['status'] = 'success';
                    $response['message'] = 'Email sent';
					echo json_encode($response);
                    return;
                }
                else {
                    $response['status'] = 'failed';
                    $response['message'] = 'Email not sent';
					echo json_encode($response);
                    return;
                }
            }
        break;
		default:
			$response['status'] = 'error';
			$response['message'] = 'Invalid request type. Currently received' . $data['type'];
    }
}


