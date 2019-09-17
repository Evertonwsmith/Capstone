<?php
$page_title = "Admin Page";
include "header.php";
require_once "../src/user.php";
require_once "../src/page_manager.php";

//Verify user permissions
include_once 'admin_gateway.php';

//Navigation bar
include 'admin_nav.php';
?>

<!-- Script that uses AJAX to reload page content in 'reload-content-container' div -->
<script type="text/javascript" src="../src/javascripts/admin_reload.js"></script>

<?php
//Include the search bar if the tab is compatable
if (isset($_REQUEST['tab']) && strlen($_REQUEST['tab']) > 0) {
    $tab = $_REQUEST['tab'];
    if (in_array($tab, ["orders", "product", "blogpost", "useraccount"])) {
        echo "<div id='search' class='d-flex flex-row'>
            <div class='p-2'>
                <h2>Search</h2>
            </div>";
        echo search_catalog::get_catalog($tab);
        echo "</div>";
        echo "<div class='d-flex flex-row' style='justify-content: flex-end;'><div class='p-2'>" . page_manager::get_page_amount_selector() . "</div></div>";
    }
}
?>

<div id="reload-content-container">
    <!-- Default Content -->
    <?php
    if (!isset($tab)) {
        echo ("<hr>
        <br>
        <h2 style='text-align: center;'>Welcome, <br>" . $user->get_first_name() . ' ' . $user->get_last_name() . "</h2>
        <br>
        <hr>
        <br>
        <div class='row justify-content-center'>
            <div id='admin-profile-img'>" . $user->get_profile_image() . "</div>
        </div>");
    }
    ?>
</div>

<?php
if (isset($tab)) {
    echo "<script>admin_nav_link_select(document.getElementById('" . $tab . "'));</script>";
}

include "footer.php";
?>