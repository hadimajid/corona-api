<?php
require __DIR__.'/database.php';
require __DIR__.'/helper/helper.php';
require __DIR__.'/log.php';
require('fpdf.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
$response = [];


if(isset($_GET) && !empty($_GET['test_id']))
{
	//getting data from backend
	//getting test
	$sql_test ="select * from tests where id=".$_GET['test_id'];
	$exec_test = mysqli_query($con, $sql_test);
	$test_data = mysqli_fetch_row($exec_test);

	if(mysqli_num_rows($exec_test) > 0){
		//getting customer
		$sql_customer ="select * from customers where id=".$test_data[1];
		$exec_customer = mysqli_query($con, $sql_customer);
		$customer = mysqli_fetch_row($exec_customer);
		if(mysqli_num_rows($exec_customer) <= 0){
			$response['status'] = 'error';
			$response['message'] = 'No customer found.';
            echo json_encode($response);
            return;
        }

		//getting testcenter
		$sql_test_center ="select * from testcenter where id=".$test_data[3];
		$exec_test_center = mysqli_query($con, $sql_test_center);
		$test_center = mysqli_fetch_row($exec_test_center);

		if(mysqli_num_rows($exec_customer) <= 0){
			$response['status'] = 'error';
			$response['message'] = 'No customer found.';
            echo json_encode($response);
            return;
		}
		
		//getting testtypes
		$sql_test_type ="select * from testtypes where id=".$test_data[2];
		$exec_test_type = mysqli_query($con, $sql_test_type);
		$test_type = mysqli_fetch_row($exec_test_type);
		
		if(mysqli_num_rows($exec_test_type) <= 0){
			$response['status'] = 'error';
			$response['message'] = 'No testtype found.';
            echo json_encode($response);
            return;
		}

		//getting users
        if($test_data[4]){
            $sql_user ="select * from users where id=".$test_data[4];
            $exec_user = mysqli_query($con, $sql_user);
            $user_data = mysqli_fetch_row($exec_user);
            if(mysqli_num_rows($exec_user) <= 0){
                $response['status'] = 'error';
                $response['message'] = 'No user found.';
                echo json_encode($response);
                return;
            }
        }else{
            $response['status'] = 'error';
            $response['message'] = 'No user found.';
            echo json_encode($response);
            return;
        }


		$response['status'] = 'success';
	    $response['message'] = 'Fetch all data';

	//Logging
	wh_log('pdf.php Fetch data 1/2');
	$msg_log = ($response['status'] == 'success' ? 'Successfully data fetch from DB' : 'Failed. '.$response['message']). " 2/2";
	wh_log($msg_log);


        $response=createPDF($test_center,$customer,$test_type,$test_data,$user_data);
        if(isset($response['filePath']) && $response['status']=='success'){
            $filePath=$response['filePath'];
            unset($response['filePath']);
        }else{
            echo json_encode($response);
            return;
        }


	//Logging
	wh_log('pdf.php Create PDF 1/3');
	$msg_log = ($response['status'] == 'success' ? 'Successfully created PDF report for customer '.$customer[0] : 'Failed. '.$response['message']). " 2/3";
	$response['status'] == 'success' ? $msg_log .= 'Path: ' .$filePath. ' 3/3' : null;
	wh_log($msg_log);



	//insert pdf filepath to database
	$url_pdf = $baseUrl ."/data-tb/api/". $filePath;
	if(mysqli_query($con,"UPDATE tests SET url_pdf = '$url_pdf' WHERE id = '$test_data[0]'")){
		$response['status'] = 'success';
		$response['message'] = 'Updated Successfully';

		//Logging
		wh_log('pdf.php Update PDF URL in Database 1/2');
		$msg_log ='Successfully update db  2/2';
		wh_log($msg_log);

	}else{
		$response['status'] = 'error';
		$response['message'] = 'Failed to update data.';
		
		//Logging
		wh_log('pdf.php Update PDF URL in Database 1/2');
		$msg_log ='Failed to update db  2/2';
		wh_log($msg_log);
	}


	//Send Emails
    $test_center_email="";
    if($test_center[6]){
        $test_center_email=$test_center[6];
    }
        sendAllEmails($customer, $test_data, $filePath, $serverName,$test_center_email);

	$response['status'] = 'success';
	//$response['result'] = $filePath;
	echo json_encode($response);




	}else{
		$response['status'] = 'error';
		$response['message'] = 'No test found.';
		echo json_encode($response);
	}

}else{
	
	$response['status'] = 'error';
	$response['message'] = 'Required information missing or wrong request type.';
	echo json_encode($response);
}

?>