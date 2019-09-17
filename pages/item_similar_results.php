<?php
require_once '../src/page_manager.php';
require_once '../src/search_catalog.php';
require_once '../src/product.php';
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';
require_once '../src/user.php';

if (session_status() == PHP_SESSION_NONE) {
    //Start session
    session_start();
}

//Get user info
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    $user = new user($user_email);
    $is_artist = $user->is_artist() || $user->is_admin() || $user->is_staff();
} else {
    $user_email = null;
    $is_artist = false;
}

if (isset($_REQUEST['store_item'])) {
    $product_id = $_REQUEST['store_item'];
} elseif (isset($_REQUEST['product_id'])) {
    $product_id = $_REQUEST['product_id'];
} else {
    exit();
}

//Create page manager
$pm = new page_manager("", "0", "search_relevance", "3", "0", array("artist_only" => "0"));

//Define headers
$headers = ["search_relevance" => true, "price" => true, "name" => true];
$pm->col = isset($pm->col) ? $pm->col : "search_relevance";
$artist_only = isset($_POST['artist_only']) && $_POST['artist_only'];

//Get search results
$search_keys = search_catalog::get_search_keys();

//remove current product from results
if (is_array($search_keys) && in_array($product_id, $search_keys)) {
    $key = array_search($product_id, $search_keys);
    unset($search_keys[$key]);
}

// Get all sorted product ids
$products = product::get_all_ids($pm->col, $pm->asc, null, "isPublic='1'", null, $artist_only);
if ($artist_only) {
    $count = my_sql::select("count(product.productID) as total", "product RIGHT JOIN artistonlyproduct ON product.productID=artistonlyproduct.productID", "isPublic='1'");
} else {
    $count = my_sql::select("count(productID) as total", "product", "isPublic='1'");
}
$total_results = $count == false ? 0 : $count[0]['total'];

// Whether results should be kept in search relevance order
$search_order = $pm->col === "search_relevance";

//sort and cross filter the products with search results
$sorted = search_catalog::filter_sort_results($search_keys, $products, $search_order);
if ($sorted) {
    $products = $sorted;
    $total_results = count($products);
}

//Make the product objects
$displayed_products = array();
$relevance = 1;
$num = 0;
foreach ($products as $index => $id) {
    if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
        $product = new product($id);
        //Set the relevance
        $product->set_relevance($relevance);
        //add to array
        array_push($displayed_products, $product);
    }
    $relevance++;
    $num++;
}

// echo the page buttons
$page_buttons = $pm->get_page_buttons($total_results);

//Echo post container
echo "<div class='row'>";

// output data of each product
$count = 0;
foreach ($displayed_products as $product) {
    $count++;
    $tagless_desc = $product->get_tagless_description();
    echo ("<div class='col row justify-content-center'>
        <div class='card-holder'>
        <div class='card' style='width: 17rem;'>
        <a class='store-item' href=\"item.php?store_item=" . $product->get_product_id() . "\"><div class='product-card-img'>" . $product->get_main_image() . "</div>
        <div class='card-body product-card-body'>
            <h5 class='item-name' class='card-title store-text'>" . $product->get_name() . "</h5>
            <p class='item-desc' class='card-text store-text'>" . substr($tagless_desc, 0, 120) . ((strlen($tagless_desc) > 120) ? "..." : "") . "</p>
            <h5 class='item-price'>$" . $product->get_price() . "</h5>
        </div>
        </a>
        </div>
        </div>
        </div>");
}

for ($count; $count % 3 != 0; $count++) {
    //Placeholder for emtpy slots
    echo "<div class='col'><div style='width: 17rem;'></div></div>";
}

echo "</div>";

//Echo end page buttons
echo $page_buttons . "<br>";
