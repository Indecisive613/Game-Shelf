<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "game_shelf";

$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname); //creates a connection to the database

if (!$con){
	die("Failed to connect");
}
?>