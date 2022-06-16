<?php
session_start(); //starts or continues a session

include("functions.php");
ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();
$my_id = $_SESSION['user_id'];

$query = "SELECT user_name, email, num_games, bio, profile_picture FROM users WHERE user_id='$my_id'";
$player_info = mysqli_fetch_assoc(mysqli_query($con, $query));
$profile_picture = $player_info['profile_picture'];

$my_friends = get_friends($my_id);
if (sizeof($my_friends) == 0){
	$friend_names = "None";
}else{
	$friend_names = array();
	foreach($my_friends as $friend){
		$friend_info = get_personnal_info($friend);
		array_push($friend_names, $friend_info['user_name']);
	}
	$friend_names = convert_arr_to_str($friend_names);
	$friend_names = str_replace(",", ", ", $friend_names);
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
	header("Location: edit_profile.php");
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
<body class="center-background" style="background-image: url('Visual Aids/scattered_pieces.jpg');">
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
	<li class="dropdown">
		<a href="javascript:void(0)" class="dropbtn"><?php if(incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Friends</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "my_friends.php";}else{echo "login.php";} ?>">View Friends</a>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "add_friend.php";}else{echo "login.php";} ?>"><?php if(incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Add Friend</a>
        </div>
    </li>
	<li class="dropdown active" style="float:right;">
		<a href="javascript:void(0)" class="dropbtn">My Account</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "profile.php";}else{echo "login.php";} ?>" class="active">My Profile</a>
            <a href="<?php if(isset($_SESSION['user_id'])){echo "logout.php";}else{echo "login.php";} ?>">Logout</a>
        </div>
    </li>
</ul>

<div class="main-header"><?php echo $player_info['user_name'] . "'s Profile";?></div>

<div class="game-pic" style="margin-bottom: 0;">
	<?php echo "<img src='Profile Pictures/" . $profile_picture . "' alt='Profile Picture'>";?>
</div> 

<div class="game-info" style="margin-left: 0; margin-bottom: 0;">
	<p>Number of Games in Shelf: <?php echo $player_info['num_games'];?></p>
	<p>Email Address: <?php echo $player_info['email'];?></p>
	<p>Bio: <?php echo $player_info['bio'];?></p>
	<p>Friends: <?php echo $friend_names; ?></p>
	<form method="post">
		<input class="button3" type="submit" name="action" value="Edit Profile" style="float:right;">
	</form>
</div>

</body>
</html>