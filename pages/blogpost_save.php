<?php
require_once '../src/blogpost.php';
require_once '../src/my_sql.php';
include '../src/my_sql_cred.php';
include 'admin_gateway.php';

if (isset($_POST['blog_id'])) {
    if ($_POST['blog_id'] === "session" && isset($_SESSION['blog_id'])) {
        $blog_id = $_SESSION['blog_id'];
    } else {
        $blog_id = $_POST['blog_id'];
    }
    $blog = new blogpost($blog_id);
    $blog->check_attr_update();
}
