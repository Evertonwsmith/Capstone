<?php
require_once '../src/user.php';
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';

//Verify user permissions
include_once 'admin_gateway.php';

if (isset($_POST['user_email'])) {
    // set user
    $user = new user($_POST['user_email']);
    // check_attr_update
    $user->check_attr_update($permission_level);
}
