<?php
require_once( "config.php");
session_start();
$debug= 0;
//Primo step, verifico se esiste il file install.php nella cartella install. Se è così rimando ad effettuare l'installazione.
/*
if ( file_exists("install")){
	header('Location: install/install.php');
	die();
}
*/

//Verifico che la sessione sia valorizzata, altrimenti mando al login
if(!isset($_SESSION['name'])){
			header('Location: login.php');
		die();

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
<title>Mnemosine - Gestione Password </title>
<!-- JQUERY -->
<!--<script type="text/javascript" language="javascript" src="jquery/jquery.js"></script> /-->

<script type="text/javascript" language="javascript" src="jquery/clipboard.min.js"></script>

<script type="text/javascript" language="javascript" src="jquery/jquery-1.12.4.js"></script>

<!-- REf: https://datatables.net/examples/styling/bootstrap.html /-->
<script type="text/javascript" language="javascript" src="jquery/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="jquery/dataTables.bootstrap.min.js"></script>


	<!-- bootstrap-3.3.7 -->
<link rel="stylesheet" href="bootstrap-3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
<script src="bootstrap-3.3.7/js/bootstrap.min.js"></script>
<script src="js/typeahead.bundle.js"></script>

<style>

.field-icon {
  float: right;
  margin-left: -25px;
  margin-top: -25px;
  position: relative;
  z-index: 2;
}


</style>
</head>

<body>
<?php

include ("includes/menu.php");
?>

<div class="container">

<div class="alert alert-info collapse" id="successClipboardCopy">
      <strong>Attenzione: </strong>Copia negli appunti effettuata correttamente !
</div>

<div class='text-center'><h2>Password gestite </h2></div>

<?php
//Recuperto l'elenco delle password

$res = $database->select("password", "*", ["ownerId" => $_SESSION['userId']]);
// var_dump($res);
?>

<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Descrizione</th>
      <th scope="col">URL</th>
      <th scope="col">Nome utente</th>
      <th scope="col">password</th>
      <th scope="col">Azioni</th>
    </tr>
  </thead>
  <tbody>

<?php
foreach ($res as $r){
	//decifro la password per questo salvataggio
	$decrypted = null;
	$k = null;
	if ($useStrongSecurity){
		$k = openssl_private_decrypt($r['encPassword'], $decrypted, $_SESSION['privkey']);
	} else {
		$priv = openssl_get_privatekey($_SESSION['privkey'],$_SESSION['password']);

		$k = openssl_private_decrypt($r['encPassword'], $decrypted, $priv);
	}

	echo "<tr id='row_" .$r['id']. "'>" . PHP_EOL;
    echo "<th scope='row'>" .$r['id']. "</th>" . PHP_EOL;
    echo "<td> <span class='campoNote' data-toggle='tooltip' data-placement='top' title='" . $r['note'] ."'>" .substr($r['note'], 0,50) ."</span></td>" . PHP_EOL;
    echo "<td><a href='" .$r['url'] ."'>" .$r['url'] ."</a></td>" . PHP_EOL;
    echo "<td> <span class='campoNote' data-toggle='tooltip' data-placement='top' id='username_" .$r['id']. "'>" .$r['username']. "</span>" . PHP_EOL;
    echo "<button class='btn btn-default' data-clipboard-target='#username_" .$r['id']. "' type='button' title='Copia'><i class='glyphicon glyphicon-file'></i></button></td>" . PHP_EOL;
	echo "<td>
          <span class='input-group-btn'><input type='password' class='form-control pwd' id='pwd_" . $r['id']. "' value='$decrypted' readonly>
            <button class='btn btn-default reveal' type='button' title='Mostra' ref='pwd_" .$r['id']."'><i class='glyphicon glyphicon-eye-open'></i></button>
            <button class='btn btn-default' data-clipboard-target='#pwd_" .$r['id']. "' type='button' title='Copia'><i class='glyphicon glyphicon-file'></i></button>
          </span> </td>";
	$createDate = date( 'd/m/Y H:i:s',strtotime( $r['creationDate']));
	$editDate = ($r['editDate'] == "")? "" : "Data modifica: " . date( 'd/m/Y H:i:s',strtotime( $r['editDate']));
	$infoBox = "Data Creazione: " .  $createDate  . "<br>" .  $editDate;
	echo  " <td><a href='#'><span class='campoNote glyphicon glyphicon-info-sign' data-toggle='tooltip' data-html='true' data-placement='top' title='$infoBox'></span></a> &nbsp; 
			<a href='edit.php?id=" .$r['id']. "' class=''><span class='glyphicon glyphicon-edit' title='Modifica'></span>  </a> &nbsp; 
				<a href='share.php?id=" .$r['id']. "' class=''><span class='glyphicon glyphicon-share' title='Condividi con..'></span>  </a> &nbsp; 
	</td>" . PHP_EOL;
    echo "</tr>" . PHP_EOL;
}
?>

  </tbody>
</table>

<div class='text-center'><h2>

Password condivise con te </h2>
</div>

<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">Proprietario</th>
      <th scope="col">Descrizione</th>
      <th scope="col">URL</th>
      <th scope="col">Nome utente</th>
      <th scope="col">password</th>
      <th scope="col">Azioni</th>
    </tr>
  </thead>
  <tbody>
  <?php

  $res = $database->select("share",
						[
							"[>]password" => ["share.passwordId" => "id"],
							"[>]user_login" => ["password.ownerId" => "id"]
						],
						[
							"share.id",
							"share.encPassword (encoded)",
							"user_login.full_name*",
							"password.url",
							"password.username",
							"user_login.email",
							"password.note",
							"password.creationDate",
							"password.editDate"
						], 
						[ "share.userId" => $_SESSION['userId'] ]);

foreach ($res as $r){
	//decifro la password per questo salvataggio
	$decrypted = null;
	$k = openssl_private_decrypt($r['encoded'], $decrypted, $_SESSION['privkey']);
	echo "<tr>" . PHP_EOL;
    echo  "<th scope='row'>" .$r['full_name']. "</th>" . PHP_EOL;
    echo   "<td> <span class='campoNote' data-toggle='tooltip' data-placement='top' title='" . $r['note'] ."'>" .substr($r['note'], 0,50) ."</span></td>" . PHP_EOL;
    echo  "<td><a href='" .$r['url'] ."' >" .$r['url'] ."</a></td>" . PHP_EOL;
    echo "<td> <span data-toggle='tooltip' data-placement='top' id='sh_username_" .$r['id']. "'>" .$r['username']. "</span>" . PHP_EOL;
    echo "<button class='btn btn-default' data-clipboard-target='#sh_username_" .$r['id']. "' type='button' title='Copia'><i class='glyphicon glyphicon-file'></i></button></td>" . PHP_EOL;
	echo "<td>
          <span class='input-group-btn'><input type='password' class='form-control pwd' id='pwd_sh_" . $r['id']. "' value='$decrypted' readonly>
            <button class='btn btn-default reveal' type='button' title='Mostra' ref='pwd_sh_" .$r['id']."'><i class='glyphicon glyphicon-eye-open'></i></button>
            <button class='btn btn-default' data-clipboard-target='#pwd_sh_" .$r['id']. "' type='button' title='Copia'><i class='glyphicon glyphicon-file'></i></button>
          </span> </td>";
	$createDate = date( 'd/m/Y H:i:s',strtotime( $r['creationDate']));
	$editDate = ($r['editDate'] == "")? "" : "Data modifica: " . date( 'd/m/Y H:i:s',strtotime( $r['editDate']));
	$infoBox = "Data Creazione: " .  $createDate  . "<br>" .  $editDate;
	echo  " <td><a href='#'><span class='campoNote glyphicon glyphicon-info-sign' data-toggle='tooltip' data-html='true' data-placement='top' title='$infoBox'></span></a> &nbsp; </td>";
	
    echo "</tr>" . PHP_EOL;
	
}
  ?>
  
  </tbody>
</table>
<br>
<div class='text-center'><h2>

Password condivise da te </h2>
</div>
<table class="table table-striped">
  <thead>
    <tr>
      <th scope="col">URL</th>
      <th scope="col">Nome utente</th>
      <th scope="col">Condivisa con: </th>
      
    </tr>
  </thead>
  <tbody>
  <?php

  $res = $database->select("v_sharedWith","*",[ "v_sharedWith.id" => $_SESSION['userId'] ]);

foreach ($res as $r){
 echo "<tr>" . PHP_EOL;
     echo  "<th scope='row'>" .$r['url']. "</th>" . PHP_EOL;
     echo  "<td>" .$r['username'] ."</td>" . PHP_EOL;
    echo   "<td>" .$r['full_name'] ."</td>" . PHP_EOL;
    // echo  " <td>" .$decrypted ."</td>" . PHP_EOL;
    // echo  " <td>-----</td>" . PHP_EOL;
    echo "</tr>" . PHP_EOL;
	
} 
  ?>
  
  </tbody>
</table>
</div>


<script>

function copyToClipboard(text){
    var dummy = document.createElement("input");
    document.body.appendChild(dummy);
    dummy.setAttribute('value', text);
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}

// Gestisco i pulsanti copia nella clipboard sfruttando questo plugin https://clipboardjs.com/
var clipboard = new Clipboard('.btn', {
    container: document.getElementById('modal')
});

clipboard.on('success', function(e) {
	$("#successClipboardCopy").fadeIn(1000).delay(1000).fadeOut(1000);
	// questo trucchetto lo devo usare per gestire il copia delle password che altrimenti non ne vogliono sapere di funzionare !
	copyToClipboard(e.text);
    e.clearSelection();
});

clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});


