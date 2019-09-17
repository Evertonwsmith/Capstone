<?php
require_once '../src/product.php';
require_once '../src/cart.php';

if (isset($_REQUEST['store_item']) || isset($_GET['product_id'])) {
    $product_id = isset($_GET['product_id']) ? $_GET['product_id'] : $_REQUEST['store_item'];
} else {
    header("location: store.php");
}

$item = new product($product_id);

$item_value = 1;
$page_direct = "";

$prod_name = $item->get_name();
$prod_desc = $item->get_description();
$prod_price = $item->get_price();
$prod_media_group = $item->get_media_group_id();
$prod_require_media = $item->requires_media_group();

if (isset($_SESSION["user_email"])) {
    $user_cart = new cart($user_email);
    if ($prod_require_media == 0) {
        $page_direct = "cart.php";
        $media_group_id = 1;
    } else {
        $page_direct = "mixtape_audio_uploader.php";
        $media_group_id = "new";
    }
}
?>
<br>
<div class="row">
    <div class="col-12 col-lg-7 col-xl-7" style='margin-bottom: 2%;'>
        <div class='product-img'><?php echo $item->get_main_image(); ?></div>
    </div>
    <div class="col" style="padding-left: 2rem;">
        <?php
        echo ("<div style='margin-top: 2%;'>
				<h1 class='h1'>$prod_name</h1>
			</div>
			<div>
				<h5>Price: $prod_price</h3>
			</div>
			<div>
				<hr>
			</div>");
        ?>
        <div>
            <h3>Quantity:</h3>
            <div>
                <input id="item_quantity" type="number" style="width: 2.5rem; text-align: center;" name="item_amount" min="1" <?php echo ($page_direct == "mixtape_audio_uploader.php" ? "max='50' onclick='set_session({quantity:this.value})'" : ""); ?> value="<?php echo $item_value; ?>" />
                <?php
                $media_req_add_button = "<button onclick='set_session({product_id:$product_id,quantity:$(\"#item_quantity\").val()}, function(){window.location.replace(\"mixtape_audio_uploader.php\");});'>Add to Cart</button>";
                $normal_add_button = "<button onclick='add_to_cart({product_id:$product_id, quantity:$(\"#item_quantity\").val(), media_group_id:1})'>Add to Cart</button>";
                $sign_in_required_button = "<button disabled>Add to Cart</button><div class='error'>You must sign in before adding an item to your cart.</div>";
                echo !isset($_SESSION["user_email"]) ? $sign_in_required_button : ($prod_require_media ? $media_req_add_button : $normal_add_button);
                ?>
            </div>
        </div>
        <div>
            <hr>
        </div>
        <?php
        echo "<div style='padding-top: 1rem;'><p>$prod_desc</p></div>";
        ?>
    </div>
</div>
<br><br><br>
<hr>
<br><br>
<h2>You Might Also Like...</h2>
<br><br>
<div id="similar-items">
    <div id="search_container" style="display:none;">
        <?php
        //Get catalog
        echo search_catalog::get_catalog("product");
        ?>
    </div>
    <script src='../src/javascripts/admin_reload.js'></script>
    <script>
        var reload_destination = "item_similar_results.php";
        window.extra_post = {
            "product_id": "<?php echo $item->get_product_id(); ?>"
        };
        //Wait for catalog load
        $('#catalog_load_trigger').on("click", function() {
            //Set search text
            $("#search_bar").val("<?php
                                    foreach (explode(" ", $item->get_tagless_description()) as $word) {
                                        if (strlen($word) > 5) echo $word . " ";
                                    };
                                    echo $item->get_name(); ?>");
            //Sumbit search
            $("#search_submit").click();
        });
    </script>
    <div id="reload-content-container">
        <?php
        //include "item_similar_results.php";
        ?>
    </div>
</div>
<br>
<?php echo "<script src='../src/javascripts/cart.js'></script>" ?>