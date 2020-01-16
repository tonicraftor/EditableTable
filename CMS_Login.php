<?php
//verify the session, if there is no session it will redirect to login page.
//after login,session starts.
session_start();
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$loginform = filter_input_array(INPUT_POST);
	include("config.php");
	$db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
	$username = mysqli_real_escape_string($db,$loginform['username']);
	
	$sql = "SELECT level,password FROM admin WHERE username = '$username'";
	$result = mysqli_query($db,$sql);
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$count = mysqli_num_rows($result);
	
	// If result matched $username, table row must be 1 row
	if($count == 1&&password_verify($loginform['password'],$row['password'])) {
		$nowdate = date("Y-m-d H:i:s");
		$sql = "UPDATE admin SET login_times = login_times+1, last_login = '$nowdate' WHERE username = '$username'";
		$result = mysqli_query($db,$sql);
		$_SESSION['username'] = $username;
		$_SESSION['level'] = $row['level'];
		$_SESSION['last_action'] = time();
		header("location: CMS_Home.php");
		die();
	}
	else{
	  $error = "Username or Password Invalid";
	}
}
else if(isset($_SESSION["username"])){
	//log out
	if(filter_input(INPUT_GET,"action")=="logout"){
		unset($_SESSION["username"]);
		unset($_SESSION['level']);
		unset($_SESSION['last_action']);
	}
	//redirect to CMS_Home.php
	else{
		header("location:CMS_Home.php");
		die();
	}
}
?>
<?php
  $page_title = "Administrator Login";
  $PageIndex = 0;
  include("CMS_Header.php");
?>

<div class="container text-center my-5">
  <span class="h1">Administrator Login</span>
</div>
  
<div class="container text-center">
  <span class="h4 text-danger">&nbsp;<?php if(isset($error))echo '* '.$error; ?></span>
</div>

<div class="container mt-3">
  <form method="POST" action="">
    <div class="form-group row w-50 mx-auto">
      <label for="usr" class="col-sm-2 col-form-label">Username:</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="usr" name="username">
      </div>
    </div>
    <div class="form-group row w-50 mx-auto">
      <label for="pwd" class="col-sm-2 col-form-label">Password:</label>
      <div class="col-sm-10">
        <input type="password" class="form-control" id="pwd" name="password">
      </div>
    </div>
    <div class="form-group w-25 text-center mx-auto">
      <button type="submit" class="btn btn-block btn-outline-dark">Login</button>
    </div>
  </form>
</div>

<?php
  include("CMS_Footer.php");
?>
