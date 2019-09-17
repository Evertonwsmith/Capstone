<?php
include "../src/my_sql_cred.php";
require_once "../src/my_sql.php";
require_once "../src/blogpost.php";
require_once "../src/user.php";
require_once "../src/page_manager.php";

//Verify user permissions
include_once 'admin_gateway.php';

//Create page manager
$pm = new page_manager("blogpost", "0", null, "25", "0");

//Define headers
$headers = array("search_relevance" => true, "blog_id" => true, "title" => true, "timestamp" => true, "is_public" => true, "blogpost_detail" => false);
$pm->col = isset($pm->col) ? $pm->col : "timestamp";

//Add create new blogpost button
echo "<br><hr>";
echo blogpost::get_new_blogpost_button();
echo "<hr><br>";

//Get search results
$search_keys = search_catalog::get_search_keys();

// Get all sorted blogpost ids
$blogposts = blogpost::get_all_ids($pm->col, $pm->asc);
$count = my_sql::select("count(blogID) as total", "blogpost");
$total_results = $count == false ? 0 : $count[0]['total'];

// Whether results should be kept in search relevance order
$search_order = $pm->col === "search_relevance";

//sort and cross filter the blogposts with search results
$sorted = search_catalog::filter_sort_results($search_keys, $blogposts, $search_order);
if ($sorted) {
    $blogposts = $sorted;
    $total_results = count($blogposts);
}

//Make the blogpost objects
$displayed_blogposts = array();
$relevance = 1;
$num = 0;
foreach ($blogposts as $index => $id) {
    if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
        $blogpost = new blogpost($id);
        //Set the relevance
        $blogpost->set_relevance($relevance);
        //add to array
        array_push($displayed_blogposts, $blogpost);
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
        $button = "<div>" . blogpost::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
        $row .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
    } else {
        $content = blogpost::get_table_header($name);
        $row .= "<div class='sort-holder-spot-reserve'>$content</div><div class='sort-holder'>$content</div>";
    }
    $row .= "</th>";
}
$row .= "</tr>";
echo $row;

// output data of each blogpost
foreach ($displayed_blogposts as $blogpost) {
    echo $blogpost->get_table_entry(array_keys($headers));
}
echo "</table></div><br><br>";

//Echo end page buttons
echo $page_buttons . "<br>";
