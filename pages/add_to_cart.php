<?php
session_start();
require_once '../src/my_sql_cred.php';
require_once '../src/my_sql.php';
require_once '../src/cart.php';

if (isset($_SESSION['user_email'], $_POST['quantity'], $_POST['product_id'], $_POST['media_group_id'])) {
    $user_cart = new cart($_SESSION['user_email']);
    $result = $user_cart->add_cart_item($_POST['product_id'], $_POST['quantity'], $_POST['media_group_id']);
    if ($result) {
        unset($_SESSION['quantity']);
        unset($_SESSION['media_group_id']);
        unset($_SESSION['product_id']);
        exit("success");
    } else {
        exit("Unable to add item to cart.");
    }
} else {
    die("There is no email set. Please log in or create an account.");
}
