<?php
session_start(); //starts or continues a session

include("functions.php");

ensure_login($_SESSION['user_id']);//ensures logged in

$personnal_info = get_personnal_info($_SESSION['user_id']); //getting username
$err_message=""; //setting error messages

if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
	$game_name = test_input($_POST['game_name']);
	if(empty($game_name)){
			$err_message = "Game name is required.";
	} else{
		$game_name = style_input($game_name); //convert sEveN woNDErs to Seven Wonders
		
		$query = "SELECT game_id FROM game_library where game_name='$game_name'";
		$result = mysqli_query(connect(), $query);
		$_SESSION['game_name'] = $game_name;
			
		if (mysqli_num_rows($result) < 1){
			header("Location: add_game2.php"); //if game dne, make new game
		} else{
			$con = connect();
			$my_id = $_SESSION['user_id'];
			
			//Get array of games owned by the user
			$query = "SELECT games FROM games_by_user WHERE user_id='$my_id'";
			$result = mysqli_fetch_assoc(mysqli_query($con,$query));
			$existing_games = str_to_stylized_arr($result['games']);
			
			//Get the game id for the current, existing (in game library) game
			$query = "SELECT game_id FROM game_library WHERE game_name = '$game_name'";
			$existing_game_id = mysqli_fetch_assoc(mysqli_query($con,$query));
			$existing_game_id = $existing_game_id['game_id'];
			
			if (in_array($existing_game_id , $existing_games)){ //ensures no duplicates on shelf
				$err_message = "This game is already on your shelf.";
			} else { //adds preexisting game to shelf
				add_existing_game($existing_game_id, $my_id);
				$_SESSION['message'] = $game_name . " has been added to your shelf.";
				header("Location: confirmation.php"); 
			}
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="keywords" content="Board Games, Organize, Visualize, Discover, Explore, Share">
	<meta name="description" content="A site to keep track of your board game collection and help you discover new games.">
	<meta name="author" content="Fiona Cheng">
    <meta name="viewport" content="width=device-width, initial-scale=1">
		
	<title>Game Shelf - Add Game</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="center-background" style="background-image: url('Visual Aids/scorecharts.jpg');">
<ul>
    <li><a href="index.php">Home</a></li>
    <li class="dropdown active">
		<a href="javascript:void(0)" class="dropbtn">My Shelf</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "view_shelf.php";}else{echo "login.php";} ?>">View Shelf</a> 
			<a href="<?php if(isset($_SESSION['user_id'])){echo "add_game1.php";}else{echo "login.php";} ?>" class="active">Add Game</a>
        </div>
	</li>
    <li class="dropdown">
		<a href="<?php if(isset($_SESSION['user_id'])){echo "view_library.php";}else{echo "login.php";} ?>" class="dropbtn">Game Library</a>
    </li>
	<li class="dropdown">
		<a href="<?php if(isset($_SESSION['user_id'])){echo "search_games.php";}else{echo "login.php";} ?>" class="dropbtn">Search Games</a>
    </li>
	<li class="dropdown">
		<a href="javascript:void(0)" class="dropbtn"><?php if(incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Friends</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "my_friends.php";}else{echo "login.php";} ?>">View Friends</a>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "add_friend.php";}else{echo "login.php";} ?>"><?php if(incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Add Friend</a>
        </div>
    </li>
	<li class="dropdown" style="float:right;">
		<a href="javascript:void(0)" class="dropbtn">My Account</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "profile.php";}else{echo "login.php";} ?>">My Profile</a>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "logout.php";}else{echo "login.php";} ?>">Logout</a>
        </div>
    </li>
</ul>



<div class="big-box">
	<form method="post">
		<p class="big-form-header">Add a Game to <?php echo $personnal_info['user_name'] . "'s";?> Game Shelf</p><br>
		
		<div class="form-descriptor">Name of Game <div class="err-message-red"><?php if(!empty($err_message)){echo $err_message;}else echo "<p></p>" ?></div></div>
		<input type="text" name="game_name" class="big-input-box" value="<?php if(!empty($game_name)){echo $game_name;}?>" <?php if(empty($game_name)){echo "autofocus";}?>>

		<br><br>
			
		<input class="button" type="submit" value="Next"><br><br><br>
	</form>
</div>

</body>
</html>