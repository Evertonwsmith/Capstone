<?php
include_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';
require_once '../src/user.php';

if (session_status() == PHP_SESSION_NONE) {
  //Start session
  session_start();
}

if (isset($_SESSION['user_email'])) {
  $user_email = $_SESSION['user_email'];
  $user = new user($user_email);
} else {
  //something
}
?>
<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link href="https://fonts.googleapis.com/css?family=Ubuntu+Condensed&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="../src/stylesheets/sleepovers.css">
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> -->
  <title><?php if (isset($page_title)) {
            echo $page_title;
          } else {
            echo 'Sleepovers';
          } ?>
  </title>
  <meta name="viewport" content="width=device-width, initial-scale=0.86, maximum-scale=3.0, minimum-scale=0.86">
</head>

<body>
  <div id='body-container'>
    <div id='content-container' class='container'>
      <div id='main'>
        <div class="navbar-container">
          <nav class="navbar navbar-expand-lg navbar-dark" style="background-color:black;">
            <a class="navbar-brand" href="home.php">Sleepovers</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav mr-auto">
                <?php
                if (isset($user) && ($user->is_admin() || $user->is_staff())) {
                  echo "<li class='nav-item header'>
                  <a class='nav-link' href='admin.php'>Admin<span class='sr-only'>(current)</span></a>
                  </li>";
                }
                ?>
                <li class="nav-item header">
                  <a class="nav-link" href="store.php">Store<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item header">
                  <a class="nav-link" href="artists.php">Artists<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item header">
                  <a class="nav-link" href="venues.php">Venues<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item header">
                  <a class="nav-link" href="blog.php">Blog<span class="sr-only">(current)</span></a>
                </li>
              </ul>
              <ul class="navbar-nav ml-auto">
                <li class="nav-item header">
                  <?php
                  include "login_function.php"
                  ?>
                </li>
              </ul>
            </div>
          </nav>
        </div>
        <br>