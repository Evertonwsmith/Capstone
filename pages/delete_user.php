<?php
require_once '../src/my_sql.php';
require_once '../src/my_sql_cred.php';
require_once '../src/user.php';
include 'admin_gateway.php';

if (!isset($_POST['user_detail_email'])) {
    header("admin.php?tab='useraccount'");
}

$user = new user($_POST['user_detail_email']);

$user->delete();
$user->ban();
exit();
