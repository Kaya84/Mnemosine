<?php

session_start();

require_once("config.php");
require_once("funzioni.php");

//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_SESSION['name'])){
			header('Location: login.php');
		die();
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


//Verifico ci sia il campo id nell'url altrimenti rimando al login:


//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_GET['id'])){
		header('Location: login.php');
		die();
}

$error = null;

// verifico se e' stato selezionato il flag "Elimina password corrente"
if (isset($_POST["checkElimina"])){
	cancellapassword($_GET['id']);
	header('Location: index.php');
	die();
}


if (isset($_POST["passwordId"])){

	$userName = $_POST["userName"];
	$password = $_POST["password"];
	$passwordId = $_POST["passwordId"];
	$url = $_POST["url"];
	$note = $_POST["note"];
	//Effettuo la pulizia dell'url con l'aggiunta di http o https
	if  ( $ret = parse_url($url) ) {

		  if ( !isset($ret["scheme"]) )
		   {
		   $url = "http://" . $url;
		   }
	}


	//Recupero la chiave pubblica dell'utente corrente e provvedo a usarla per cifrare la password
	$pubKey = $_SESSION["pubkey"];
	$encriptedPassword = null;
	openssl_public_encrypt($password, $encriptedPassword, $pubKey);

	//Inserisco i dati nel database
	$r = $database->update("password", [
		"ownerId" => $_SESSION["userId"],
		"username" => $userName,
		"encPassword" => $encriptedPassword,
		"url" => $url,
		"note" => $note
	], ["id" => $passwordId]);
	if ($r->rowCount() == 0){
			$error = $database->error();
	} else {

	}

	//Provvedo ora ad aggiornare la password per ognuno di quelli a cui è stata condivisa la password
	$res = $database->select("share", "*", ["passwordId" => $passwordId]);

	foreach ($res as $r){
		//Recupero la chiave pubblica di ogni utente con il quale la password è stata condivisa
		$userInfo = $database->get("user_login", ["pubkey", "notifyOnUpdate", "email", "full_name" ], ["id" => $r['userId']]);
		$ec = null;
		openssl_public_encrypt($password, $ec, $userInfo['pubkey']);

		$database->update("share", ["encPassword" => $ec], ["id" => $r['id']]);
		//verifico se vuole la notifica a mezzo mail dell'update
		if ($userInfo['notifyOnUpdate'] == "1"){

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

				$mail->addAddress($userInfo['email'], $userInfo['full_name']);     // TODO: Recuperare il destinatario corretto dal db
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
				$mail->Subject = 'Here is the subject';
				$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
				$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

				$mail->send();
			} catch (Exception $e) {
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			}
		}
	}
}

//Recupero i dati dal database (Si, la query rigira anche se ho appena fatto l'update.. si può migliorare ma al momento va bene così)
$res = $database->get("password", "*", [ "id" => $_GET['id']]);

//estraggo la password cifrata
$decrypted = null;
openssl_private_decrypt($res['encPassword'], $decrypted, $_SESSION['privkey']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
<title>Mnemosine - Gestione Password </title>
<!-- JQUERY -->
<script type="text/javascript" language="javascript" src="jquery/jquery.js"></script>
<!-- bootstrap-3.3.7 -->
<link rel="stylesheet" href="bootstrap-3.3.7/css/bootstrap.min.css">
<script src="bootstrap-3.3.7/js/bootstrap.min.js"></script>


</head>

<body> 
<?php

include ("includes/menu.php");
?>

<div class="container">

<h2>Modifica password</h2>
<br>
<form method='post' action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>?id=<?php echo $_GET['id']?>'>
  <div class="form-row">
	    <label for="exampleFormControlTextarea1">Descrizione</label>
    	<textarea class="form-control" id="exampleFormControlTextarea1" rows="3" name='note' ><?php echo $res['note'] ?></textarea>
  </div>
  <div class="form-row">
  	<div class="form-group">
    	<label for="inputEmail4">Nome utente</label>
	    <input type="text" class="form-control" id="inputEmail4" placeholder="Nome utente del sito" name='userName' value='<?php echo $res['username'] ?>'>
	</div>
	<div class="form-group">
    	<label for="inputPassword4">Password</label>
	    <input type="text" class="form-control" id="inputPassword4" placeholder="Password" name='password' value='<?php echo $decrypted ?>'>
	</div>
  </div>
  <div class="form-row">
  	<div class="form-group">
	    <label for="inputAddress">URL</label>
    	<input type="text" class="form-control" id="inputAddress" placeholder="https://" name='url' value='<?php echo $res['url'] ?>'>
	  </div>
  </div>
  <div class="form-check">
    <input type="checkbox" class="form-check-input" id="checkElimina" name="checkElimina">
    <label class="form-check-label" for="checkElimina">Elimina la password corrente</label>
  </div>
  <div class="form-row">
	</br>
	<input type='hidden' name='passwordId' value='<?php echo $_GET['id']?>'>
	<button type="submit" class="btn btn-primary">Aggiorna</button>
	<a class="btn btn-success" href="/index.php" role="button">Chiudi</a>
  </div>
</form>


<br>
</div>
<?php
if ($debug){
	echo "<pre>" . $_SESSION['privkey'] . "</pre>";
}
?>
</body>
</html>


