<?php
session_start(); //starts or continues a session

include("functions.php");
ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();
$game_id = $_SESSION['clicked'];

$query = "SELECT * FROM game_library WHERE game_id = '$game_id'";
$game_data = mysqli_fetch_assoc(mysqli_query($con, $query));

$player_count = explode(",", $game_data['player_count']);
$game_length = explode(",", $game_data['length']);

$game_popularity = get_popularity_chart();
$game_popularity = $game_popularity[$game_data['popularity']];

$game_type = str_replace(",",", ",$game_data['category']);

if (strlen($game_data['keywords']) < 2){ //empty is just a comma
	$keywords = "There are no keywords yet.";
} else{
	$keywords = str_replace(",",", ",$game_data['keywords']);
}

$query = "SELECT game_id FROM game_library";
$total_games = mysqli_num_rows(mysqli_query($con, $query));

$filename = $game_data['filename'];

$my_games = get_my_games($_SESSION['user_id']);
if (in_array($game_id, $my_games)){ 
	$owned = true;
}else{
	$owned = false;
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
	$_SESSION['clicked'] = $_POST['action'];
	if ($_SESSION['clicked'] == "Edit Description"){
		$_SESSION['clicked'] = $game_id;
		header("Location: edit_game.php"); 
	} elseif ($_SESSION['clicked'] == "Add to My Shelf"){
		add_existing_game($game_id, $_SESSION['user_id']);
		$_SESSION['message'] = $game_data['game_name'] . " has been successfully added to your shelf.";
		header("Location: confirmation.php"); 
	} else{
		$_SESSION['game_name'] = $game_data['game_name'];
		remove_game($game_id, $_SESSION['user_id']);
		$_SESSION['message'] = $game_data['game_name'] . " has been successfully removed from your shelf.";
		header("Location: confirmation.php"); 
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
		<a href="<?php if(isset($_SESSION['user_id'])){echo "view_library.php";}else{echo "login.php";} ?>" class="dropbtn active">Game Library</a>
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

<div class="main-header" ><?php echo $game_data['game_name'];?></div>
<div class="sub-header" style="background-color:#19E6C8; color: black; padding: 5px; font-size: 40px;">Created by <?php echo $game_data['creator'];?></div>

<div class="game-info">
	<p>Complexity: <?php echo $game_data['complexity'];?> / 5</p>
	<p>Player Count: <?php echo $player_count[0] . " - " . $player_count[1];?></p>
	<p>Playing Time: <?php echo $game_length[0] . "min - " . $game_length[1] . "min";?></p>
	<p>Game Type: <?php echo $game_type;?></p>
	<p>Keyword(s): <?php echo $keywords;?></p>
	<p>Popularity: <?php echo "#" . $game_popularity . " out of " . $total_games . " games";?></p>
	
	<form method="post">
		<input class="button3" type="submit" name="action" value="Edit Description" style="float:left;">
		<?php
		if(!$owned){
		echo "<input class='button3' type='submit' name='action' value='Add to My Shelf' >";
		} else{
		echo "<input class='button3' type='submit' name='action' value='Remove from My Shelf' style='width: 350px;'>";
		}?>
	</form>
</div>

<div class="game-pic">
	<?php echo "<img src='Game Images/" . $filename . "'><br>
	Published by ". $game_data['publisher'];?>
</div>



</body>
</html>