<?php
require_once("config.php");

if (empty($_GET['guid'])){
	
	die("ERROR: no GUID set");
}


$guid = $_GET['guid'];
//Ricerco il dato nel DB ma solo se la richiesta di attivazione è stata fatta nelle 24 ore precedenti (per evitare brute force)
$res = $database->get("activation", "*", [ "AND" => [ "guid" => $guid, "date[>]" => date('Y-m-d H:i:s', strtotime("-1 days")) ]]);
if (!$res){
	
	die ("ERROR: no guid in DB");
} else {
	//Ok il guid c'è. Provvedo con l'attivazione dell'utente impostando il flag a 1
	$database->update("user_login", ["isActive" => 1], ["id" => $res['userId']]);
	$database->delete("activation",[ "guid" => $guid ]);
echo '	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="refresh" content="5;url=index.php">
<title>Mnemosine - Gestione Password </title>
</head>

<body>';



	echo "Utente attivato. Attendere per il redirect oppure <a href='index.php'>click qua</a> " ;
	
	echo "</body></html>";
	
	
}



?>