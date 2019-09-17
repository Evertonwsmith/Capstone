<?php
include "../src/my_sql_cred.php";
require_once "../src/my_sql.php";
require_once "../src/order.php";
require_once "../src/user.php";
require_once "../src/page_manager.php";

//Verify user permissions
include_once 'admin_gateway.php';

if (isset($_REQUEST['archive_orders'])) {
    if ($_REQUEST['archive_orders'] === "unset") {
        unset($_SESSION['archive_orders']);
    } else {
        $_SESSION['archive_orders'] = $_REQUEST['archive_orders'];
    }
}

?>

<br>
<hr>
<div class='row'>
    <button onclick='reload_destination="admin_order.php";reload_content(document.getElementById("search_results"), {"archive_orders" : "0"});' class='<?php echo isset($_SESSION['archive_orders']) && !$_SESSION['archive_orders'] ? "current-page" : ""; ?>'>Active Orders</button>
    <div class='split-margin'></div>
    <button onclick='reload_destination="admin_order.php";reload_content(document.getElementById("search_results"), {"archive_orders" : "1"});' class='<?php echo isset($_SESSION['archive_orders']) && $_SESSION['archive_orders'] ? "current-page" : ""; ?>'>Archive Orders</button>
    <div class='split-margin'></div>
    <button onclick='reload_destination="admin_order.php";reload_content(document.getElementById("search_results"), {"archive_orders" : "unset"});' class='<?php echo !isset($_SESSION['archive_orders']) ? "current-page" : ""; ?>'>All Orders</button>
</div>
<hr>
<br>

<?php

//Create page manager
$pm = new page_manager("orders", "0", "order_date", "25", "0");

//Define headers
$headers = array('search_relevance' => true, 'order_id' => true, 'user_email' => true, 'user_name' => false, 'order_date' => true, 'order_status' => true, 'shipping_address' => false, 'order_detail' => false);
$pm->col = isset($pm->col) ? $pm->col : "order_date";

//Get search results
$search_keys = search_catalog::get_search_keys();

// Get all sorted order ids
if (isset($_SESSION['archive_orders'])) {
    if ($_SESSION['archive_orders']) {
        $where = "FIND_IN_SET(orderStatus,:0)>0";
        $where_attr = array("ship");
    } else {
        $where = "FIND_IN_SET(orderStatus,:0)>0";
        $where_attr = array("uncon,con,comp");
    }
} else {
    $where = null;
    $where_attr = null;
}
$orders = order::get_all_ids($pm->col, $pm->asc, null, $where, $where_attr);
$count = my_sql::select("count(orderID) as total", "orders");
$total_results = $count == false ? 0 : $count[0]['total'];

// Whether results should be kept in search relevance order
$search_order = $pm->col === "search_relevance";

//sort and cross filter the orders with search results
$sorted = search_catalog::filter_sort_results($search_keys, $orders, $search_order);
if ($search_keys) {
    $orders = $sorted;
    $total_results = count($orders);
}

//Make the order objects
$displayed_orders = array();
$relevance = 1;
$num = 0;
foreach ($orders as $index => $id) {
    if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
        $order = new order($id);
        //Set the relevance
        $order->set_relevance($relevance);
        //add to array
        array_push($displayed_orders, $order);
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
        $button = "<div>" . order::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
        $row .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
    } else {
        $content = order::get_table_header($name);
        $row .= "<div class='sort-holder-spot-reserve'>$content</div><div class='sort-holder'>$content</div>";
    }
    $row .= "</th>";
}
$row .= "</tr>";
echo $row;

// output data of each order
foreach ($displayed_orders as $order) {
    echo $order->get_table_entry(array_keys($headers));
}
echo "</table></div><br><br>";

//Echo end page buttons
echo $page_buttons . "<br>";
