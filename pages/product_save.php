<?php
require_once '../src/product.php';
require_once '../src/my_sql.php';
include '../src/my_sql_cred.php';
include 'admin_gateway.php';

if (isset($_POST['product_id'])) {
    if ($_POST['product_id'] === "session" && isset($_SESSION['product_id'])) {
        $product_id = $_SESSION['product_id'];
    } else {
        $product_id = $_POST['product_id'];
    }
    $product = new product($product_id);
    $product->check_attr_update();
}
