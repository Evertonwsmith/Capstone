<?php
include "../src/my_sql_cred.php";
require_once "../src/my_sql.php";
require_once "../src/product.php";
require_once "../src/user.php";
require_once "../src/page_manager.php";

//Verify user permissions
include_once 'admin_gateway.php';

//Create page manager
$pm = new page_manager("product", "1", null, "25", "0");

//Define headers
$headers = array("search_relevance" => true, "product_id" => true, "name" => true, "price" => true, "max_quantity" => true, "requires_media_group" => true, "is_public" => true, "product_detail" => false);
$pm->col = isset($pm->col) ? $pm->col : "product_id";

//Add create new product button
echo "<br><hr>";
echo product::get_new_product_button();
echo "<hr><br>";

//Get search results
$search_keys = search_catalog::get_search_keys();

// Get all sorted product ids
$products = product::get_all_ids($pm->col, $pm->asc);
$count = my_sql::select("count(productID) as total", "product");
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
echo $page_buttons . "<br>";

// echo the start of the table
echo "<div class='admin-table-container'><table id='" . $pm->tab . "_table'>";

// echo table headers
$row = "<tr>";
foreach ($headers as $name => $sortable) {
    $row .= "<th>";
    if ($sortable) {
        $button = "<div>" . product::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
        $row .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
    } else {
        $content = product::get_table_header($name);
        $row .= "<div class='sort-holder-spot-reserve'>$content</div><div class='sort-holder'>$content</div>";
    }
    $row .= "</th>";
}
$row .= "</tr>";
echo $row;

// output data of each product
foreach ($displayed_products as $product) {
    echo $product->get_table_entry(array_keys($headers));
}
echo "</table></div><br><br>";

//Echo end page buttons
echo $page_buttons . "<br>";
