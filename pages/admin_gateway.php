<?php
require_once '../src/user.php';
if (session_status() == PHP_SESSION_NONE) {
    //Start session
    session_start();
}

//Get user information
if (isset($_SESSION['user_email'])) {
    //Verify user is allowed to be here
    $user = new user($_SESSION['user_email']);
    $is_admin_or_staff = $user->is_staff() || $user->is_admin();
    if (!$is_admin_or_staff) {
        exit("<br><h3 class='error'>You do not have the required permissions to view this content.</h3>");
    } else {
        //Get user current permission level
        $permission_level = $user->get_permission_level();
    }
} else {
    exit("<br><h3 class='error'>You do not have the required permissions to view this content.</h3>");
}
