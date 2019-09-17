<?php
require_once '../src/page_manager.php';
require_once '../src/search_catalog.php';
require_once '../src/product.php';
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';
require_once '../src/user.php';

if (session_status() == PHP_SESSION_NONE) {
    //Start session
    session_start();
}

//Get user info
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    $user = new user($user_email);
    $is_artist = $user->is_artist() || $user->is_admin() || $user->is_staff();
} else {
    $user_email = null;
    $is_artist = false;
}

//Create page manager
$pm = new page_manager("", "0", "price", "9", "0", array("artist_only" => "0"));

//Define headers
$headers = ["search_relevance" => true, "price" => true, "name" => true];
$pm->col = isset($pm->col) ? $pm->col : "price";
$artist_only = isset($_POST['artist_only']) && $_POST['artist_only'];
?>
<div class='row'>
    <div class='my-auto'>
        <h4>Sort By: </h4>
    </div>
    <div class='row'>
        <?php
        foreach ($headers as $name => $sortable) {
            $sorter = "<div class='public_sort_container'>";
            if ($sortable) {
                $button = "<div>" . product::get_table_header($name) . "</div><div>" . $pm->get_list_button($name) . "</div>";
                $sorter .= "<div class='sort-holder-spot-reserve'>$button</div><div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click()'>$button</div>";
            } else {
                $content = product::get_table_header($name);
                $sorter .= "<div class='sort-holder-spot-reserve'>$content</div><div class='sort-holder'>$content</div>";
            }
            $sorter .= "</div>";
            echo $sorter;
        }

        //Display artist-only filter
        if ($is_artist) {
            echo "
            <div class='public_sort_container'>
                <div class='sort-holder-spot-reserve'>
                    Artist-Only Products
                    <div class=''>
                        <button></button>
                    </div>
                </div>
                <div class='sort-holder' onclick='this.getElementsByTagName(\"button\")[0].click();'>
                    Artist-Only Products
                    <div class='" . ($artist_only ? "filter-check" : "") . "'>
                        <button onclick='if(window.extra_post[\"artist_only\"] == \"1\"){ window.extra_post[\"artist_only\"]=\"0\"; }else{ window.extra_post[\"artist_only\"]=\"1\"; } reload_content(null,window.extra_post);'></button>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
</div>
<hr>
<br><br>
<?php
//Get search results
$search_keys = search_catalog::get_search_keys();

// Get all sorted product ids
$products = product::get_all_ids($pm->col, $pm->asc, null, "isPublic='1'", null, $artist_only);
if ($artist_only) {
    $count = my_sql::select("count(product.productID) as total", "product RIGHT JOIN artistonlyproduct ON product.productID=artistonlyproduct.productID", "isPublic='1'");
} else {
    $count = my_sql::select("count(productID) as total", "product", "isPublic='1'");
}
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

//Echo post container
echo "<div class='row'>";

// output data of each product
$count = 0;
foreach ($displayed_products as $product) {
    $count++;
    $tagless_desc = $product->get_tagless_description();
    echo ("<div class='col row justify-content-center'>
            <div class='card-holder'>
                <div class='card' style='width: 17rem;'>
                    <a class='store-item' href=\"item.php?store_item=" . $product->get_product_id() . "\">
                        <div class='product-card-img'>" . $product->get_main_image() . "</div>
                        <div class='card-body product-card-body'>
                            <h5 class='item-name' class='card-title store-text'>" . $product->get_name() . "</h5>
                            <p class='item-desc' class='card-text store-text'>" . substr($tagless_desc, 0, 120) . ((strlen($tagless_desc) > 120) ? "..." : "") . "</p>
                            <h5 class='item-price'>$" . $product->get_price() . "</h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>");
}

for ($count; $count % 3 != 0; $count++) {
    //Placeholder for emtpy slots
    echo "<div class='col'><div style='width: 17rem;'></div></div>";
}

echo "</div>";

//Echo end page buttons
echo $page_buttons . "<br>";
?>