<?php
//verify the session, if there is no session it will redirect to login page.
session_start();
if(!isset($_SESSION["username"])) {
	header("location:CMS_Login.php");
	die();
}
$nowtime = time();
if($nowtime-$_SESSION["last_action"]>= 1800){
	unset($_SESSION["username"]);
	unset($_SESSION['level']);
	unset($_SESSION['last_action']);
	header("location:CMS_Login.php");
	die();
}
$_SESSION["last_action"] = $nowtime;
?>