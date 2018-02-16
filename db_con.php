<?php 



include "includes/Medoo.php";

$connection = mysqli_connect($hostname,$username,$password) or die ("connection failed");
mysqli_select_db($connection  , $databasename) or die ("error connect database");


use Medoo\Medoo;
$database = new Medoo([
	// required
	'database_type' => 'mysql',
	'database_name' => 'password',
	'server' => 'sqlnew.ledro.it',
	'username' => 'password',
	'password' => 'KZWXk1saFOutqK3e',
 
 
	// [optional]
	'charset' => 'utf8',
	'port' => 3306,
 
	// [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
	'option' => [
		PDO::ATTR_CASE => PDO::CASE_NATURAL
	],
 
	// [optional] Medoo will execute those commands after connected to the database for initialization
	// 'command' => [
		// 'SET SQL_MODE=ANSI_QUOTES'
	// ]
]);
 
// $database->insert("account", [
	// "user_name" => "foo",
	// "email" => "foo@bar.com"
// ]);





?>