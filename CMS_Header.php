<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
  <!-- stylesheet -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/CMS_styles.css"><br>
  <script>
    var PageIndex = <?php echo $PageIndex; ?>;
  </script>
</head>

<body>
<header>
  <nav class="navbar navbar-expand-md fixed-top bg-secondary navbar-dark" role="navigation">
    <div class="navbar-brand">Content Management System</div>
    <div class="container">
      <ul class="navbar-nav mr-auto" id="mainmenu">
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item"><a href="CMS_Login.php?action=logout" class="nav-link">LOGOUT</a></li>
      </ul>
    </div><!-- container -->
  </nav>
  <div style="height:50px"></div>
</header>