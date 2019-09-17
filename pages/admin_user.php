<?php
include "../src/my_sql_cred.php";
require_once "../src/my_sql.php";
require_once "../src/user.php";
require_once "../src/page_manager.php";

//Verify user permissions
include_once 'admin_gateway.php';

if (isset($_REQUEST['user_type'])) {
    if ($_REQUEST['user_type'] === "unset") {
        unset($_SESSION['user_type']);
    } else {
        $_SESSION['user_type'] = $_REQUEST['user_type'];
    }
}
?>

<br>
<hr>
<div class='row'>
    <button onclick='reload_destination="admin_user.php";reload_content(document.getElementById("search_results"), {"user_type" : "unset"});' class='<?php echo !isset($_SESSION['user_type']) ? "current-page" : ""; ?>'>All Accounts</button>
    <div class='split-margin'></div>
    <button onclick='reload_destination="admin_user.php";reload_content(document.getElementById("search_results"), {"user_type" : "artist"});' class='<?php echo isset($_SESSION['user_type']) && $_SESSION['user_type'] == "artist" ? "current-page" : ""; ?>'>Artist Accounts</button>
    <div class='split-margin'></div>
    <button onclick='reload_destination="admin_user.php";reload_content(document.getElementById("search_results"), {"user_type" : "venue"});' class='<?php echo isset($_SESSION['user_type']) && $_SESSION['user_type'] == "venue" ? "current-page" : ""; ?>'>Venue Accounts</button>
    <div class='split-margin'></div>
    <button onclick='reload_destination="admin_user.php";reload_content(document.getElementById("search_results"), {"user_type" : "staff"});' class='<?php echo isset($_SESSION['user_type']) && $_SESSION['user_type'] == "staff" ? "current-page" : ""; ?>'>Staff Accounts</button>
    <div class='split-margin'></div>
    <button onclick='reload_destination="admin_user.php";reload_content(document.getElementById("search_results"), {"user_type" : "admin"});' class='<?php echo isset($_SESSION['user_type']) && $_SESSION['user_type'] == "admin" ? "current-page" : ""; ?>'>Admin Accounts</button>
</div>
<hr>
<br>

<?php
//Create page manager
$pm = new page_manager("useraccount", "1", null, "25", "0");

//Define headers
$headers = array("search_relevance" => true, "user_email" => true, "last_name" => true, "first_name" => true, "shipping_address" => false, "user_detail" => false);
$pm->col = isset($pm->col) ? $pm->col : "last_name";

//Get search results
$search_keys = search_catalog::get_search_keys();

// Get all sorted user ids
if (isset($_SESSION['user_type'])) {
    switch ($_SESSION['user_type']) {
        case "staff":
            $table = "staffaccount";
            break;
        case "admin":
            $table = "adminaccount";
            break;
        case "artist":
            $table = "artistaccount";
            break;
        case "venue":
            $table = "venueaccount";
            break;
    }
} else {
    $table = null;
}

$users = user::get_all_ids($pm->col, $pm->asc, null, $table);
if (!isset($table)) {
    $count = my_sql::select("count(userEmail) as total", "useraccount", "isActive=:0", array("1"));
} else {
    $count = my_sql::select("count($table.userEmail) as total", "useraccount, $table", "$table.userEmail=useraccount.userEmail AND isActive=:0", array("1"));
}
$total_results = $count == false ? 0 : $count[0]['total'];

// Whether results should be kept in search relevance order
$search_order = $pm->col === "search_relevance";

//sort and cross filter the users with search results
$sorted = search_catalog::filter_sort_results($search_keys, $users, $search_order);
if ($search_keys) {
    $users = $sorted;
    $total_results = count($users);
}

//Make the user objects
$displayed_users = array();
$relevance = 1;
$num = 0;
foreach ($users as $index => $id) {
    if ($num >= $pm->offset && $num - $pm->offset < $pm->page_amount) {
        $user = new user($id);
        //Set the relevance
        $user->set_relevance($relevance);
        //add to array
        array_push($displayed_users, $user);
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
        $button = "<div>" . user::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
        $row .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
    } else {
        $content = user::get_table_header($name);
        $row .= "<div class='sort-holder-spot-reserve'>$content</div><div class='sort-holder'>$content</div>";
    }
    $row .= "</th>";
}
$row .= "</tr>";
echo $row;

// output data of each user
foreach ($displayed_users as $user) {
    echo $user->get_table_entry(array_keys($headers));
}
echo "</table></div><br><br>";

//Echo end page buttons
echo $page_buttons . "<br>";
