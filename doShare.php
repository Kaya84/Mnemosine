<?php 
include "config.php";
session_start();
$debug= 0;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_SESSION['name'])){
			header('Location: login.php');
		die();
		
}

//Il campo action definisce la tipologia di azione. Possibili valori sono
//share -> effettua la condivisione con l'insert
//delete -> elimina la condivisione rimuovendo dalla tabella share l'id in questione

if (isset($_GET['action'])){
	$action = $_GET['action'];
} else {
	die("Error: action not performed");
}

switch ($action){
	
	case "share" :
		//TODO: verificare che l'id della password che si vuole condividere appartenga al proprietario
		
		//Recupero la password salvata dal db
		$r = $database->get("password", "*", ["id" => $_GET['passwordId']]);
		
		//Generazione del messaggio per la mail
		$username = $r['username'];
		$url = $r['url'];
		$messaggio = "L'utente $username ha condiviso con te delle credenziali per il sito web " . $url . "\nAccedi alla piattaforma per i dettagli";
		
		//Recupero credenziali dal db
		$decrypted = null;
		$k = openssl_private_decrypt($r['encPassword'], $decrypted, $_SESSION['privkey']);		
		//adesso recupero la chiave pubblica dell'utente con cui voglio condividere la password
		$r = $database->get("user_login", ["pubkey", "notifyOnShare"],  ["id" => $_GET['userId']]);
		// var_dump($r);die();
		$encriptedPassword = null;
		openssl_public_encrypt($decrypted, $encriptedPassword, $r['pubkey']);
		$database->insert("share", 
							[
							"passwordId" => $_GET['passwordId'],
							"userId" => $_GET['userId'],
							"encPassword" => $encriptedPassword
							] );
		$db_id = $database->id();
		
		//Verifico se l'utente vuole la notifica della password 
			$newUserToShare = $database->get("user_login", "*", ["id" => $_GET['userId']]);
			if ($newUserToShare['notifyOnShare'] == 1){
				//Ok l'utente vuole la notifica per mail, richiamo la funzione
				require 'PHPMailer/src/Exception.php';
				require 'PHPMailer/src/PHPMailer.php';
				require 'PHPMailer/src/SMTP.php';
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
					$mail->addAddress($newUserToShare['email'], $newUserToShare['full_name']);    
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
					$mail->Subject = 'Nuova password condivisa con te'; //Todo: migliorare il testo
					$mail->Body    = $messaggio;
					$mail->AltBody = $messaggio;

					$mail->send();
					// echo 'Message has been sent';
				} catch (Exception $e) {
					echo 'Message could not be sent.';
					echo 'Mailer Error: ' . $mail->ErrorInfo;
				}
					
					
			}
		echo json_encode(["res" => 0, "msg" => "Condivisione creata correttamente"]);	
	break;
	case "delete":
		$data = $database->delete("share", ["id" => $_GET['id']]);
		if ($data->rowCount() == 1){
			echo json_encode(["res" => 0, "msg" => "Condivisione rimossa"]);		
		} else {
			echo json_encode(["res" => 1, "msg" => "Errore nell'eliminazione"]);		
		}

	break;
	default:
			echo json_encode(["res" => 1, "msg" => "Comando sconosciuto"]);		
			die();
	break;
	
}





?>
