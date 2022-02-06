<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer-6.5.3/src/Exception.php';
require './PHPMailer-6.5.3/src/PHPMailer.php';
require './PHPMailer-6.5.3/src/SMTP.php';

function dieDump($data){
    die(var_dump($data));
}
function emailMsgForCustomer($firstName,$lastName){
    $fullName=ucwords("$firstName $lastName");
    return '<!DOCTYPE html>
	<html lang="de">
	<body>
	<b>Hallo ' . $fullName . ', </b>
	<p></p>
	<p> im Anhang finden Sie eine PDF-Datei mit Ihren Ergebnis.</p> 
	<p></p>
	<p>NEU: Du kannst auch dein letztes Ergebnis jederzeit unter diesem <a title="MyResult" href="https://app.be-checkpoint-schnelltest.de/myresult">Link</a> aufrufen!</p>
	<p></p>
	<p>Wenn Sie Probleme beim öffnen der Datei haben, kontaktieren Sie bitte den Support.</p>
	<p>Email: xberg@checkpoint-schnelltest.de</p>
	<p></p>
	<p>Viele Grüße</p>
	<p>Ihre Schnellteststelle Berlin </p>
	</body>
	</html>';
}
function emailMsgForTestCenter($firstName,$lastName,$to_customer,$link){
    $fullName=ucwords("$firstName $lastName");
    return "<b> Testperson: " . $fullName . ", </b></br>
	<b> Email: " . $to_customer . ", </b> </br>
	<p> Im Anhag finden Sie die PDF-Datei</p>
	<p> Und hier der <a title='MyResult' href='" . $link . "'>Link</a></p>
  	<p>Wenn Sie Probleme beim öffnen der Datei haben, kontaktieren Sie bitte den Support. </p>";

}
function sendAllEmails($customer, $test_data, $file_path, $server_name,$test_center_email) {
    $to_customer = $customer[8];
    if(empty($test_center_email)){
        $test_center_email='jan.hiweno.lorenz0@googlemail.com';
    }

    //Customer
    $subject_customer = "Corona Schnelltest Ergebnis";
    $msg_customer = emailMsgForCustomer($customer[1],$customer[2]);

    //Testcenter
    $link = "https://be-checkpoint-schnelltest.de/data-tb/api/" . $file_path;
    $subject_test_center = "Test #" . $test_data[0] . " | " . $to_customer . "";
    $msg_test_center = emailMsgForTestCenter($customer[1],$customer[2],$to_customer,$link);

    //Email to Testcenter

    sendEmail($subject_test_center, $test_center_email, $file_path, $msg_test_center);
    sendEmail($subject_customer, $to_customer, $file_path, $msg_customer);
}

function sendEmail($subject, $to, $file_path, $msg) {

    $mail = new PHPMailer(true);

    try {
        //Server settings                     //Enable verbose debug output
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64'; //Send using SMTP
        // $mail->Host = 'be-checkpoint-schnelltest.com'; //Set the SMTP server to send through
        $mail->Host = 'smtp.gmail.com'; //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = "hadimajidtestacc@gmail.com";                     			  //SMTP username
        $mail->Password   = 'haditestacc1212';                             //SMTP password
        $mail->SMTPSecure = 'tls'; //Enable implicit TLS encryption
        $mail->Port = 587; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        //Recipients
        $mail->setFrom('info@be-checkpoint-schnelltest.com', $subject);
        $mail->addAddress($to); //Add a recipient
        $mail->addReplyTo('info@be-checkpoint-schnelltest.com', 'Checkpoint Schnelltest');

        //Attachments
        if (!empty($file_path)) {
            $mail->addAttachment($file_path); //Add attachments

        }

        //Content
        $mail->isHTML(true); //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = $msg;

        $mail->send();
        //wh_log('email.php Sent Email to: ' . $to . ' 1/1');
        return true;
    }
    catch(Exception $e) {
        //wh_log('email.php Sent Email failed to customer ' . $to . ' 1/2');
        //wh_log("Mailer Error: {$mail->ErrorInfo} 2/2");
        return false;
    }
}
function generateRandomString($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $characters_length = strlen($characters);
    $random_string = '';
    for ($i = 0; $i < $length; $i++) {
        $random_string .= $characters[rand(0, $characters_length - 1)];
    }
    return $random_string;
}

