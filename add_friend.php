<?php
session_start(); //starts or continues a session

include("functions.php");

ensure_login($_SESSION['user_id']);//ensures logged in
$my_id = $_SESSION['user_id'];
$con = connect();
$personnal_info = get_personnal_info($my_id); 

//user list prep
$query = "SELECT user_name FROM users";
$result = mysqli_query($con, $query);
$total_users = mysqli_num_rows($result);

$other_users = array();
for($i = 1; $i <= $total_users; $i++){
	if ($i != $my_id){
		array_push($other_users, $i);
	}
}

$all_user_data = get_user_data_from_users($other_users);

//prep for input by username
$user_name_list = array();
while ($row = mysqli_fetch_assoc($result)){
	array_push($user_name_list, $row['user_name']);
}

$err_message = "";
$successful_send = false;

//prep for incoming friend requests
$query = "SELECT incoming_requests FROM friends WHERE user_id = '$my_id'";
$incoming_requests = mysqli_fetch_assoc(mysqli_query($con, $query));
$incoming_requests = $incoming_requests['incoming_requests'];
if (strlen($incoming_requests) > 0){
	$incoming_requests = explode(",", $incoming_requests);
} else{
	$incoming_requests = array();
}

if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
	$successful_send = false;
	if ($_POST['clicked'] == "Send Friend Request"){
		$user_name = test_input($_POST['user_name']);
		if(empty($user_name)){
			$err_message = "Please enter a username.";
		}elseif($user_name == $personnal_info['user_name']){
			$err_message = "You can't be friends with yourself.";
		} else{
			$err_message = send_friend_request($my_id, $user_name);
			if ($err_message == "Done"){
				$err_message = "";
				$successful_send = true;
			}
		}
	} else{
		$_SESSION['clicked'] = substr($_POST['clicked'], 1, strlen($_POST['clicked']) - 1); //get rid of "!"
		header("Location: view_player.php");
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
<body class="generic-background" style="background-image: url('Visual Aids/scattered_dice.jpg');">
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
            <a href="<?php if(isset($_SESSION['user_id'])){echo "add_friend.php";}else{echo "login.php";} ?>" class="active"><?php if(incoming_request($_SESSION['user_id'])){echo "<i class='fa-solid fa-circle-exclamation fa-1x' style='color:#EEF40B;padding-right:10px;'></i>";}?>Add Friend</a>
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

<div class="main-header">Add a Friend</div>
<div class="sub-header">Add a friend by username or click on a profile in the user list to go to a player's profile.</div>

<form method="post">
<?php
foreach($incoming_requests as $user_id){
	$query = "SELECT user_name FROM users WHERE user_id='$user_id'";
	$user_name = mysqli_fetch_assoc(mysqli_query($con, $query));
	$user_name = $user_name['user_name'];
	echo "<div class='popup'  style='margin-bottom: 2%;margin-top: 100px; height: 170px;'><input class='full-button' type='submit' value='!". $user_id."' name='clicked'><p>" . $user_name ." has sent you a friend request. Click on this box to see " . $user_name . "'s profile.</p></div>";
}
?>

<div class="big-box">
<p class="big-form-header"><?php echo "Friend&#39;s Username";?></p>
		
<div class="err-message-red" style="font-size: 24px;"><?php if(!empty($err_message)){echo $err_message;}?></div>
<div class="err-message-red" style="color: black; font-size: 24px;"><?php if($successful_send){echo "Friend Request sent!";}?></div>
<input type="text" name="user_name" class="big-input-box" value="<?php if(isset($friend_name)){echo $friend_name;}?>" style="margin-top: 10px;">
<br>
			
<input class="button" type="submit" value="Send Friend Request" style="width: 350px;" name="clicked"><br ><br>
</div>

<div class="border">
<div class = "user-list-header">User List</div>

<?php
for ($i = 0; $i < $total_users - 1; $i++){
	echo "<div class='friend'>
			<input class='full-button' type='submit' value='!". $other_users[$i]."' name='clicked'>
			<img src='Profile Pictures/angled_meeple.jpg'>
			<p style='font-size: 45px; top: 30px;'>" . $all_user_data[$i]['user_name'] . "</p>
			<p style='top: 90px; font-size: 24px;'>Number of Games in Shelf: " . $all_user_data[$i]['num_games'] . "</p>
		</div>";
}
?>
</div>
</form>

</body>
</html>