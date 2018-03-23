<?php
include "config.php";
include "mail.php";
$errorMsg = null;
$registrationOk = false;

//Funzione per la verifica e la generaizone deu GUID
//REF: https://stackoverflow.com/questions/18206851/com-create-guid-function-got-error-on-server-side-but-works-fine-in-local-usin/18206984
function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }
    else {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid =             substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);            
        return $uuid;
    }
}


//Verifico se è ammessa la registrazione
if (!$isRegistrationEnabled ){
	//Rimando al login
	header('Location: index.php');
	die();
}
//Variabile tooltip sui requisiti minimi della password:

$passwordToolTip = "";
if (isset($minLengthPassword)){
	$passwordToolTip .= "Lunghezza minima: $minLengthPassword caratteri\n";
}
if (isset($useSpecialChars) && $useSpecialChars == 1){
	$passwordToolTip .= "Presenza di almeno un carattere speciale (!##$%...)\n";
}


//Verifico se arrivano dei dati in post, vuol dire che è stata richiesta una registrazione
if (isset($_POST['requestRegistration'])){
	
	//Faccio una verifica se c'è il limite a livello di dominio mail
	$emailAllowed = false;
	if ($allowedDomains != "*"){
		$elencoDomini = explode(";", $allowedDomains);//Separo i domini per punto e virgola
		$mailDomain = substr($_POST['email'], stripos($_POST['email'], "@") +1 , strlen($_POST['email'])); //Estrapolo il dominio della mail di registrazione
		foreach ($elencoDomini as $dominio){

			if ( $mailDomain == $dominio){
				$emailAllowed = true;
			}		
		}
	}

		die();
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
	//Default se l'utente è già attivo ($requestMailActivation = 0) oppure devo aspettare il login
	$isActive = $requestMailActivation == false ? 1 : 0;
	//inserisco i dati nel db
	$database->insert("user_login",
			[ 
				"full_name" => $_POST['name'],
				"email" => $_POST['email'],
				"password" => password_hash($_POST['password'], PASSWORD_DEFAULT),
				"privkey" => $privKey,
				"pubkey" => $pubKey,
				"isActive" => $isActive
				
			]);
	
	// $errorMsg = "Inserito con " . $database->id();
	
	//Recupero l'id di inserimento
	$account_id = $database->id();
	if ($account_id != 0){
		$registrationOk = true;	
	}
	//Todo: Gestire eventuali errori di inserimento
	//Todo: gestire mail di conferma registrazione
	if( $requestMailActivation){
		
		$guid = getGUID();
		$database->insert("activation",
					[
						"userId" => $account_id,
						"guid" => $guid					
					]);
					
		
		
		$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER[HTTP_HOST] . $_SERVER[CONTEXT_PREFIX] . "/index.php";
		$htmlMailText = "Per completare l'attivaizone cliccare sul link: " . $actual_link;
		
		
		
		mnemosineSendMail("kaya84@gmail.com", "kaya84@gmail.com", "Richiesta attivazione account Mnemosine", "$htmlMailText", "$htmlMailText");
					
	}
	
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
						<div class="form-group">
							<label for="password" class="cols-sm-2 control-label">Password <span class='campoNote glyphicon glyphicon-info-sign' data-toggle='tooltip' data-html='true' data-placement='top' title='<?php echo $passwordToolTip?>'></span>
							<?php
							/*
							if (isset($minLengthPassword)){
										//Gestione lunghezza minima password
										echo "Lunghezza minima= $minLengthPassword caratteri";
									}*/?>
									</label>
							<div class="cols-sm-10">
								<div class="input-group">
									<span class="input-group-addon"><i class="fa fa-lock fa-lg" aria-hidden="true"></i></span>
									<input type="password" <?php
									if (isset($minLengthPassword)){
										//Gestione lunghezza minima password
										echo "data-minlength='$minLengthPassword'";
									}
									?>class="form-control" name="password" id="password"  placeholder="Enter your Password"/>
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
	
	
	
	$(".campoNote").tooltip();
});
</script>
</html>
