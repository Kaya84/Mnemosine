<?php

//
// funzione utilizzata per la cancellazione di una password
//
function cancellapassword($id) {
	global $database;

	//Primo step elimino tutte le condivisioni di quella password
	$database->delete("share", ["passwordId" => $id]);

	//elimino ora la password
	$database->delete("password", ["id" => $id]);

}

//
// Funzione utilizzata per creare un nuovo utente al volo
//
function newuser($full_name,$email,$password) {
	global $database,$useStrongSecurity;

	// se l'utente e' gia' presente nel DB passo oltre !
	$res = $database->get('user_login', [
                        'id',
                        'email',
                        'full_name',
                        'privkey',
                        'pubkey',
                        ], [
                                'email' => $email
                        ]);
	if ($res['email']==$email) {
		// NON aggiungo un nuovo utente al DB in quanto già esiste !
		return 0;
	}

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
				"password" => password_hash($_POST['password'], PASSWORD_DEFAULT),
				"full_name" => $full_name,
				"privkey" => $privKey,
				"pubkey" => $pubKey,
				"email" => $email,
			]);
	//Recupero l'id di inserimento
	$account_id = $database->id();
	if ($account_id != 0){
		$registrationOk = true;	
	}
	//Todo: Gestire eventuali errori di inserimento
	//Todo: gestire mail di conferma registrazione
}
