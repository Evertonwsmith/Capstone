<?php
require_once '../src/blogpost.php';

if (isset($_GET['blog_id'])) {
    $post = new blogpost($_GET['blog_id']);
} else {
    header("location: admin.php?tab=blogpost");
}

$page_title = "Sleepovers Blog Preview";
include 'header.php';
include 'admin_gateway.php';
include 'blog_post.php';
include 'footer.php';