function createPDF($test_center,$customer,$test_type,$test_data,$user_data){
    //--------PDF file layout---
    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',12);


    if($test_center[0]==11){
        $pdf->Image('images/logo.jpeg',140,10,45,20);
        $pdf->Image('images/Logo_3DH_Marine.png',85,5,57,25);
    }else{
        $pdf->Image('images/logo.jpeg',140,10,50,25);
    }



// $pdf->Cell(70);
    $pdf->Cell(50,10,iconv('UTF-8', 'windows-1252',$test_center[1]) ." Corona Schnelltest");
    $pdf->SetFont('Arial','',12);
    $pdf->Ln(5);
    $pdf->Cell(50,10,ucwords(strtolower(iconv('UTF-8', 'windows-1252',$test_center[2])), '\',.- ') ." ". $test_center[3].", ". $test_center[4]." ". $test_center[5]);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Ln(15);
    $str = iconv('UTF-8', 'windows-1252', "Bescheinigung über das Vorliegen eines positiven oder negativen Antigentests zum");
    $pdf->Ln(10);
    $pdf->Cell(50,10,$str);
    $pdf->Ln(5);
    $pdf->Cell(50,10,iconv('UTF-8', 'windows-1252','Nachweis des SARS-CoV-2 Virus'));
//end page header
    $pdf->Ln(25);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0,0,iconv('UTF-8', 'windows-1252','Getestete Person:'));
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(8);
    $pdf->Cell(70,0,'Name: ');
    $pdf->Cell(80,0,ucwords(strtolower(iconv('UTF-8', 'windows-1252',$customer[1])), '\',.- ').' '.ucwords(strtolower(iconv('UTF-8', 'windows-1252',$customer[2])), '\',.- '),10,0,'c');

    $pdf->Ln(8);

    $pdf->Cell(70,0,'Anschrift:');
    $pdf->Cell(40,0,ucwords(strtolower(iconv('UTF-8', 'windows-1252',$customer[4])), '\',.- ')." ".iconv('UTF-8', 'windows-1252',$customer[5]).", ".iconv('UTF-8', 'windows-1252',$customer[6])." ".ucfirst(iconv('UTF-8', 'windows-1252',$customer[7])));
    $pdf->Ln(8);
    $pdf->Cell(70,0,'Geburtsdatum:');
    $pdf->Cell(40,0,date('d.m.Y',strtotime($customer[3])));

    $pdf->Ln(20);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0,0,iconv('UTF-8', 'windows-1252','Antigen-Schnelltest:'));
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(8);
    $pdf->Cell(70,0,iconv('UTF-8', 'windows-1252','Name des Tests:'));
    $pdf->Cell(40,0, iconv('UTF-8', 'windows-1252',$test_type[1]));


    $pdf->Ln(8);
    $pdf->Cell(70,0,iconv('UTF-8', 'windows-1252','Hersteller:'));
    $pdf->Cell(40,0, iconv('UTF-8', 'windows-1252',$test_type[4]));
    $pdf->Ln(8);
    $pdf->Cell(70,0,iconv('UTF-8', 'windows-1252','Testdatum/Testuhrzeit:'));
    $pdf->Cell(40,0, date('d.m.Y H:i:s',strtotime($test_data[5])) );
    $pdf->Ln(8);
    $pdf->Cell(70,0,iconv('UTF-8', 'windows-1252', 'Test durchgeführt durch (Namen):'));
    $pdf->Cell(40,0, iconv('UTF-8', 'windows-1252',$user_data[1]).' '. iconv('UTF-8', 'windows-1252',$user_data[2]));

    $pdf->Ln(12);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0,0,iconv('UTF-8', 'windows-1252','Testergebnis:'));
    $pdf->Ln(10);


//Result Checkbox

    if($test_data[6] == 1)
    {
        $pdf->Image('images/pcorrect.png',60,160,30,10);
        $pdf->Image('images/ncross.png',120,160,30,10);
    }
    else{

        $pdf->Image('images/pcross.png',60,160,30,10);
        $pdf->Image('images/ncorrect.png',120,160,30,10);
    }


//Stamp Img
    $pdf->Image('images/stamp_'.$test_center[0].'.png',130,175,70,20);



    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(10,45, date('d.m.Y'));

    $pdf->Image('images/sign.PNG',80,175,30,18);
    $pdf->Image('images/stt.PNG',10,195,100,10);
    $pdf->Image('images/stt2.png',10,215,185,30);
    $pdf->Ln(80);
    $pdf->SetFont('Arial', '', 11);
    $pdf->Multicell(180,5,iconv('UTF-8', 'windows-1252','*Bei einem positiven Ergebnis muss sich die Person unmittelbar in Quarantäne begeben. Dies gilt auch für Haushaltsangehörige von Personen mit einem positiven Schnelltest. Die Quarantäne darf erst beendet werden, wenn ein nachfolgender PCR-Test ein negatives Ergebnis hat. Die positiv getestete Person hat zur Bestätigung oder auch Widerlegung Anspruch auf einen PCR-Test.'));
    $pdf->AddPage();

    $pdf->Image('images/page2_1.PNG',10,20,180,170);
    $pdf->Image('images/page2_2.PNG',15,170,180,80);

//Random Name


    $random_name = generateRandomString();
    $filePath = "reports/report_".$random_name."_".$test_data[0].".pdf";
    $pdf->Output($filePath,'F');


    if($pdf){
        $response['filePath']=$filePath;
        $response['status'] = 'success';
        $response['message'] = 'PDF created';
    }
    else{
        $response['status'] = 'error';
        $response['message'] = "Couldn't create PDF";
    }
    return $response;
}