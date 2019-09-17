<?php

require_once '../src/product.php';

if (isset($_GET['product_id']) || isset($_REQUEST['store_item'])) {
	$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : $_REQUEST['store_item'];
	$product = new product($product_id);
	if (!$product->is_public()) {
		header("location: store.php");
	}
} else {
	header("location: store.php");
}

$page_title = "Product Preview";
include 'header.php';
include 'item_contents.php';
include 'footer.php';
