<?php
function connect(){ //creates a connection to the database
	$dbhost = "localhost";
	$dbuser = "root";
	$dbpass = "";
	$dbname = "game_shelf";
	$result = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
	return $result; //equivalent to $con
}

function test_input($data) { //prevents sql injection
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function valid_username($user_name){ //checks for valid usernames
	if (!empty($user_name)){
		if (strlen($user_name) < 16){
			if (preg_match("/^[0-9a-zA-Z-' ]*$/",$user_name)) {
				$query = "SELECT user_name FROM users WHERE user_name='$user_name';";
				$result = mysqli_query(connect(), $query);
				if (mysqli_num_rows($result)==0){
					return "Ok";
				}else{ //Hi and hi can't both be users because SQL can't tell them apart
					return "That username is already in use";
				}
			} else{
				return "Only letters and numbers are allowed";
			}
		} else{
			return "Username must be under 16 characters.";
		}
	}
	return "Username is required";
}
function valid_password($password){ //checks for valid passwords
	if (!empty($password)){
		if (strlen($password) < 16){
			return "Ok";
		} else{
			return "Password must be under 16 characters";
		}
	}
	return "Password is required";
}

function valid_email($email) { //checks for valid emails
	if (!empty($email)){
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$query = "SELECT user_id FROM users WHERE email='$email';";
			$result = mysqli_query(connect(), $query);
			if (mysqli_num_rows($result)==0){
				return "Ok";
			}else{
				return "That email address is already in use";
			}
		} else{
			return "Not a valid email address";
		}
	}
	return "Email is required";
}

function get_personnal_info ($user_id){ //returns an array(user_name, email, num_games, bio)
	$query = "SELECT user_name, email, num_games, bio, profile_picture FROM users WHERE user_id = '$user_id'";
	$result = mysqli_fetch_assoc(mysqli_query(connect(), $query));
	return $result;
}

function style_input($game_name){ //styles input by capitalizing and trimming whitespace
	$game_name = trim($game_name, " ");//get rid of spaces
	$game_name = strtolower($game_name);
	$game_name = substr_replace($game_name, strtoupper(substr($game_name, 0, 1)), 0, 1);
	for ($i = 1; $i < strlen($game_name); $i++){
		$prev_char = substr($game_name, $i - 1, 1);
	if ($prev_char === " " || $prev_char === "-"){
			$game_name = substr_replace($game_name, strtoupper(substr($game_name, $i, 1)), $i, 1);
		}
	}
    return $game_name;
}

function ensure_login($user_id){ //sends to login if user not logged in
	if(!isset($user_id)){
		header("Location: login.php");
	}
}

function str_to_stylized_arr($str){ //splits a string into an array using commas as separators 
	$arr = explode(",", $str);
    $len = sizeof($arr);
    for($i = 0; $i < $len; $i=$i+1){
    	$arr[$i] = style_input($arr[$i]); //styles words
    }
    return $arr;
}

function get_my_games($my_id){ //returns an array of games which belong to a specific player
	$query = "SELECT games FROM games_by_user WHERE user_id='$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query(connect(), $query));
	$result = $result['games'];
	$arr = explode(",", $result);
	return $arr;
}

function add_existing_game($game_id, $my_id){//adds a game to a user's gameshelf, increases owned games, and increases game popularity
	$con = connect();
	//adds the current game_id to a users game shelf
	$query = "SELECT games FROM games_by_user WHERE user_id = '$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query)); 
				
	$new_result = $result['games'] . "," . $game_id;
	$new_result = trim($new_result, ",");
	$query = "UPDATE games_by_user SET games='$new_result' WHERE user_id = '$my_id'";
	mysqli_query($con, $query);
		
	//increases number of owned games by one
	$query = "SELECT num_games FROM users WHERE user_id='$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$new_result = $result['num_games'] + 1;
	$query = "UPDATE users SET num_games='$new_result' WHERE user_id = '$my_id'";
	mysqli_query($con, $query);
				
	//increases game popularity
	$query = "SELECT popularity FROM game_library WHERE game_id = '$game_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$new_result = $result['popularity'] + 1;
	$query = "UPDATE game_library SET popularity='$new_result' WHERE game_id='$game_id'";
	mysqli_query($con, $query);
}

