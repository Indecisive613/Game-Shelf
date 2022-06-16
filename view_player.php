<?php
session_start(); //starts or continues a session

include("functions.php");
$my_id = $_SESSION['user_id'];
ensure_login($my_id);//ensures logged in
$con = connect();
$visitor_id = $_SESSION['clicked'];
$my_friends = get_friends($my_id);

$visitor_friends = get_friends($visitor_id);
if (sizeof($visitor_friends) == 0){
	$friend_names = "None";
}else{
	$friend_names = array();
	foreach($visitor_friends as $friend){
		$friend_info = get_personnal_info($friend);
		array_push($friend_names, $friend_info['user_name']);
	}
	$friend_names = convert_arr_to_str($friend_names);
	$friend_names = str_replace(",", ", ", $friend_names);
}

if (in_array($visitor_id,  $my_friends)){
	$friend_status = "yes";
} else{
	$query = "SELECT pending_requests, incoming_requests FROM friends WHERE user_id = '$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$pending_requests = explode(",", $result['pending_requests']);
	$incoming_requests = explode(",", $result['incoming_requests']);
	if(in_array($visitor_id, $pending_requests)){ 
		$friend_status = "outgoing";
	}elseif(in_array($visitor_id, $incoming_requests)){ 
		$friend_status = "incoming";
	} else{
		$friend_status = "no";
	}
}

$query = "SELECT user_name, email, num_games, bio, profile_picture FROM users WHERE user_id='$visitor_id'";
$player_info = mysqli_fetch_assoc(mysqli_query($con, $query));
$profile_picture = $player_info['profile_picture'];

if($_SERVER['REQUEST_METHOD'] == "POST"){
	if($_POST['action'] == "Send Friend Request"){
		send_friend_request($my_id, $player_info['user_name']); 
	} elseif($_POST['action'] == "Remove Friend"){
		//remove friend from your friend list
		$new_friends = array();
		foreach($my_friends as $friend){
			if ($friend != $visitor_id){
				array_push($new_friends, $friend);
			}
		}
		$new_friends = convert_arr_to_str($new_friends);
		$query = "UPDATE friends SET friends='$new_friends' WHERE user_id = '$my_id'";
		mysqli_query($con, $query);
		
		//remove yourself from other player's friend list
		$query = "SELECT friends FROM friends WHERE user_id='$visitor_id'";
		$old_friends = mysqli_fetch_assoc(mysqli_query($con, $query));
		$old_friends = explode(",", $old_friends['friends']);
		$new_friends = array();
		foreach($old_friends as $friend){
			if ($friend != $my_id){
				array_push($new_friends, $friend);
			}
		}
		$new_friends = convert_arr_to_str($new_friends);
		$query = "UPDATE friends SET friends='$new_friends' WHERE user_id = '$visitor_id'";
		mysqli_query($con, $query);
	}elseif($_POST['action'] == "Accept Friend Request"){
		remove_friend_request($my_id, $visitor_id);
		add_friend($my_id, $visitor_id);
		add_friend($visitor_id, $my_id);
	}elseif($_POST['action'] == "Decline"){
		remove_friend_request($my_id, $visitor_id);
	}else{//view shelf
		$_SESSION['friend_collection'] = $visitor_id;
		header("Location: search_games.php"); 
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
	<li class="dropdown active">
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

<div class="main-header"><?php echo $player_info['user_name'] . "'s Profile";?></div>

<div class="game-pic" style="margin-bottom: 0;">
	<?php echo "<img src='Profile Pictures/" . $profile_picture . "' alt='Profile Picture'>";?>
</div> 

<div class="game-info" style="margin-left: 0; margin-bottom: 0;">
	<p>Number of Games: <?php echo $player_info['num_games'];?></p>
	<p>Bio: <?php echo $player_info['bio'];?></p>
	<p>Friends: <?php echo $friend_names; ?></p>
	<form method="post">
		<?php if($friend_status=="yes"){echo "<input class='button3' type='submit' name='action' value='View " . $player_info['user_name'] . "&#39;s Shelf' style='float:right; width: auto; padding-left: 20px; padding-right: 20px;'>
		<input class='button3' type='submit' name='action' value='Remove Friend' style='float:right;'>";} 
		elseif($friend_status == "no"){ echo "<input class='button3' type='submit' name='action' value='Send Friend Request' style='float:right; width: 350px;'>";}
		else{ echo "<p>Friend Request Pending.</p><br>";
		if($friend_status == "incoming"){echo "<input class='button3' type='submit' name='action' value='Accept Friend Request' style='float:right; width: 350px;'><input class='button3' type='submit' name='action' value='Decline' style='float:right; width: 350px;'>";}}?>
	</form>
</div>

</body>
</html>