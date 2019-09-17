<?php
require_once '../src/blogpost.php';
if (isset($_REQUEST['blog_id']) || isset($_SESSION['blog_id'])) {
    $blog_id = isset($_REQUEST['blog_id']) ? $_REQUEST['blog_id'] : $_SESSION['blog_id'];
    $blogpost = new blogpost($blog_id);
    $blogpost->check_attr_update();
    if ($blog_id === "new") {
        header("location: blogpost_detail.php?blog_id=" . $blogpost->get_blog_id());
    }
} else {
    header("location: admin.php?tab=blogpost");
}

//header
$page_title = "Blogpost Detail";
include 'header.php';

//Verify user permissions
include_once 'admin_gateway.php';

//Nav bar
include 'admin_nav.php';

?>
<br>
<h2>Blogpost Details</h2>
<span class='required'>* (required fields)</span><br><br>

<?php
echo ($blogpost->get_vertical_table(
    array(
        "blog_id",
        "title",
        "is_public",
        "timestamp",
        "media_group_id",
        "text",
        "media"
    ),
    true
));
echo "<br><h4>Upload Media File</h4><br>";
include('../pages/blogpost_file_uploader.php');
?>

<br>
<hr>
<br>
<h4>Blogpost Options</h4>
<br>
<div class="row">
    <div>
        <a href="blog_preview.php?blog_id=<?php echo $blogpost->get_blog_id(); ?>"><button>Preview</button></a>
    </div>
    <div class="split-margin"></div>
    <div>
        <?php echo $blogpost->get_is_public_edit_form(); ?>
    </div>
    <div class="split-margin"></div>
    <div>
        <?php echo $blogpost->get_deletion_form(); ?>
    </div>
</div>
<br>
<hr>
<br><br>

<?php
include 'footer.php';
?>