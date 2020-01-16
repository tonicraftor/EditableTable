<?php
  include "CMS_SessionCheck.php";
  include "config.php";
  $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
  $sql = "SELECT * FROM table_info WHERE auth_view>={$_SESSION['level']}";//  
  $result = mysqli_query($db,$sql);
  if($result){
    $tableinfo = mysqli_fetch_all($result,MYSQLI_ASSOC);
    mysqli_free_result($result);
    foreach($tableinfo as $info){
      $counts[$info['name']]=$info['count'];
    }
  }
?>
<?php
  $PageIndex = 0;
  include("CMS_Header.php");
?>

<div class="container pt-5 my-5">
  <h3>
    Welcome,&nbsp;<?php echo "{$_SESSION['username']}(Level {$_SESSION['level']})" ?>!
    <a href="CMS_Login.php?action=logout">Click Here to Log Out</a>.
  </h3>
</div>
<div class="container pt-5 my-5">
  <a href="CMS_Photos.php"><h4>You have <?php if(isset($counts['photo_list']))echo $counts['photo_list']; ?> photos.</h4></a>
  <a href="CMS_Menus.php"><h4>You have <?php if(isset($counts['menus']))echo $counts['menus']; ?> menus.</h4></a>
</div>
<div class="CMSTblWrap" tbl-name="photo_list" max-row="2" tab-pages="2" edit-type="window">
</div>

<div class="CMSTblWrap" tbl-name="links">
</div>

<div class="CMSTblWrap" tbl-name="menus" max-row="5" tab-pages="3" edit-type="window">
</div>

<div class="CMSTblWrap" tbl-name="booking" edit-type="window">
</div>

<div class="CMSTblWrap" tbl-name="subscribe">
</div>

<div class="CMSTblWrap" tbl-name="comments">
</div>

<div class="CMSTblWrap" tbl-name="admin" edit-type="window">
</div>

<div class="CMSTblWrap" tbl-name="user">
</div>

<?php
  include("CMS_Footer.php");
?>