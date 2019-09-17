<?php
require_once '../src/blogpost.php';

if (isset($_GET['blog_id'])) {
    $post = new blogpost($_GET['blog_id']);
    if (!($post->is_public())) {
        header("location: blog.php");
    }
} else {
    header("location: blog.php");
}

$page_title = "Sleepovers Blog";
include 'header.php';
include 'blog_post.php';
include 'footer.php';