function remove_game($game_id, $my_id){ //removes a game from a user's shelf, decreases owned games and decreases game popularity
	$con = connect();
	
	//removes the current game_id to a users game shelf
	$my_games = get_my_games($my_id);
	$key = array_search($game_id, $my_games);
	unset($my_games[$key]);
	$new_result = "";
	
	foreach($my_games as $game){
		$new_result = $new_result . $game . ",";
	}
	$new_result = trim($new_result, ",");
	
	$query = "UPDATE games_by_user SET games='$new_result' WHERE user_id = '$my_id'";
	mysqli_query($con, $query);
	
	//decrease number of owned games by one
	$query = "SELECT num_games FROM users WHERE user_id='$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$new_result = $result['num_games'] - 1;
	$query = "UPDATE users SET num_games='$new_result' WHERE user_id = '$my_id'";
	mysqli_query($con, $query);
				
	//decrease game popularity
	$query = "SELECT popularity FROM game_library WHERE game_id = '$game_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$new_result = $result['popularity'] - 1;
	$query = "UPDATE game_library SET popularity='$new_result' WHERE game_id='$game_id'";
	mysqli_query($con, $query);
}

function get_popularity_chart(){//returns an array where the index is the number shown in the popularity column and the value is the popularity index (1st, 3rd, 5th) ex: arr(1, 1, 2, 2, 3) -> arr(1=>4, 2=>2, 3=>1)
	$con = connect();
	
	//setting up popularity array
	$query = "SELECT popularity FROM game_library";
	$data = mysqli_query($con, $query);
	$individual_popularity = array();
	
	while($row = mysqli_fetch_assoc($data)){
		array_push($individual_popularity, $row['popularity']);
	}

	sort($individual_popularity);
	
	$total = sizeof($individual_popularity);
	$max = $individual_popularity[$total-1];
	$result = array();
	$last = -1;
	
	for($i = $total-1; $i > -1; $i--){
		if ($individual_popularity[$i] != $last){
			$result[$individual_popularity[$i]] = ($total) - $i;
			$last = $individual_popularity[$i];
		}
	}

	return $result;
}

function get_friends($my_id){ //returns an array of friends
	$query = "SELECT friends from friends WHERE user_id = '$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query(connect(), $query));
	if (strlen($result['friends']) > 0){
		$result = $result['friends'];
		$result = explode(",", $result);
		return $result;
	}
	return array();
}

function get_user_data_from_users($user_list){//returns an array containing arrays of user data. The first element in the array to be returned [0] refers to the first user_id in $user_list 
$con = connect();
$query = "SELECT user_id, user_name, num_games, bio, profile_picture FROM users";
$data = mysqli_query($con, $query);
$values = array();

while($row = mysqli_fetch_assoc($data)){
	if(in_array($row['user_id'], $user_list)){
		array_push($values, $row);
	}
}
return $values;
}

function user_name_list(){//returns a list of all username
	$con = connect();
	$query = "SELECT user_name FROM users";
	$result = mysqli_query($con, $query);
	$user_name_list = array();
	while ($row = mysqli_fetch_assoc($result)){
		array_push($user_name_list, $row['user_name']);
	}
	return $user_name_list;
}

function convert_arr_to_str($arr){ //converts an array to a string separated by commas
	$new_arr = "";
	foreach ($arr as $elem){
		$new_arr = $new_arr . "," . $elem;
	}
	$new_arr = trim($new_arr, ",");
	return $new_arr;
}

function send_friend_request($my_id, $friend_user_name) {//sends a friend request or output error
	$err_message = "Done";
	$con = connect();
	$query = "SELECT user_id FROM users WHERE user_name = '$friend_user_name'";
	$result = mysqli_query($con, $query);
	if (mysqli_num_rows($result) < 1){
		$err_message = "This user does not exist.";
	} elseif(!in_array($friend_user_name, user_name_list())){ //for case sensitivity
		$err_message = "This user does not exist.";
	} else{ 
		$result = mysqli_fetch_assoc($result);
		$friend_id = $result['user_id'];
		if(in_array($friend_id, get_friends($my_id))){ //already friends
			$err_message = "This user is already your friend.";	
		} else{
			$query = "SELECT pending_requests, incoming_requests FROM friends WHERE user_id = '$my_id'";
			$result = mysqli_fetch_assoc(mysqli_query($con, $query));
			$pending_requests = explode(",", $result['pending_requests']);
			$incoming_requests = explode(",", $result['incoming_requests']);
			if(in_array($friend_id, $pending_requests)){ //already sent
				$err_message = "You have already sent this person a friend request.";
			} elseif(in_array($friend_id, $incoming_requests)){ //already trying to be friends
				$err_message = "This person has already sent you a friend request.";
			} else{ //send the friend request
				//put friend into your pending
				array_push($pending_requests, $friend_id);
				$pending_requests = convert_arr_to_str($pending_requests);
				$query = "UPDATE friends SET pending_requests='$pending_requests' WHERE user_id='$my_id'";
				mysqli_query($con, $query);
				
				//put you into the other user's incoming_requests
				$query = "SELECT incoming_requests FROM friends WHERE user_id = '$friend_id'";
				$result = mysqli_fetch_assoc(mysqli_query($con, $query));
				$new_result = $result['incoming_requests'] . "," . $my_id;
				$new_result = trim($new_result, ",");
				$query = "UPDATE friends SET incoming_requests='$new_result' WHERE user_id='$friend_id'";
				mysqli_query($con, $query);
			}
		}
	}
	return $err_message;
}

