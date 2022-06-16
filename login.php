<?php
	session_start();

	include("functions.php");

	$err_message=""; //setting error messages

	if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
		$user_name = test_input($_POST['user_name']);
		$password = test_input($_POST['password']);
		
		if(empty($user_name) || empty($password)){
			$err_message = "Username and password are required";
		} else{
			$query = "SELECT user_id, password, user_name FROM users where user_name='$user_name'";
			$result = mysqli_query(connect(), $query);
			
			if (mysqli_num_rows($result) < 1){
				$err_message = "Incorrect username or password";
			} else{
				$user_data = mysqli_fetch_assoc($result);
				if ($user_data['password'] === $password && $user_name === $user_data['user_name']){ //checks if password matches
					$_SESSION['user_id'] = $user_data['user_id']; //creates session variable containing user_id
					$_SESSION['connection'] = connect(); //creates session variable to the database
					header("Location: index.php"); //goes to index
				} else{
					$err_message = "Incorrect username or password";
				}
			}
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
		
	<title>Game Shelf - Login</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>

<body class="center-background" style="background-image: url('Visual Aids/meeple_snowflake.jpg');">
	<div class="box">
		<form method="post">
			<div class="form-header">Login</div>
			<span class="err-message"><?php if(!empty($err_message)){echo $err_message;}else echo "<p><br></p>" ?></span>
			<input type="text" name="user_name" class="input-box" placeholder="Username" value="<?php if(!empty($user_name)){echo $user_name;}?>" <?php if(empty($user_name)){echo "autofocus";}?>><br><br>
			<input type="password" name="password" class="input-box" placeholder="Password"><br>
			
			<input class="button" type="submit" value="Login"><br><br><br>
			
			<div  class="goto"> Don't have an account? <a href="signup.php">Sign up now!</a><br></div>
		</form>
	</div>
</body>
</html>