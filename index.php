<?php
session_start(); //starts or continues a session
include("functions.php");
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="keywords" content="Board Games, Organize, Visualize, Discover, Explore, Share">
	<meta name="description" content="A site to keep track of your board game collection and help you discover new games.">
	<meta name="author" content="Fiona Cheng">
    <meta name="viewport" content="width=device-width, initial-scale=1">
		
	<title>Game Shelf</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="generic-background" style="background-image: url('Visual Aids/partial_game_shelf.jpg');">
<ul>
    <li class = "active"><a href="index.php">Home</a></li>
    <li class="dropdown">
		<a href="javascript:void(0)" class="dropbtn">My Shelf</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "view_shelf.php";}else{echo "login.php";} ?>">View Shelf</a> 
			<a href="<?php if(isset($_SESSION['user_id'])){echo "add_game1.php";}else{echo "login.php";} ?>">Add Game</a>
        </div>
	</li>
    <li class="dropdown">
		<a href="<?php if(isset($_SESSION['user_id'])){echo "view_library.php";}else{echo "login.php";} ?>" class="dropbtn">Game Library</a>
    </li>
	<li class="dropdown">
		<a href="<?php if(isset($_SESSION['user_id'])){echo "search_games.php";}else{echo "login.php";} ?>" class="dropbtn">Search Games</a>
    </li>
	<li class="dropdown">
		<a href="javascript:void(0)" class="dropbtn"><?php if(isset($_SESSION['user_id']) &&incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Friends</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "my_friends.php";}else{echo "login.php";} ?>">View Friends</a>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "add_friend.php";}else{echo "login.php";} ?>"><?php if(isset($_SESSION['user_id']) &&incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Add Friend</a>
        </div>
    </li>
	<li class="dropdown" style="float:right;">
		<a href="javascript:void(0)" class="dropbtn">My Account</a>
        <div class="dropdown-content">
			<?php if(!isset($_SESSION['user_id'])){ echo "<a href='profile.php'>Login</a>";} ?>
			<?php if(!isset($_SESSION['user_id'])){ echo "<a href='signup.php'>Signup</a>";} ?>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "profile.php";}else{echo "login.php";} ?>">My Profile</a>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "logout.php";}else{echo "login.php";} ?>">Logout</a>
        </div>
    </li>
</ul>

<div class="main-header">Game Shelf</div>
<div class="sub-header">All your games in one place</div>

<br><br><br>
<table class="center">
	<tr>
		<td class="words">Add games to your own virtual game shelf to keep track of your boardgames</td>
		<td class="words">Explore the game library to discover new games</td>
		<td class="words">Search for games based on criteria such as player count and complexity</td>
	</tr>
	<tr>
		<td><button onclick="window.location.href='<?php if(isset($_SESSION['user_id'])){echo "add_game1.php";}else{echo "login.php";} ?>';" class="button2" >Add Game</button></td>
		<td><button onclick="window.location.href='<?php if(isset($_SESSION['user_id'])){echo "view_library.php";}else{echo "login.php";} ?>';" class="button2" >Visit Library</button></td>
		<td><button onclick="window.location.href='<?php if(isset($_SESSION['user_id'])){echo "search_games.php";}else{echo "login.php";} ?>';" class="button2" >Search Games</button></td>
	</tr>
</table>

</body>
</html>