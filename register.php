<?php
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
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Mnemosine - Gestione Password </title>

<!-- bootstrap-3.3.7 -->
<script type="text/javascript" language="javascript" src="jquery/jquery.js"></script>
<script src="bootstrap-3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.js"></script>
<link rel="stylesheet" href="bootstrap-3.3.7/css/bootstrap.min.css">

<!-- JQUERY -->


<link href="style/style.css" rel="stylesheet" type="text/css" media="all"/>
<script type="text/javascript" language="javascript" src="style/style.js"></script>
<style >

/*
/* Created by Filipe Pina
 * Specific styles of signin, register, component
 */
/*
 * General styles
 */

body, html{
     height: 100%;
 	background-repeat: no-repeat;
 	background-color: #d3d3d3;
 	font-family: 'Oxygen', sans-serif;
}

.main{
 	margin-top: 70px;
}

h1.title { 
	font-size: 50px;
	font-family: 'Passion One', cursive; 
	font-weight: 400; 
}

hr{
	width: 10%;
	color: #fff;
}

.form-group{
	margin-bottom: 15px;
}

label{
	margin-bottom: 15px;
}

input,
input::-webkit-input-placeholder {
    font-size: 11px;
    padding-top: 3px;
}

.main-login{
 	background-color: #fff;
    /* shadows and rounded borders */
    -moz-border-radius: 2px;
    -webkit-border-radius: 2px;
    border-radius: 2px;
    -moz-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
    -webkit-box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);
    box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.3);

}

.main-center{
 	margin-top: 30px;
 	margin: 0 auto;
 	max-width: 330px;
    padding: 40px 40px;

}

.login-button{
	margin-top: 5px;
}

.login-register{
	font-size: 11px;
	text-align: center;
}
</style>


</head>
	<body>
		<div class="container">
		<?php echo $errorMsg; ?>
		<?php
		if ($registrationOk){
			echo "<div class=\"alert alert-success\" role=\"alert\">
		  Registrazione effettuata correttamente
		</div>";
		}
		?>
			<div class="row main">
				<div class="panel-heading">
	               <div class="panel-title text-center">
	               		<h1 class="title">Gestore e password sharer</h1>
	               		<hr />
	               	</div>
	            </div> 
				<div class="main-login main-center">
					<form  data-toggle="validator" class="form-horizontal" method="post" action="#" role='form'>
						
						<div class="form-group">
							<label for="name" class="cols-sm-2 control-label">Il tuo nome (e cognome)</label>
							<div class="cols-sm-10">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-user fa" aria-hidden="true"></i></span>
									<input type="text" class="form-control" name="name" id="name"  placeholder="Enter your Name"/>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="email" class="cols-sm-2 control-label">Indirizzo e-mail</label>
							<div class="cols-sm-10">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-envelope fa" aria-hidden="true"></i></span>
									<input type="email" class="form-control" name="email" id="email"  placeholder="Enter your Email" data-error="Inserire una mail valida" required>
								</div>    <div class="help-block with-errors"></div>
							</div>
						</div>
<?php
						/*<div class="form-group">
							<label for="username" class="cols-sm-2 control-label">Username</label>
							<div class="cols-sm-10">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-users fa" aria-hidden="true"></i></span>
									<input type="text" class="form-control" name="username" id="username"  placeholder="Enter your Username"/>
								</div>
							</div>
						</div>
					*/
						?>

						<div class="form-group">
							<label for="password" class="cols-sm-2 control-label">Password</label>
							<div class="cols-sm-10">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-lock fa-lg" aria-hidden="true"></i></span>
									<input type="password" class="form-control" name="password" id="password"  placeholder="Enter your Password"/>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="confirm" class="cols-sm-2 control-label">Conferma Password</label>
							<div class="cols-sm-10">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-lock fa-lg" aria-hidden="true"></i></span>
									<input type="password" class="form-control" name="confirm" id="confirm"  placeholder="Confirm your Password"/>
								</div>
							</div>
						</div>

						<div class="form-group ">
							<button type="submit" class="btn btn-primary btn-lg btn-block login-button">Registra</button>
							<input type="hidden" name='requestRegistration' value='1'>
						</div>
						<div class="login-register">
				            <a href="index.php">Login</a>
				         </div>
					</form>
				</div>
			</div>
		</div>

	</body>
	
	

<script>
$( document ).ready(function() {
	$('.form-horizontal').validator();
});
</script>
</html>