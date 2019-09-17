<?php
require_once '../src/product.php';

if (!isset($_GET['product_id'])) {
    header("location: admin.php?tab=product");
}

$page_title = "Product Preview";
include 'header.php';
include 'admin_gateway.php';
include 'item_contents.php';
include 'footer.php';
