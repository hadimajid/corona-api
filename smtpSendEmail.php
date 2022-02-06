<?php
require __DIR__ . '/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.5.3/src/Exception.php';
require 'PHPMailer-6.5.3/src/PHPMailer.php';
require 'PHPMailer-6.5.3/src/SMTP.php';


require __DIR__.'/log.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input') , true);
$response = [];



if ($_SERVER['REQUEST_METHOD'] == "POST") {
	
    switch ($data['type']) {
        case 'send':
           
               $response['res'] = sendEmail();
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

//$response['request'] = $data;
echo json_encode($response);





function sendEmail(){
	
try {
	$mail = new PHPMailer(true);
    //Server settings
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'be-checkpoint-schnelltest.com';        //Set the SMTP server to send through
    //$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    //$mail->Username   = 'info';                                 //SMTP username
    //$mail->Password   =  'info#123';                            //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;                                     //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    $mail->SMTPDebug = 2;
    //Recipients
    $mail->setFrom('info@be-checkpoint-schnelltest.com', 'Test');
    $mail->addAddress('jan.hiweno.lorenz0@googlemail.com');     //Add a recipient
    //$mail->addReplyTo('info@shinycar24.de', 'Information');
    //$mail->addCC('jan.hiweno.lorenz0@googlemail.com');
    //$mail->addBCC('jan.hiweno.lorenz0@gmail.com');

    //Attachments
    //$mail->addAttachment('reports/report_5G4PTz_119.pdf');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    return 'Message has been sent';
} catch (Exception $e) {
    return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

}

