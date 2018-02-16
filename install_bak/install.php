<?php 
//Tool di installazione di Mnemosine
session_start();
$debug= 0;

/*
include "config.php";
include "db_con.php";
$errorMsg = null;
$registrationOk = false;

//Verifico se è ammessa la registrazione
if (!$isRegistrationEnabled ){
	//Rimando al login
	header('Location: index.php');
	die();
}


//Verifico se arrivano dei dati in post, vuol dire che è stata richiesta una registrazione
if (isset($_POST['requestRegistration'])){
	//TODO: Verificare prima che l'indirizzo mail non sia già presente
	//Genero la coppia di chiavi pubblica/privata
	$config = array(
		"digest_alg" => "sha512",
		"private_key_bits" => 4096,
		"private_key_type" => OPENSSL_KEYTYPE_RSA,
	);
	   
	// Create the private and public key
	$res = null;
	$res = openssl_pkey_new($config);
	if ($useStrongSecurity == 0){
		// Extract the private key from $res to $privKey
		openssl_pkey_export($res, $privKey);
	} else {
		// Extract the private key from $res to $privKey
		openssl_pkey_export($res, $privKey, $_POST['password']);
	}

	// Extract the public key from $res to $pubKey
	$pubKey = openssl_pkey_get_details($res);
	$pubKey = $pubKey["key"];
	
	//inserisco i dati nel db
	$database->insert("user_login",
			[ 
				"full_name" => $_POST['name'],
				"email" => $_POST['email'],
				// "username" => $_POST['username'],
				"password" => password_hash($_POST['password'], PASSWORD_DEFAULT),
				"privkey" => $privKey,
				"pubkey" => $pubKey
				
			]);
	
	// $errorMsg = "Inserito con " . $database->id();
	
	//Recupero l'id di inserimento
	$account_id = $database->id();
	if ($account_id != 0){
	$registrationOk = true;	
	}
	//Todo: Gestire eventuali errori di inserimento
	//Todo: gestire mail di conferma registrazione
}*/

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Mnemosine - Gestione Password </title>


</head>

	<body>
	<h1>Installazione:</h1>
	<form method='post'>
Nome DB: <input type='text' name = 'dbName'> <br>
Utente DB: <input type='text' name = 'userName'> <br>
Password DB: <input type='text' name = 'dbPassword'> <br>
URL DB: <input type='text' name = 'dbUrl'> <br>

<input type='submit' value='submit'>



	</form>
	
</body>
</html>