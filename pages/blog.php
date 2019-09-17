<?php
$page_title = "Sleepovers Blog";
include 'header.php';
require_once '../src/blogpost.php';
require_once '../src/page_manager.php';
?>
<br>
<br>
<br>

<!-- Script that uses AJAX to reload page content in 'reload-content-container' div -->
<script type="text/javascript" src="../src/javascripts/admin_reload.js"></script>
<script>
	reload_destination = "blog_list.php";
</script>

<!-- Search Bar -->
<div id='search' class='d-flex flex-row'>
	<div class='p-2'>
		<h2>Sleepovers Blog</h2>
	</div>
	<?php echo search_catalog::get_catalog("blogpost"); ?>
</div>
<div class='d-flex flex-row' style='justify-content: flex-end;'>
	<div class='p-2'><?php echo page_manager::get_page_amount_selector(); ?></div>
</div>
<br>
<hr>
<br>
<br>
<div id="reload-content-container">
	<?php include 'blog_list.php'; ?>
</div>

<?php

include 'footer.php';
