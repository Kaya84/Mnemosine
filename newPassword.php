<?php

session_start();
include "config.php";

// var_dump($_SESSION);

//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_SESSION['name'])){
			header('Location: login.php');
		die();
}
$error = null;
$debug= 0;
//Valorizzo a 1 se c'è l'inserimento di una nuova password
$newPassword = 0;
IF(ISSET($_SESSION['name'])){

	if (isset($_POST["check"])){

		$userName = $_POST["userName"];
		$password = $_POST["password"];
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
		// var_dump($encriptedPassword);
		// die();
		//Inserisco i dati nel database
		$r = $database->insert("password", [
			"ownerId" => $_SESSION["userId"],
			"username" => $userName,
			"encPassword" => $encriptedPassword,
			"url" => $url,
			"note" => $note
		]);
		if ($r->rowCount() == 0){
				$error = $database->error();
		} else {
			$newPassword = 1;
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
<?php
if ($newPassword){
	echo "<div class=\"alert alert-success\" role=\"alert\">
  Nuova password inserita correttamente
</div>";
}
?>
<h2>Inserisci nuova password</h2>
<br>
<form method='post'>
  <div class="form-row">
	<div class="form-group">
	    <label for="note">Descrizione</label>
	    <textarea class="form-control" id="note" rows="3" name='note'></textarea>
	</div>
  </div>
  <div class="form-row">
    <div class="form-group">
      <label for="userName">Nome utente</label>
      <input type="text" class="form-control" id="userName" placeholder="Nome utente del sito" name='userName'>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="text" class="form-control" id="password" placeholder="Password" name='password'>
    </div>
  </div>
  <div class="form-row">
	<div class="form-group">
    	<label for="inputAddress">URL</label>
	    <input type="text" class="form-control" id="inputAddress" placeholder="https://" name='url'>
	</div>
  </div>
  <input type='hidden' name='check' value='1'>
  <button type="submit" class="btn btn-primary">Memorizza</button>
  <a class="btn btn-success" href="index.php" role="button">Home</a>
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

<?php

}else{
	echo "<script language=\"javascript\">alert(\"Please login\");document.location.href='login.php';</script>";	
}
?>
