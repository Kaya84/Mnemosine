<?php 
include "config.php";
include "db_con.php";
session_start();
$debug= 0;

//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_SESSION['name'])){
			header('Location: login.php');
		die();
		
}
header('Content-Type: application/json');
//Id della password
$id = $_GET['id'];

//Primo step elimino tutte le condivisioni di quella password
$database->delete("share", ["passwordId" => $id]);
//elimino ora la password
$database->delete("password", ["id" => $id]);

echo json_encode(["res" => 0]);





?>