<?php
	session_start();

	include("functions.php");
	
	$username_err = $password_err = $email_err = ""; //setting error messages
	$user_name = $password = $email = ""; //setting initial info
	
	if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
		$user_name = test_input($_POST['user_name']);
		$password = test_input($_POST['password']);
		$email = test_input($_POST['email']);
		
		$all_ok = true;
		
		//checking to make sure username, password and email are all valid
		$status = valid_username($user_name);
		if($status != "Ok"){
			$all_ok = false;
			$username_err = $status;
		}
		$status = valid_password($password);
		if($status != "Ok"){
			$all_ok = false;
			$password_err = $status;
		}
		$status = valid_email($email);
		if($status != "Ok"){
			$all_ok = false;
			$email_err = $status;
		}
		
		if ($all_ok){ //if everything is good, create a new user
			$query = "INSERT INTO users (user_name, password, email) values ('$user_name', '$password', '$email')";
			$con = connect();
			mysqli_query($con, $query);
			$query = "INSERT INTO friends () values ()";
			mysqli_query($con, $query);
			$query = "INSERT INTO games_by_user () values ()";
			mysqli_query($con, $query);
			
			header("Location: login.php"); //redirects to login
			
			/*$to = $email; Email no longer works because gmail turned off less secure apps
			$subject = 'Account Confirmation';
			$message = "Hi " . $user_name .", thank you for registering for Game Shelf!";
			
			echo $to . "<br>";
			echo $subject . "<br>";
			echo $message . "<br>";

			$mail_sent = mail($to, $subject, $message);
			
			if ($mail_sent == true){
				header("Location: login.php");
			} else{
				header("Location: login.php");
				echo "Something went wrong.";
			}*/
			
		}
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="keywords" content="Board Games, Organize, Visualize, Discover, Share">
	<meta name="description" content="A site to keep track of your board game collection and help you discover new games.">
	<meta name="author" content="Fiona Cheng">
    <meta name="viewport" content="width=device-width, initial-scale=1">
		
	<title>Game Shelf - Signup</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="center-background" style="background-image: url('Visual Aids/meeple_snowflake.jpg');">
	<div class="box">
		<form method="post">
			<div class="form-header">Signup</div>
			<span class="err-message"><?php if($username_err){echo $username_err;}else echo "<p><br></p>" ?></span>
			<input type="text" name="user_name" class="input-box" placeholder="Username" value="<?php if(!empty($user_name)){echo $user_name;}?>" <?php if(empty($user_name)){echo "autofocus";}?>>
			<span class="err-message"><?php if($password_err){echo $password_err;}else echo "<p><br></p>" ?></span>
			<input type="password" name="password" class="input-box" placeholder="Password">
			<span class="err-message"><?php if($email_err){echo $email_err;}else echo "<p><br></p>" ?></span>
			<input type="email" name="email" class="input-box" placeholder="Email Address" value="<?php if(!empty($email)){echo $email;}?>">
			
			<input id="button" type="submit" value="Signup" class="button"><br><br><br><br>
			
			<div class="goto">Already have an account? <a href="login.php">Login here!</a><br></div>
		</form>
	</div>
</body>
</html>