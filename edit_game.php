<?php
session_start(); //starts or continues a session

include("functions.php");

ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();//creates connection

$game_data = get_game_data(array($_SESSION['clicked']));
$game_data = $game_data[0];
$all_game_types = get_all_mechanisms();

$creator_err = $publisher_err = $complexity_err = $player_count_err = $length_err = $type_err = $keyword_err = $image_err = ""; //setting error messages
$creator = $game_data['creator'];
$publisher = $game_data['publisher'];;
$complexity = $game_data['complexity'];
$player_count = explode(",", $game_data['player_count']);
$low_player = $player_count[0];
$high_player= $player_count[1];
$game_length = explode(",", $game_data['length']);
$low_length = $game_length[0];
$high_length= $game_length[1];
$existing_game_types = explode(",", $game_data['category']);
$keywords = $game_data['keywords'];

if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
	if ($_POST['done'] == "Update"){
		$creator = test_input($_POST['creator']);
		$publisher = test_input($_POST['publisher']);
		if (in_array("complexity", array_keys($_POST))){ //if complexity was selected
			$complexity = (int)$_POST['complexity'];
		}
		$low_player = test_input($_POST['low_player']);
		$high_player = test_input($_POST['high_player']);
		$low_length = test_input($_POST['low_length']);
		$high_length = test_input($_POST['high_length']);
		if (in_array("game_type", array_keys($_POST))){ //if category was selected
			$existing_game_types = array(); //resets to empty in case something was unchecked
			foreach ($_POST['game_type'] as $elem){//adds all checked items.
				array_push($existing_game_types, $elem);
			}
		} else{
			$existing_game_types = array();
		}
		output($existing_game_types);///test
		$other_category = style_input(test_input($_POST['other_category']));
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
		if(empty($low_player) || empty($high_player)){
			$player_count_err = "Both sides of the range are required.";
			$all_ok = false;
		}elseif (!preg_match("/^[0-9]*$/",$low_player) || !preg_match("/^[0-9]*$/",$high_player)){
			$player_count_err = "Range must be numeric.";
			$all_ok = false;
		} elseif ((int)$low_player > (int)$high_player){
			$player_count_err = "Please enter a valid range.";
			$all_ok = false;
		}
		if(empty($low_length) || empty($high_length)){
			$length_err = "Both sides of the range are required.";
			$all_ok = false;
		}elseif (!preg_match("/^[0-9]*$/",$low_length) || !preg_match("/^[0-9]*$/",$high_length)){
			$length_err = "Range must be numeric.";
			$all_ok = false;
		} elseif ((int)$low_length > (int)$high_length){
			$length_err = "Please enter a valid range.";
			$all_ok = false;
		}
		if(!empty($other_category) && sizeof(explode(",",$other_category)) > 1){
			$type_err = "Please enter a single type of game in the text field.";
			$all_ok = false;
		}
		if(sizeof($existing_game_types) == 0 && empty($other_category)){
			$type_err = "A type of game is required.";
			$all_ok = false;
		}
		if(strlen($_FILES["uploadfile"]["name"]) > 0){//check image exists
			$filename = $_FILES["uploadfile"]["name"];
			$sql="SELECT game_id FROM game_library WHERE filename='$filename'";
			$result = mysqli_query($con, $sql);
			if (mysqli_num_rows($result)>0){
				$image_err = "A file with this name already exists. Please rename your file.";
				$all_ok = false;
			} else{
				$set_new_image = true;
			}
		} else{
			$set_new_image = false;
		}
		
		if($all_ok){
			$creator = style_input($creator);
			$publisher = style_input($publisher);
			$player_count = $low_player . "," . $high_player;
			$game_length = $low_length . "," . $high_length;
			
			if(!empty($other_category)){
				array_push($existing_game_types, $other_category);
			}
			$game_types = convert_arr_to_str($existing_game_types);
			$game_id = $_SESSION['clicked'];
			
			$individual_keywords = str_to_stylized_arr($keywords);
			$keywords = convert_arr_to_str($individual_keywords);
			
			$query = "UPDATE game_library SET creator='$creator', publisher='$publisher', complexity='$complexity', player_count='$player_count', length = '$game_length', category = '$game_types', keywords = '$keywords' WHERE game_id = '$game_id'";
			mysqli_query($con, $query);
			
			if ($set_new_image){
				if ($game_data['filename'] != "default_image.png"){ //don't delete the default!
					unlink("Game Images/" . $game_data['filename']);
				}
				//update database
				$query = "UPDATE game_library SET filename='$filename' WHERE game_id='$game_id'";
				mysqli_query($con, $query);
				
				//add to images folder
				$tempname = $_FILES["uploadfile"]["tmp_name"];
				$folder = "Game Images/".$filename;
				move_uploaded_file($tempname, $folder);
			}
			$_SESSION['message'] = $game_data['game_name'] . " was updated successfully.";
			header("Location: confirmation.php"); //temp
		}
	} else{ //cancel was pressed
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
		
	<title>Game Shelf - Edit Game</title>
		
	<link rel="icon" type="image/jpg" href="Visual Aids\die_favicon_2.ico">
	<link rel="stylesheet" href="stylesheet.css">
		
	<script src="https://kit.fontawesome.com/51b60ac200.js" crossorigin="anonymous"></script>
</head>
<body class="center-background" style="background-image: url('Visual Aids/scorecharts.jpg');">
<ul>
    <li><a href="index.php">Home</a></li>
    <li class="dropdown">
		<a href="javascript:void(0)" class="dropbtn">My Shelf</a>
        <div class="dropdown-content">
            <a href="<?php if(isset($_SESSION['user_id'])){echo "view_shelf.php";}else{echo "login.php";} ?>">View Shelf</a> 
			<a href="<?php if(isset($_SESSION['user_id'])){echo "add_game1.php";}else{echo "login.php";} ?>">Add Game</a>
        </div>
	</li>
    <li class="dropdown active">
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
		<p class="big-form-header">Update Game Information</p>
		<p class="big-form-header" style="font-size:30px; font-style:italic; font-weight: normal;">Edit the information for <?php echo $game_data['game_name'];?> in the designated field(s) below.</p><br>
		
		<div class="form-descriptor">Creator (optional)<div class="err-message-red"><?php if(!empty($creator_err)){echo $creator_err;}else echo "<p></p>" ?></div></div>
		<input type="text" name="creator" class="big-input-box" value="<?php echo $creator;?>"><br><br>
		
		<div class="form-descriptor">Publisher (optional)<div class="err-message-red"><?php if(!empty($publisher_err)){echo $publisher_err;}else echo "<p></p>" ?></div></div>
		<input type="text" name="publisher" class="big-input-box" value="<?php echo $publisher;?>"><br><br>
		
		<div class="form-descriptor">Game Complexity (optional)<div class="err-message-red"><?php if(!empty($complexity_err)){echo $complexity_err;}else echo "<p></p>" ?></div></div>

		<label class="container">
			<input type="radio" name="complexity" class="go-away"  value="5" <?php if($complexity=="5") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			5 - Extremely complex
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away" value="4" <?php if($complexity=="4") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			4
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away"value="3" <?php if($complexity=="3") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			3 - Average complexity
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away" value="2" <?php if($complexity=="2") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			2
		</label>
		<label class="container">
			<input type="radio" name="complexity" class="go-away" value="1" <?php if($complexity=="1") echo "checked";?>>
			<span class="radio1"></span><span class="radio2"></span><span class="radio3"></span>
			1 - Very simple
		</label><br>
		
		<div class="form-descriptor">Player Count (optional)<div class="err-message-red"><?php if(!empty($player_count_err)){echo $player_count_err;}?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Enter the player count as a range. (Ex. 2-6, 3-7, 2-2)</p>
		<input type="text" name="low_player" class="range-input-box" value="<?php echo $low_player;?>"<span> - </span>
		<input type="text" name="high_player" class="range-input-box" value="<?php echo $high_player;?>"><br><br>
		
		<div class="form-descriptor">Game Length (optional)<div class="err-message-red"><?php if(!empty($length_err)){echo $length_err;}?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Enter the estimated play time in minutes as a range. (Ex. 30-40, 10-10, 60-120)</p>
		<input type="text" name="low_length" class="range-input-box" value="<?php if(!empty($low_length)){echo $low_length;}?>"><span> - </span>
		<input type="text" name="high_length" class="range-input-box" value="<?php if(!empty($high_length)){echo $high_length;}?>"><br><br>
		
		<div class="form-descriptor">Type of Game (optional)<div class="err-message-red"><?php if(!empty($type_err)){echo $type_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Hold down the Ctrl (windows) or Command (Mac) button to select multiple options.</p>
		<select name="game_type[]" multiple class="multi-select" size="5" >
			<?php foreach ($all_game_types as $type){
				echo "<option style='font-size: 24px; padding-left: 10px;' value='" . $type . "'";
				if(in_array($type, $existing_game_types)){
					echo "selected";
				}
				echo ">" . $type . "</option>";
			} ?>
		</select><br>
		
		<input type="text" name="other_category" class="half-input-box" style="margin-top:15px; border-radius: 10px; padding-left: 15px;" value="<?php if(isset($other_category)){echo $other_category;}?>" placeholder="Other game type"><br><br>
		
		<div class="form-descriptor">Keywords (optional) <div class="err-message-red"><?php if(!empty($keyword_err)){echo $keyword_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">Enter keywords separated by a comma. (Ex. Party, Oranges, Canada)</p>
		<input type="text" name="keywords" class="big-input-box" value="<?php if(!empty($keywords)){echo $keywords;}?>"><br><br>
		
		<div class="form-descriptor">Image (optional) <div class="err-message-red"><?php if(!empty($image_err)){echo $image_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">For the best quality, make your image a square.</p>
		<div class="button-wrap">
			<label class="choose-file-button" for="uploadfile">Upload File</label>
			<input name="uploadfile" id="uploadfile" type="file">
		</div><br>
		
		<br><input class="button" type="submit" name="done" value="Update"><input class="button" type="submit" name="done" value="Cancel" style="margin-right: 3%;"><br><br>
		
	</form>
</div>

</body>
</html>