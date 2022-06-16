<?php
session_start(); //starts or continues a session

include("functions.php");
ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();

$my_friends = get_friends($_SESSION['user_id']);
$num_friends = sizeof($my_friends);

$friend_data = get_user_data_from_users($my_friends);

if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
	$_SESSION['clicked'] = $_POST['clicked'];
	header("Location: view_player.php");
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
		
	<title>Game Shelf - My Friends</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="center-background" style="background-image: url('Visual Aids/scattered_dice.jpg');">
<ul>
    <li><a href="index.php">Home</a></li>
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
	<li class="dropdown active">
		<a href="javascript:void(0)" class="dropbtn"><?php if(incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Friends</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "my_friends.php";}else{echo "login.php";} ?>" class="active">View Friends</a>
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

<div class="main-header" style="margin-bottom: 2.5%;">My Friends</div>

<form method="post">

<?php
if($num_friends == 0){
	echo "<div class='popup'><p>You don't have any friends yet.</p></div>";
}

for ($i = 0; $i < $num_friends; $i++){
	echo "<div class='friend'>
			<input class='full-button' type='submit' value='". $my_friends[$i]."' name='clicked'>
			<img src='Profile Pictures/angled_meeple.jpg'>
			<p style='font-size: 45px; top: 30px;'>" . $friend_data[$i]['user_name'] . "</p>
			<p style='top: 90px; font-size: 24px;'>Number of Games: " . $friend_data[$i]['num_games'] . "</p>
		</div>";
}
?>
</form>
<br><br><br>

</body>
</html>