<?php
require_once '../src/product.php';

//Verify user permissions
include_once 'admin_gateway.php';

if (isset($_REQUEST['product_id']) || isset($_SESSION['product_id'])) {
    $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : $_SESSION['product_id'];
    $product = new product($product_id);
    $product->check_attr_update();
    if ($product_id === "new") {
        header("location: product_detail.php?product_id=" . $product->get_product_id());
    }
} else {
    header("location: admin.php?tab=product");
}

$page_title = "Product Detail";
include 'header.php';

include 'admin_nav.php';

echo "<h2>Product Details</h2><br>";
echo "<hr><br><h4>Update Image</h4><br>";
include('../pages/product_image_uploader.php');
echo "<hr><br>";
echo "<h4>Update Product Details</h4><br>";
?>
<span class='required'>* (required fields)</span><br><br>
<?php
echo ($product->get_vertical_table(
    array(
        "main_image",
        "product_id",
        "name",
        "description",
        "price",
        "max_quantity",
        "requires_media_group",
        "artist_only",
        "is_public"
    ),
    true
));
?>

<br>
<hr>
<br>
<h4>Product Options</h4>
<br>
<div class="row">
    <div>
        <a href="product_preview.php?product_id=<?php echo $product->get_product_id(); ?>"><button>Preview</button></a>
    </div>
    <div class="split-margin"></div>
    <div>
        <?php echo $product->get_is_public_edit_form(); ?>
    </div>
    <div class="split-margin"></div>
    <div>
        <?php echo $product->get_deletion_form(); ?>
    </div>
</div>
<br>
<hr>
<br><br>

<?php
include 'footer.php';
?>