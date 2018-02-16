<?php
include "config.php";
$errorMsg = null;

if(isset($_POST['login'])){
	// $email = ;
	// $password = ;
	//Mi faccio resituire solo il primo valore
	$res = $database->get("user_login","*", ["email" => $_POST['email']] );
	
	// $cek = mysqli_num_rows(mysqli_query($connection , "SELECT * FROM user_login WHERE email='$email' AND password='$password'"));
	// $data = mysqli_fetch_array(mysqli_query($connection , "SELECT * FROM user_login WHERE email='$email' AND password='$password'"));
	
	// echo count($res);
	// die();
	// var_dump($res);die();
	if($res !== false && password_verify($_POST['password'],$res['password']))	{
		
		session_start();
		$_SESSION['userId'] = $res['id'];
		$_SESSION['email'] = $res['email'];
		$_SESSION['name'] = $res['full_name'];
		$_SESSION['privkey'] = $res['privkey'];
		$_SESSION['pubkey'] = $res['pubkey'];
		$_SESSION['userId'] = $res['id'];
		
		// echo "<script language=\"javascript\">alert(\"welcome \");document.location.href='index.php';</script>";
		
		header('Location: index.php');
		die();
		
	} else{
		$errorMsg = "Invalid username or password";
	}
}
?>
<!DOCTYPE HTML>
<html>
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

</head>
<body>

<div class="container">
        <div class="card card-container">
            <img id="profile-img" class="profile-img-card" src="img/avatar_2x.png" />
            <p id="profile-name" class="profile-name-card"></p>
			<?php
			if ($errorMsg){
				echo "<p>$errorMsg</p>";
			}
			?>
            <form class="form-signin" action="" method="POST">
				<span id="reauth-email" class="reauth-email"></span>
                <input type="username" id="inputEmail" name="email" class="form-control" placeholder="Email address" required autofocus>
                <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
                <br>
               <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit"  name="login">Login</button>
            </form>
			
				<?php
				//Mostro la possibilità di registrazione solo se è valorizzato nel campo config
				if ($isRegistrationEnabled ){
					 // echo "<button class=\"btn btn-lg btn-primary btn-block btn-signin\" type=\"submit\"  name=\"login\">Registrati</button>";
					 
					 echo "<a href=\"register.php\" class=\"btn  btn-primary btn-block\">Registrati</a>";
				}
				?>
                    
            
            
        </div>
</div>

</body>
</html>
