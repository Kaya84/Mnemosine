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

//TODO: verificare che l'id della password che si vuole condividere appartenga al proprietario



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
<title>Bootstrap Login</title>
<!-- JQUERY -->
<!--<script type="text/javascript" language="javascript" src="jquery/jquery.js"></script> /-->

<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-1.12.4.js"></script>

<!-- REf: https://datatables.net/examples/styling/bootstrap.html /-->
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js"></script>

	
	<!-- bootstrap-3.3.7 -->
<link rel="stylesheet" href="bootstrap-3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css">
<script src="bootstrap-3.3.7/js/bootstrap.min.js"></script>
<script src="js/typeahead.bundle.js"></script>




		
</head>

<body>
<?php

include ("includes/menu.php");
?>

<div class="container">

<h2>Condivisione password</h2>
<br>

<?php
//Recuperto la password

$res = $database->get("password", "*", ["id" => $_GET['id']]);
// var_dump($res);
?>
Password per
<?php
$decrypted = null;
	openssl_private_decrypt($res['encPassword'], $decrypted, $_SESSION['privkey']);

echo "<ul>";
echo "<li>" . $res["username"] . "</li>";
echo "<li>" . $decrypted. "</li>";
echo "<li>" . $res["url"] . "</li>";
echo "</ul>";

//Versione alpha 1.0
// 
// Recupero con una join tutti gli utenti intersecandoli con la tabella delle condivisioni
// Se ce la condivisione l'utente è già stato aggiunto

// SELECT * FROM share WHERE passwordId = $_GET['id'];

//Facciamola easy per ora
//Estraggo tutti gli utenti che HANNO la condivisione della password
$res = $database->select("user_login", 
						["[>]share" => ["id" => "userId" ]],
						"*", ["share.passwordId" => $_GET['id'] ]);

echo "<table class='table table-striped' id='table_1'>
  <thead>
    <tr>
      <th scope=\"col\">#</th>
      <th scope=\"col\">username</th>
      <th scope=\"col\">Azioni</th>
    </tr>
  </thead>
  <tbody>";
foreach ($res as $r){
	
	
	  echo "<tr id='sharedTable_" . $r['id'] ."'>" . PHP_EOL;
    echo  "<th scope='row'>" .$r['id']. "</th>" . PHP_EOL;
    
	
	// echo  "<td>" .$r['url'] ."</td>" . PHP_EOL;
    echo   "<td>" .$r['full_name'] ."</td>" . PHP_EOL;
    // echo  " <td>" .$decrypted ."</td>" . PHP_EOL;
    echo  " <td><a href='#' class='doShare' action='delete' id='" . $r['id']."'><span class='glyphicon glyphicon-remove'></span>  Elimina condivisione</a></td>" . PHP_EOL;
    echo "</tr>" . PHP_EOL;
	
	
	// var_dump($r['username']);
}

echo "  </tbody>
</table>";
//ora tiro fuori tutti gli utenti che mancano 
echo "<br><br>";
$query = "SELECT * FROM user_login WHERE id NOT IN ( SELECT user_login.id FROM user_login LEFT JOIN share ON user_login.id = share.userId WHERE share.passwordId = :id ) "; //TODO: Fix l'sql injection
$sth = $database->pdo->prepare($query);
$sth->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
$res = $sth->execute();
// $res = $database->query($query)->fetchAll();

echo "<table class='table table-striped ' id='table_2'>
  <thead>
    <tr>
      <th scope=\"col\">#</th>
      <th scope=\"col\">username</th>
      <th scope=\"col\">Azioni</th>
    </tr>
  </thead>
  <tbody>";
foreach ($sth as $r){
	
	
	  echo "<tr id='newShareTable_" . $r['id'] ."'>" . PHP_EOL;
    echo  "<th scope='row'>" .$r['id']. "</th>" . PHP_EOL;
    
    echo   "<td>" .$r['full_name'] ." ( " . $r['email'] ." )</td>" . PHP_EOL;
    echo  " <td><a href='#' class='doShare' action='share' passwordId='" .$_GET['id']. "' userId='" . $r['id'] ."' ><span class='glyphicon glyphicon-share'></span>  Condividi</a></td>" . PHP_EOL;
    echo "</tr>" . PHP_EOL;
	
	
	// var_dump($r['username']);
}

echo "  </tbody>
</table>"; 
 
 
// var_dump($sth);

// $res = $database->debug()->select("password", 
						// ["[>]share" => ["ownerId" => "userId" ]],
						// "*",
						// ["password.id" => $_GET["id"]]);
						// var_dump($res);


// $res = $database->select("share", "*", [ "passwordId" => $_GET['id']]);

// foreach ($res as $r){
	// decifro la password per questo salvataggio
	// $decrypted = null;

	  // echo "<tr>" . PHP_EOL;
     // echo  "<th scope='row'>" .$r['id']. "</th>" . PHP_EOL;
     // echo  "<td>" .$r['url'] ."</td>" . PHP_EOL;
    // echo   "<td>" .$r['username'] ."</td>" . PHP_EOL;
    // echo  " <td>" .$decrypted ."</td>" . PHP_EOL;
    // echo  " <td><a href='share.php?id=" .$r['id']. "' class=''><span class='glyphicon glyphicon-share'></span>  </a></td>" . PHP_EOL;
    // echo "</tr>" . PHP_EOL;
	
// }
//</table>


// <div id="remote">
  // <input class="typeahead" type="text" placeholder="Oscar winners for Best Picture">
// </div>

?>
<br>
</div>
<?php
if ($debug){
	
	
	echo "<pre>" . $_SESSION['privkey'] . "</pre>";
}
?>

<script>
$( document ).ready(function() {
	$( "a.doShare" ).on( "click", function() {
		if ( $(this).attr("action") == "delete"){
			$.get( "doShare.php", { 
					id: $(this).attr("id"), 
					action : "delete"
					} ).done(function( data ) {
						alert( "Data Loaded: " + data );
				  });
			
		} else if ( $(this).attr("action") == "share"){
			$.ajax({ 
				type: 'GET', 
				url: 'doShare.php', 
				data: { 
					userId: $(this).attr("userId"),
					passwordId: $(this).attr("passwordId"),
					action : "share" }, 
				dataType: 'json',
				success: function (data) { 
					if (data.res == 0){
							alert("condivisione fatta");
						} else {
							alert("speta che non entro" + data.msg);
						}
				}
			});
					
			
			// $.get( "doShare.php", { 
					// passwordId: $(this).attr("passwordId"), 
					// userId: $(this).attr("userId"),
					// action : "share"
					// } , "json").done(function( data ) {
						// if (data.res == 0){
							// alert("condivisione fatta");
						// } else {
							// alert("speta che non entro" + data.msg);
						// }
				  // });
				  
		} else {
			alert ("Oh-oh...");
		}
	  // alert( "Goodbye!" + $(this).attr("passwordId") + $(this).attr("userId")); // jQuery 1.3+
	});
});
</script>
</body>
</html>

