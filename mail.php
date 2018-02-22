<?php
use PHPMailer\PHPMailer\PHPMailer;
//faccio un check se config.php è stato caricato per scrupolo
if (!defined("CONFIG_LOADED")){
	require_once( "config.php)";
}

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function mnemosineSendMail($mailTo, $nameTo, $subject, $rawText, $htmlText){
	global $SMTPDebug;
	global $configMailFrom;
	global $configMailSmtp;

	// echo "AaaaaaaaaaaaaaaaSS";
	// echo "configMailFrom $configMailFrom";
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	try {
		//Server settings
		$mail->SMTPDebug = $SMTPDebug;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = $configMailSmtp;  					  // Specify main and backup SMTP servers
		$mail->SMTPAuth = false;                               // Enable SMTP authentication
		$mail->Username = $configMailFrom ;                   // SMTP username
		//$mail->Password = 'secret';                           // SMTP password
		//$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		//$mail->Port = 587;                                    // TCP port to connect to
		$mail->SMTPSecure = false;
		$mail->SMTPAutoTLS = false;
		//Recipients
		$mail->setFrom($configMailFrom, 'Gestore Password');
		$mail->addAddress($mailTo, $nameTo);    
		// $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
		// $mail->addAddress('ellen@example.com');               // Name is optional
		$mail->addReplyTo($configMailFrom, 'Gestore password');
		// $mail->addCC('cc@example.com');
		// $mail->addBCC('bcc@example.com');

		//Attachments
		// $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->setLanguage('it');
		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject; //Todo: migliorare il testo
		$mail->Body    = $htmlText;
		$mail->AltBody = $rawText;

		$mail->send();
		// echo 'Message has been sent';
	} catch (Exception $e) {
		echo 'Message could not be sent.';
		echo 'Mailer Error: ' . $mail->ErrorInfo;
	}

}

?>