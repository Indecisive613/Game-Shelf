<?php
session_start();

if(isset($_SESSION['user_id'])){
	unset($_SESSION['user_id']); //unsets the user information.
}
header("Location: login.php"); //redirects to login
die;
?>