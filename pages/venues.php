<?php
$page_title = "Sleepovers Venues";
include "header.php";
require_once '../src/page_manager.php';
?>
<br>
<br>
<br>

<!-- Script that uses AJAX to reload page content in 'reload-content-container' div -->
<script type="text/javascript" src="../src/javascripts/admin_reload.js"></script>
<script>
    reload_destination = "venues_results.php";
    page_amount = 9;
</script>

<!-- Search Bar -->
<div id='search' class='d-flex flex-row'>
    <div class='p-2'>
        <h2>Venue</h2>
    </div>
    <?php echo search_catalog::get_catalog("venueaccount"); ?>
</div>
<div class='d-flex flex-row' style='justify-content: flex-end;'>
    <div class='p-2'><?php echo page_manager::get_page_amount_selector(array("6", "9", "15", "27"), 9); ?></div>
</div>
<br>
<hr>
<div id="reload-content-container">
    <?php include 'venues_results.php'; ?>
</div>

<?php

include 'footer.php';