function remove_friend_request($my_id, $friend_id){ //takes the pending request from the friend and the incoming request from my_id and removes the associated id
	$con = connect();
	$query = "SELECT incoming_requests FROM friends WHERE user_id='$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$result = explode(",", $result['incoming_requests']);
	$new_result = array();
	foreach($result as $req){
		if($req != $friend_id){
			array_push($new_result, $req);
		}
	}
	$new_result = convert_arr_to_str($new_result);
	$query = "UPDATE friends SET incoming_requests='$new_result' WHERE user_id='$my_id'";
	mysqli_query($con, $query);
	
	$query = "SELECT pending_requests FROM friends WHERE user_id='$friend_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$result = explode(",", $result['pending_requests']);
	$new_result = array();
	foreach($result as $pen){
		if($pen != $my_id){
			array_push($new_result, $pen);
		}
	}
	$new_result = convert_arr_to_str($new_result);
	$query = "UPDATE friends SET pending_requests='$new_result' WHERE user_id = '$friend_id'";
	mysqli_query($con, $query);
}

function add_friend($my_id, $friend_id){ //adds $friend_id to the friend list of my_id
	$con=connect();
	$query = "SELECT friends FROM friends WHERE user_id='$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$new_result = $result['friends'] . "," . $friend_id;
	$new_result = trim($new_result, ",");
	
	$query = "UPDATE friends SET friends='$new_result' WHERE user_id='$my_id'";
	mysqli_query($con, $query);
}

function incoming_request($my_id){ //returns true if a user has an incoming friend request
	$con = connect();
	$query = "SELECT incoming_requests FROM friends WHERE user_id='$my_id'";
	$result = mysqli_fetch_assoc(mysqli_query($con, $query));
	$result = $result['incoming_requests'];
	return strlen($result) > 0;
}

function get_all_mechanisms(){ //returns an array containing all the game mechanisms
	$con = connect();
	$types = array("Escape Room", "Cooperative", "Hidden Role", "Area Control", "Deck Building", "Card Drafting", "Dungeon Crawler", "Roll and Write", "Worker Placement");
	$query = "SELECT category FROM game_library";
	$result = mysqli_query($con, $query);
	while($row = mysqli_fetch_assoc($result)){
		$row = explode(",", $row['category']);
		foreach ($row as $elem){
			if(!in_array($elem, $types)){
				array_push($types, $elem);
			}
		}
	}
	return $types;
}

function output($arr){//echoes an array
	foreach($arr as $el){
		echo $el . "<br>";
	}
}

function get_game_data($arr){ //gets all game data for an array of game_ids. The first game_id of the array corresponds to the first element in the output.
	$con = connect();
	$query = "SELECT * FROM game_library";
	$result = mysqli_query($con, $query);
	$game_data = array();
	while($row = mysqli_fetch_assoc($result)){
		if(in_array($row['game_id'], $arr)){
			array_push($game_data, $row);
		}
	}
	return $game_data;
}

function alphabetical_comparison($x, $y){ //a-z
	return strcmp($x['game_name'], $y['game_name']);
}

function complexity_comparison($x, $y){ //lowest to highest complexity
	return $x['complexity'] > $y['complexity'];
}

function popularity_comparison($x, $y){ //highest to lowest popularity
	return $x['popularity'] < $y['popularity'];
}

function time_comparison($x, $y){ //shortest to longest time
	$x_time = explode(",", $x['length']);
	$x_time = (int)$x_time[0] + (int)$x_time[1];
	$y_time = explode(",", $y['length']);
	$y_time = (int)$y_time[0] + (int)$y_time[1];
	return $x_time > $y_time;
}

function get_corresponding_user_id($user_name){ //gets the user_id corresponding to a username
	$con = connect();
	$query = "SELECT user_id FROM users WHERE user_name='$user_name'";
	$result = mysqli_query($con, $query);
	$result = mysqli_fetch_assoc($result);
	$result = $result['user_id'];
	return $result;
}

?>