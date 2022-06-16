<?php
session_start(); //starts or continues a session

include("functions.php");

ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();//creates connection
$personnal_info = get_personnal_info($_SESSION['user_id']); //getting username
$my_games = get_my_games($_SESSION['user_id']);
$game_data = get_game_data($my_games);

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$_SESSION['clicked'] = $_POST['clicked'];
	if ($_SESSION['clicked'] == "Add"){
		header("Location: add_game1.php");
	} else{
		header("Location: view_game.php");
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
		
	<title>Game Shelf - View Shelf</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="generic-background" style="background-image: url('Visual Aids/large_game_shelf.jpg');">
<ul>
    <li><a href="index.php">Home</a></li>
    <li class="dropdown active">
		<a href="javascript:void(0)" class="dropbtn">My Shelf</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "view_shelf.php";}else{echo "login.php";} ?>" class="active">View Shelf</a> 
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

<div class="main-header"><?php echo $personnal_info['user_name'] . "'s Game Shelf"?></div>

<form method="post">
<?php 
for($i = 0; $i < sizeof($game_data); $i++){
	$game_name = $game_data[$i]['game_name'];
	$filename = $game_data[$i]['filename'];

	echo 	"<div class='game-container'>
				<input class='full-button' type='submit' value='" .$game_data[$i]['game_id']."' name='clicked'>
				<div class='game'>
					<p>" . $game_name . "</p>
					<img src='Game Images/" . $filename . "'>
				</div>
			</div>";
}

?>
<div class="game-container">
	<input class="full-button" type="submit" value="Add" name='clicked'>
	<div class="game">
		<p>Add Game</p>
		<img src="Game Images/plus_sign.png">
	</div>
</div>
</form>

</body>
</html>