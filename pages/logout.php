<?php
session_start();
session_unset();
session_destroy();
echo "Succesful Logout";

header("location:home.php");

?>