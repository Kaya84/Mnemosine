<?php
include "config.php";
session_start();
$debug= 0;

//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_SESSION['name'])){
			header('Location: login.php');
		die();
}


if (isset($_POST["userId"])){

	//Valorizzo i checkbox
	$notifyOnUpdate = isset($_POST['notifyOnUpdate']) ? 1 : 0;
	$notifyOnShare = isset($_POST['notifyOnShare']) ? 1 : 0;
	//Prima di tutto aggiorno COMUNQUE tutti i campi ad eccezione della password
	$database->update("user_login", 
						[
						"full_name" => $_POST['full_name'],
						"email" => $_POST['email'],
						"notifyOnUpdate" => $notifyOnUpdate,
						"notifyOnShare" => $notifyOnShare
						],
						["id" => $_POST['userId']]);

	//Verifico ora se il campo password mi arriva con qualche valorizzazione, se si avvio la procedura
	//TODO: sarebbe opportuno gestire il tutto con una transazione

	if( !empty($_POST['password'])){

		//Se uso la sicurezza avanzata, devo rigenerare la chiave pubblica/privata per l'utente

		if ($useStrongSecurity ){

			$config = array(
				"digest_alg" => "sha512",
				"private_key_bits" => 4096,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
			);

			// Create the private and public key
			$res = null;
			$res = openssl_pkey_new($config);
			// Extract the private key from $res to $privKey
			openssl_pkey_export($res, $privKey, $_POST['password']);

			// Extract the public key from $res to $pubKey
			$pubKey = openssl_pkey_get_details($res);
			$pubKey = $pubKey["key"];
			//Sicccome ha cambiato la password e ovviamente la chiave privata dipende dalla nuova password, devo aggiornare tutte le password ricevute in condivisione 
			//Prima di salvare la nuova chiave nel DB devo PRIMA AGGIORNARE TUTTE LE PASSWORD generate con la vecchia chiave
			//TODO : fare tutte la parte sopra descritta
			//[.......]

			//Recupero tutti i dati per cui l'utente ha una password condivisa
			$shares = $database->select("share", "*", ["userId" => $_SESSION['userId']]);

			foreach($shares as $share){
					//Primo step: decodifico in chiaro la password fatta con la vecchia chiave

					$decrypted = null;
					$k = openssl_private_decrypt($share['encPassword'], $decrypted, $_SESSION['privkey']);	
					//Cifro la password con la nuova chiave pubblica
					$encriptedPassword = null;
					openssl_public_encrypt($decrypted, $encriptedPassword, $pubKey);

					//Ora procedo con il salvare la nuova password cifrata	nel db
					$database->update("share", 
											[
											"encPassword" => $encriptedPassword
											],[ "id" => $share['id']]);
			}

			// Adesso che ho sistemato le password pre-esistenti, procedo ad aggiornare la chiave pubblica/privata nel db
			$database->update("user_login", 
						[ "password" => password_hash($_POST['password'], PASSWORD_DEFAULT),
							"privkey" => $privKey,
							"pubkey" => $pubKey
							],
						["id" => $_POST['userId']]);


		} else {
			//Non avendo la chiave privata che dipende dalla password, posso fare solo l'aggiornamento della password
			$database->update("user_login", 
						[ "password" => password_hash($_POST['password'], PASSWORD_DEFAULT) ],
						["id" => $_POST['userId']]);
		}
	}

}
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

<h2>Password gestite </h2>
<br>

<?php
//Recuperto l'elenco delle password

$res = $database->get("user_login", "*", ["id" => $_SESSION['userId']]);

?>
<form method='post' action='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>'>
  <div class="form-row">
    <div class="form-group">
		<?php
		// se e' stata attivata l'autenticazione tramite LDAP non consento la modifica dell'email
		if ($autenticazioneLDAP == 0){
			echo '<label for="inputEmail4">Nome</label>';
			echo '<input type="text" class="form-control" id="inputEmail4" placeholder="" name="full_name" value="' .$res['full_name']. '">';
		}
		else {
			echo '<label for="inputEmail4">Nome</label>';
			echo '<output name="emailtesto" id="output">' .$res['full_name']. '</output>';
		}
		?>
    </div>
	<?php
	// se e' stata attivata l'autenticazione tramite LDAP non consento la modifica della password utente
	if ($autenticazioneLDAP == 0){
		echo '
		    <div class="form-group">
			<label for="inputPassword4">Password</label>
      		<input type="text" class="form-control" id="inputPassword4" placeholder="Valorizzare solo se si vuole cambiare password" name="password" value="">
		    </div>
		';
	}
	?>
  </div>
  <div class="form-group">
	<?php
	// se e' stata attivata l'autenticazione tramite LDAP non consento la modifica dell'email
	if ($autenticazioneLDAP == 0){
		echo '<label for="email">Email</label>';
		echo '<input type="text" class="form-control" id="email" placeholder="" name="email" value="' .$res['email']. '">';
	}
	else {
		echo '<label for="email">Email:</label>';
		echo '<output name="emailtesto" id="output">' .$res['email']. '</output>';
	}
	?>
  </div>
    <div class="form-group">


	<?php
	$notifyOnShare = ($res['notifyOnShare']) ? "checked" : "";
	?>
	<input type="checkbox" name="notifyOnShare" <?php echo $notifyOnShare?>>
	<label for="exampleFormControlTextarea1"> Notificami se una password viene condivisa con me </label>
  </div>
    <div class="form-group">
	<?php
	$notifyOnShare = ($res['notifyOnUpdate']) ? "checked" : "";
	?>
	<input type="checkbox" name="notifyOnUpdate" <?php echo $notifyOnShare?>>
	<label for="exampleFormControlTextarea1"> Notificami se una password condivisa viene aggiornata</label>
  </div>
  <input type='hidden' name='userId' value='<?php echo $_SESSION['userId']?>'>
  <button type="submit" class="btn btn-primary">Aggiorna</button>
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
