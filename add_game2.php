<?php
session_start(); //starts or continues a session

include("functions.php");

ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();//creates connection

$personnal_info = get_personnal_info($_SESSION['user_id']); //getting username
$creator_err = $publisher_err = $complexity_err = $player_count_err = $length_err = $game_type_err = $keyword_err = $image_err = ""; //setting error messages
$checked_items = array();

if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
	/*ignoring type and image*/
	$creator = test_input($_POST['creator']);
	$publisher = test_input($_POST['publisher']);
	if (in_array("complexity", array_keys($_POST))){ //if complexity was selected
		$complexity = (int)$_POST['complexity'];
	}
	$low_player = test_input($_POST['low_player']);
	$high_player = test_input($_POST['high_player']);
	$low_length = test_input($_POST['low_length']);
	$high_length = test_input($_POST['high_length']);
	if (in_array("category", array_keys($_POST))){ //if category was selected
		$checked_items = array(); //resets to empty in case something was unchecked
		foreach ($_POST['category'] as $elem){//adds all checked items.
			array_push($checked_items, $elem);
		}
		if (in_array("Other", $checked_items)){
			unset($checked_items[sizeof($checked_items)-1]);
			if (strlen($_POST['other_category'])>0){
				$other_category = style_input(test_input($_POST['other_category']));
				array_push($checked_items, $other_category);
			}
		}
	}
	$keywords = test_input($_POST['keywords']);

	$all_ok = true;
	
	if(empty($creator)){
		$creator_err = "Creator is required.";
		$all_ok = false;
	}
	if(empty($publisher)){
		$publisher_err = "Publisher is required.";
		$all_ok = false;
	}
	if(!isset($complexity)){
		$complexity_err = "Complexity is required.";
		$all_ok = false;
	}
	if(empty($low_player) || empty($high_player)){
		$player_count_err = "Both sides of the range are required.";
		$all_ok = false;
	} elseif (!preg_match("/^[0-9]*$/",$low_player) || !preg_match("/^[0-9]*$/",$high_player)){
		$player_count_err = "Range must be numeric.";
		$all_ok = false;
	} elseif ((int)$low_player > (int)$high_player){
		$player_count_err = "Please enter a valid range.";
		$all_ok = false;
	}
	if(empty($low_length) || empty($high_length)){
		$length_err = "Both sides of the range are required.";
		$all_ok = false;
	} elseif (!preg_match("/^[0-9]*$/",$low_length) || !preg_match("/^[0-9]*$/",$high_length)){
		$length_err = "Range must be numeric.";
		$all_ok = false;
	} elseif ((int)$low_length > (int)$high_length){
		$length_err = "Please enter a valid range.";
		$all_ok = false;
	}
	if(sizeof($checked_items) == 0){
		$game_type_err = "Game type is mandatory.";
		$all_ok = false;
	}
	if(strlen($_FILES["uploadfile"]["name"]) > 0){//check image doesn't already exist
		$filename = $_FILES["uploadfile"]["name"];
		$sql="SELECT game_id FROM game_library WHERE filename='$filename'";
		$result = mysqli_query($con, $sql);
		if (mysqli_num_rows($result)>0){
			$image_err = "A file with this name already exists. Please rename your file.";
			$all_ok = false;
		}
	}
	
	if($all_ok){
		$game_name = $_SESSION['game_name'];
		$creator = style_input($creator);
		$publisher = style_input($publisher);
		$game_length = $low_length . "," . $high_length;
		$player_count = $low_player . "," . $high_player;
		$game_types = convert_arr_to_str($checked_items);
		$individual_keywords = str_to_stylized_arr($keywords);
		$keywords = convert_arr_to_str($individual_keywords);
		if(strlen(($_FILES["uploadfile"]["name"])) > 0){//downloads image
			$filename = $_FILES["uploadfile"]["name"];
			$tempname = $_FILES["uploadfile"]["tmp_name"];
			$folder = "Game Images/".$filename;
			move_uploaded_file($tempname, $folder);
		} else{
			$filename = "default_image.png";
		}
		$query = "SELECT game_id FROM game_library WHERE game_name='$game_name'";
		$result = mysqli_num_rows(mysqli_query($con, $query));
		
		if($result == 0){ //the game doesn't exist yet (doing this in case someone presses back after filling the form)
			//adds the new game to the game library
			$query = "INSERT INTO game_library (game_name, creator, publisher, complexity, length, player_count, category, keywords, filename) values ('$game_name', '$creator', '$publisher', '$complexity','$game_length', '$player_count', '$game_types', '$keywords', '$filename')";
			mysqli_query($con, $query);
		
			//adds the current game to a users game shelf
			$query = "SELECT game_id FROM game_library WHERE game_name='$game_name'";
			$game_id = mysqli_fetch_assoc(mysqli_query($con, $query));
			$game_id = $game_id['game_id'];
			add_existing_game($game_id, $_SESSION['user_id']);
		
			$_SESSION['message'] = $game_name . " has been successfully added to your shelf.";
			header("Location: confirmation.php"); 
		}else{
			$_SESSION['message'] = $game_name . " has already been added to your shelf.";
			header("Location: confirmation.php"); 
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
	<form method="post" enctype="multipart/form-data">
		<p class="big-form-header">Add <?php echo $_SESSION['game_name'];?> to <?php echo $personnal_info['user_name'] . "'s";?> Game Shelf</p><br>
		
		<div class="form-descriptor">Creator <div class="err-message-red"><?php if(!empty($creator_err)){echo $creator_err;}else echo "<p></p>" ?></div></div>
		<input type="text" name="creator" class="big-input-box" value="<?php if(!empty($creator)){echo $creator;}?>" <?php if(empty($creator)){echo "autofocus";}?>><br><br>
		
		<div class="form-descriptor">Publisher <div class="err-message-red"><?php if(!empty($publisher_err)){echo $publisher_err;}else echo "<p></p>" ?></div></div>
		<input type="text" name="publisher" class="big-input-box" value="<?php if(!empty($publisher)){echo $publisher;}?>"><br><br>
		
		<div class="form-descriptor">Game Complexity <div class="err-message-red"><?php if(!empty($complexity_err)){echo $complexity_err;}else echo "<p></p>" ?></div></div>

		<label class="container">
			<input type="radio" name="complexity" class="go-away"  value="5" <?php if (isset($complexity) && $complexity=="5") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			5 - Extremely complex
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away" value="4" <?php if (isset($complexity) && $complexity=="4") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			4
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away"value="3" <?php if (isset($complexity) && $complexity=="3") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			3 - Average complexity
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away" value="2" <?php if (isset($complexity) && $complexity=="2") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			2
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away" value="1" <?php if (isset($complexity) && $complexity=="1") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			1 - Very simple
		</label><br>
		
		<div class="form-descriptor">Player Count <div class="err-message-red"><?php if(!empty($player_count_err)){echo $player_count_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Enter the player count as a range. (Ex. 2-6, 3-7, 2-2)</p>
		<input type="text" name="low_player" class="range-input-box" value="<?php if(!empty($low_player)){echo $low_player;}?>"><span> - </span>
		<input type="text" name="high_player" class="range-input-box" value="<?php if(!empty($high_player)){echo $high_player;}?>"><br><br>
		
		<div class="form-descriptor">Game Length <div class="err-message-red"><?php if(!empty($length_err)){echo $length_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Enter the estimated play time in minutes as a range. (Ex. 30-40, 10-10, 60-120)</p>
		<input type="text" name="low_length" class="range-input-box" value="<?php if(!empty($low_length)){echo $low_length;}?>"><span> - </span>
		<input type="text" name="high_length" class="range-input-box" value="<?php if(!empty($high_length)){echo $high_length;}?>"><br><br>
		
		<div class="form-descriptor">Type of game <div class="err-message-red"><?php if(!empty($game_type_err)){echo $game_type_err;}else echo "<p></p>" ?></div></div>
		
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Escape Room" <?php if (in_array("Escape Room", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Escape Room - Solve puzzles within a certain time.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Cooperative" <?php if (in_array("Cooperative", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Cooperative - Working as a group to achieve a goal.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Hidden Role" <?php if (in_array("Hidden Role", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Hidden Role - Each person is assigned a secret role and you must deduce the identity of others in order to complete an objective.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Area Control" <?php if (in_array("Area Control", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Area Control - Gain control of regions on a central board.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Deck Building" <?php if (in_array("Deck Building", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Deck Building - Build a deck of cards.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Card Drafting" <?php if (in_array("Card Drafting", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Card Drafting - Take a card, pass along the rest.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Dungeon Crawler" <?php if (in_array("Dungeon Crawler", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Dungeon Crawler - Create a character and roll play your way through an epic adventure.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Roll and Write" <?php if (in_array("Roll and Write", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Roll and Write - Roll dice and write something based on the outcome.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Worker Placement" <?php if (in_array("Worker Placement", $checked_items)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			Worker Placement - Allocate workers to various actions.
		</label>
		<label class="container">
			<input type="checkbox" name="category[]" class="go-away" value="Other" <?php if(isset($other_category)){echo "checked";}?>>
			<span class="checkbox1"></span><span class="checkbox2"><i class="fa-solid fa-square-check fa-1x"></i></span>
			<input type="text" name="other_category" class="half-input-box" value="<?php if(isset($other_category)){echo $other_category;}?>">
		</label><br>
		
		<div class="form-descriptor">Keywords (optional) <div class="err-message-red"><?php if(!empty($keyword_err)){echo $keyword_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Enter keywords separated by a comma. (Ex. Party, Oranges, Canada)</p>
		<input type="text" name="keywords" class="big-input-box" value="<?php if(!empty($keywords)){echo $keywords;}?>"><br><br>
		
		<div class="form-descriptor">Image (optional) <div class="err-message-red"><?php if(!empty($image_err)){echo $image_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">For the best quality, make your image a square.</p>
		<div class="button-wrap">
			<label class="choose-file-button" for="uploadfile">Upload File</label>
			<input name="uploadfile" id="uploadfile" type="file">
		</div><br>
		
		<br><input class="button" type="submit" value="Submit"><br><br><br>
		
	</form>
</div>

</body>
</html>