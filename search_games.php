<?php
session_start(); //starts or continues a session
include("functions.php");

$my_id = $_SESSION['user_id'];
ensure_login($my_id);//ensures logged in
$con = connect();

$err_message="";

$friend_data  = get_user_data_from_users(get_friends($my_id));
$all_collections = array("Game Library", "My Shelf");
foreach($friend_data as $f){
	array_push($all_collections, $f['user_name']);
}

if(isset($_SESSION['friend_collection'])){
	$collection = get_personnal_info($_SESSION['friend_collection']);
	$collection = $collection['user_name'];
	unset($_SESSION['friend_collection']);
}else{
	$collection="Game Library";
}

$all_game_types = array_merge(array("All"), get_all_mechanisms());
sort($all_game_types);
$game_type = "All";

$all_complexities = array("Any", "1", "2", "3", "4", "5");
$complexity = "Any";

$sort_by = "Alphabetic Order";

$search = false;

if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(!isset($_POST['action']) || $_POST['action'] == "Search"){
		$all_ok = true;
		$keyword = style_input(test_input($_POST['keyword']));
		if(!empty($keyword) && strpos($keyword, ",")){
			$err_message = "Please only enter keyword.";
			$all_ok = false;
		}
		$players = test_input($_POST['players']);
		if(!empty($players) && !is_numeric($players)){
			$err_message = "Please enter the number of players as a single number.";
			$all_ok = false;
		}
		$length = test_input($_POST['length']);
		if(!empty($length) && !is_numeric($length)){
			$err_message = "Please enter the length of the game in minutes as a single number.";
			$all_ok = false;
		}
		$sort_by = $_POST['sort_by'];
		$collection = $_POST['collection'];
		$game_type = $_POST['game_type'];
		$complexity = $_POST['complexity'];
		$creator = (test_input($_POST['creator']));
		
		if($all_ok){
			$search = true;
			
			//exhaustive game list
			$query = "SELECT game_id FROM game_library";
			$result = mysqli_query($con, $query);
			$game_ids = array();
			while($row = mysqli_fetch_assoc($result)){
				array_push($game_ids, $row['game_id']);
			}
			$game_data = get_game_data($game_ids);
		
			//filter to collection
			switch($collection){
				case "Game Library":
				//do nothing
				break;
				case "My Shelf":
				$edit_collection = get_my_games($my_id);
				break;
				default:
				$friend_user_name = $collection;
				$friend_id = get_corresponding_user_id($friend_user_name);
				$edit_collection = get_my_games($friend_id);
			}
			if(isset($edit_collection)){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					if(in_array($game_data[$i]['game_id'], $edit_collection)){
						array_push($game_data_2, $game_data[$i]);
					}
				}
			} else{
				$game_data_2 = $game_data; //stays the same for Game Library
			}
			
			$game_data = $game_data_2; //temp
		
			//filter by game criteria
			
			if($game_type != "All"){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					if(is_numeric(strpos($game_data[$i]['category'], $game_type))){ //accounts for being at position 0
						array_push($game_data_2, $game_data[$i]);
					}
				}
				$game_data = $game_data_2; //temp
			}
		
			//filter by keyword
			
			if(!empty($keyword)){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					if(is_numeric(strpos($game_data[$i]['keywords'], $keyword))){ //accounts for being at position 0
						array_push($game_data_2, $game_data[$i]);
					}
				}
				$game_data = $game_data_2; //temp
			}
			
			//filter by creator
			if(!empty($creator)){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					if($game_data[$i]['creator'] == $creator){
						array_push($game_data_2, $game_data[$i]);
					}
				}
				$game_data = $game_data_2; //temp
			}
		
			//filter by complexity
			
			if($complexity != "Any"){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					if($game_data[$i]['complexity'] == $complexity){
						array_push($game_data_2, $game_data[$i]);
					}
				}
				$game_data = $game_data_2; //temp
			}
		
			//filter by players
			
			if(!empty($players)){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					$range = explode(",", $game_data[$i]['player_count']);
					if($players >= $range[0] && $players <= $range[1]){
						array_push($game_data_2, $game_data[$i]);
					}
				}
				$game_data = $game_data_2; //temp
			}
		
			//filter by length
			
			if(!empty($length)){
				$game_data_2 = array();
				for($i = 0; $i < sizeof($game_data); $i++){
					$range = explode(",", $game_data[$i]['length']);
					if($length >= $range[0] && $length <= $range[1]){
						array_push($game_data_2, $game_data[$i]);
					}
				}
				$game_data = $game_data_2; //temp
			}
		
			//organize
			usort($game_data, "alphabetical_comparison");
		
			if($sort_by == "Playing Time"){
				usort($game_data, "time_comparison");
			}elseif($sort_by == "Complexity"){
				usort($game_data, "complexity_comparison");
			}elseif($sort_by == "Popularity"){
				usort($game_data, "popularity_comparison");
			}
		}
		
	}else{
		$_SESSION['clicked'] = $_POST['action'];
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
		
	<title>Game Shelf - Search Games</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="center-background" style="background-image: url('Visual Aids/partial_game_shelf.jpg');">
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
	<li class="dropdown active">
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

<div class="main-header">Search Games</div>
<div class="sub-header">Pick the collection you wish to search, what type of game you want to find, and how you want the results to be sorted.</div>

<form method="post">
	<?php	
	if($search){
		if(sizeof($game_data) == 0){
			echo "<div class='popup'  style='margin-bottom: 2%;margin-top: 100px; height: 150px;'>
				<p>There are no games that match your search criteria.</p>
			</div>";
		}
		for($i = 0; $i < sizeof($game_data); $i++){
			$game_name = $game_data[$i]['game_name'];
			$filename = $game_data[$i]['filename'];
	
			echo "<div class='game-container'>
					<input class='full-button' type='submit' value='" .$game_data[$i]['game_id']."' name='action'>
					<div class='game'>
						<p>" . $game_name . "</p>
						<img src='Game Images/" . $filename . "'>
					</div>
				</div>";
		}
	}?>

<div class="big-box" style="width: 50%; margin: 4% 0% 4% 23%; display:inline-block;">
		<span class="big-form-header" style="margin-right: 20px">Choose Collection: </span>
		
		<select name="collection" class="multi-select" size="1">
			<?php for($i = 0; $i < sizeof($all_collections); $i++){
				echo "<option value='" . $all_collections[$i] . "'";
				if($collection == $all_collections[$i]){
					echo "selected";
				}
				if($i > 1){
					echo ">" . $all_collections[$i] . "'s Shelf</option>";
				}else{
					echo ">" . $all_collections[$i] . "</option>";
				}
			} ?>
		</select><br><br>
		
		<div class="big-form-header">Specify Criteria:</div>
		<div class="err-message-red" style="font-size: 20px; margin-top: 20px;"><?php if(!empty($err_message)){echo $err_message;}?></div>
		
		<div class="column" style="margin-right: 15%;">
			<p>Game Mechanism:<br></p>
			<select name="game_type" class="multi-select" size="3">
				<?php foreach ($all_game_types as $type){
					echo "<option value='" . $type . "'";
					if($game_type == $type){
						echo "selected";
					}
					echo ">" . $type . "</option>";
				} ?>
			</select><br>
				
			<p>Keyword:<br></p>
			<input type="text" name="keyword" class="multi-select" style="width: 300px;" placeholder="None" value="<?php if(isset($keyword)){echo $keyword;}?>">

			<p>Creator:<br></p>
			<input type="text" name="creator" class="multi-select" style="width: 300px;" placeholder="None" value="<?php if(isset($creator)){echo $creator;}?>">		
		</div>
		
		<div class="column">
			<p>Complexity:<br></p>
			<select name="complexity" class="multi-select" size="1" style="width: 120px;">
				<?php foreach ($all_complexities as $comp){
					echo "<option value='" . $comp . "'";
					if($complexity == $comp){
						echo "selected";
					}
					echo ">" . $comp . "</option>";
				} ?>
			</select><br>
				
			<p>Players:<br></p>
			<input type="text" name="players" class="multi-select" style="width: 100px;" placeholder="Any" value="<?php if(isset($players)){echo $players;}?>"><br>
			
			<p>Length:<br></p>
			<input type="text" name="length" class="multi-select" style="width: 100px;" placeholder="Any" value="<?php if(isset($length)){echo $length;}?>">
		</div>
		
		<div class="big-form-header" style="margin-bottom: 30px; margin-top: 10px;">Display Games By: </div>
		<label class="container indent">
			<input type="radio" name="sort_by" class="go-away" value="Alphabetic Order" <?php if($sort_by == "Alphabetic Order"){echo "checked";}?>>		
			<span class="radio1 radio1B"></span><span class="radio2 radio2B"></span><span class="radio3 radio3B"></span>
			Alphabetic Order
		</label>
		<label class="container indent">
			<input type="radio" name="sort_by" class="go-away" value="Popularity" <?php if($sort_by == "Popularity"){echo "checked";}?>>
			<span class="radio1 radio1B"></span><span class="radio2 radio2B"></span><span class="radio3 radio3B"></span>
			Popularity
		</label>
		<label class="container indent">
			<input type="radio" name="sort_by" class="go-away" value="Complexity" <?php if($sort_by == "Complexity"){echo "checked";}?>>
			<span class="radio1 radio1B"></span><span class="radio2 radio2B"></span><span class="radio3 radio3B"></span>
			Complexity
		</label>
		<label class="container indent">
			<input type="radio" name="sort_by" class="go-away" value="Playing Time" <?php if($sort_by == "Playing Time"){echo "checked";}?>>
			<span class="radio1 radio1B"></span><span class="radio2 radio2B"></span><span class="radio3 radio3B"></span>
			Playing Time
		</label>

		<input class="button" type="submit" name="action" value="Search"><br><br>
	</form>
</div>

</body>
</html>