<?php
session_start(); //starts or continues a session

include("functions.php");

ensure_login($_SESSION['user_id']);//ensures logged in
$con = connect();//creates connection

$personnal_info = get_personnal_info($_SESSION['user_id']); 
$username_err = $email_err = $bio_err = $image_err = ""; //setting error messages

if($_SERVER['REQUEST_METHOD'] == "POST"){ //something was posted
	if ($_POST['done'] == "Update"){
		$user_name = test_input($_POST['user_name']);
		$email = test_input($_POST['email']);
		$bio = test_input($_POST['bio']);
		
		$all_ok = true;
		$set_new_image = false;
		
		if($user_name !== $personnal_info['user_name']){ //check if different
			$status = valid_username($user_name);
			if($status != "Ok"){
				$all_ok = false;
				$username_err = $status;
			}
		}
		
		if($email !== $personnal_info['email']){ //check if different
			$status = valid_email($email);
			if($status != "Ok"){
				$all_ok = false;
				$email_err = $status;
			}
		}
		
		if(strlen($bio) > 200){
			$all_ok = false;
			$bio_err = "Please keep your bio to 200 characters or less.";
		}
		
		if(strlen($_FILES["uploadfile"]["name"]) > 0){//check image exists
			$filename = $_FILES["uploadfile"]["name"];
			$sql="SELECT user_id FROM users WHERE profile_picture='$filename'";
			$result = mysqli_query($con, $sql);
			if (mysqli_num_rows($result)>0){
				$image_err = "A file with this name already exists. Please rename your file.";
				$all_ok = false;
			} else{
				$set_new_image = true;
			}
		}
		
		if($all_ok){
			$my_id = $_SESSION['user_id'];
			
			if ($set_new_image){
				if ($personnal_info['profile_picture'] != "angled_meeple.jpg"){ //don't delete the default!
					unlink("Profile Pictures/" . $personnal_info['profile_picture']);
				}
				//update database
				$query = "UPDATE users SET profile_picture='$filename' WHERE user_id='$my_id'";
				mysqli_query($con, $query);
				
				//add to images folder
				$tempname = $_FILES["uploadfile"]["tmp_name"];
				$folder = "Profile Pictures/".$filename;
				move_uploaded_file($tempname, $folder);
			}
			//update user info
			$query = "UPDATE users SET user_name='$user_name', email='$email', bio='$bio' WHERE user_id='$my_id'";
			mysqli_query($con, $query);
			
			header("Location: profile.php");
		}
	} else{ //cancel was pressed
		header("Location: profile.php");
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

<div class="big-box">
	<form method="post" enctype="multipart/form-data">
		<p class="big-form-header">Update User Information</p>
		<p class="big-form-header" style="font-size:30px; font-style:italic; font-weight: normal;">Edit your user information in the designated field(s) below.</p><br>
		
		<div class="form-descriptor">Username (optional)<div class="err-message-red"><?php if(!empty($username_err)){echo $username_err;}else echo "<p></p>" ?></div></div>
		<input type="text" name="user_name" class="big-input-box" value="<?php if(!empty($user_name)){echo $user_name;}else{echo $personnal_info['user_name'];}?>"><br><br>
		
		<div class="form-descriptor">Email Address (optional) <div class="err-message-red"><?php if(!empty($email_err)){echo $email_err;}else echo "<p></p>" ?></div></div>
		<input type="text" name="email" class="big-input-box" value="<?php if(!empty($email)){echo $email;}else{echo $personnal_info['email'];}?>"><br><br>
		
		<div class="form-descriptor">Bio (optional) <div class="err-message-red"><?php if(!empty($bio_err)){echo $bio_err;}else echo "<p></p>"?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">What's your favourite type of game? What got you interested in board games?
		
		</p>
		<textarea name="bio" rows="4" style="width: 98%; padding: 1%;";><?php echo $personnal_info['bio'];?></textarea><br><br>
			
		<div class="form-descriptor">Profile Picture (optional) <div class="err-message-red"><?php if(!empty($image_err)){echo $image_err;}else echo "<p></p>" ?></div></div>
		<p style="font-size: 18px;padding-bottom: 5px;">For the best quality, make your image a square.</p>
		<div class="button-wrap">
			<label class="choose-file-button" for="uploadfile">Upload File</label>
			<input name="uploadfile" id="uploadfile" type="file">
		</div><br>
		
		<br><input class="button" type="submit" name="done" value="Update"><input class="button" type="submit" name="done" value="Cancel" style="margin-right: 3%;"><br><br><br>
		
	</form>
</div>

</body>
</html>