//
// gestisco tramite javascript funzioni particolari (es. mostra/nascondi password, tooltip...
//
$( document ).ready(function() {
	//Attivo i tooltip per il campo note
	$(".campoNote").tooltip();

	//Attivo il sorting e search sulle tabelle
	$('.table').DataTable( {
		"language": {
			"sEmptyTable":     "Nessun dato presente nella tabella",
		    "sInfo":           "Vista da _START_ a _END_ di _TOTAL_ elementi",
		    "sInfoEmpty":      "Vista da 0 a 0 di 0 elementi",
		    "sInfoFiltered":   "(filtrati da _MAX_ elementi totali)",
		    "sInfoPostFix":    "",
		    "sInfoThousands":  ".",
		    "sLengthMenu":     "Visualizza _MENU_ elementi",
		    "sLoadingRecords": "Caricamento...",
		    "sProcessing":     "Elaborazione...",
		    "sSearch":         "Cerca:",
		    "sZeroRecords":    "La ricerca non ha portato alcun risultato.",
		    "oPaginate": {
		        "sFirst":      "Inizio",
		        "sPrevious":   "Precedente",
		        "sNext":       "Successivo",
		        "sLast":       "Fine"
		    },
		    "oAria": {
		        "sSortAscending":  ": attiva per ordinare la colonna in ordine crescente",
		        "sSortDescending": ": attiva per ordinare la colonna in ordine decrescente"
		    }
        }
	});

	//mostra e nasconde la password	
	$(".reveal").on('click',function() {
		// alert($(this).attr('ref'));
	    var $pwd = $("#" + $(this).attr('ref'));
	    if ($pwd.attr('type') === 'password') {
	        $pwd.attr('type', 'text');
	    } else {
	        $pwd.attr('type', 'password');
	    }
	});

});

</script>
<?php
if ($debug){
	echo "<pre>" . $_SESSION['privkey'] . "</pre>";
}
?>
</body>
</html>

