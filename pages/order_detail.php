<?php
include '../src/order.php';

//Verify user permissions
include_once 'admin_gateway.php';

if (isset($_REQUEST['order_id'])) {
    // Get order object
    $order = new order($_REQUEST['order_id']);

    if (isset($_REQUEST['order_status'])) {
        //Change the order status
        $order->set_order_status($_REQUEST['order_status']);
    }

    if (isset($_REQUEST['order_item_status']) && isset($_REQUEST['media_group_id']) && isset($_REQUEST['product_id'])) {
        //Change the order item status
        ($order->get_order_item($_REQUEST['product_id'], $_REQUEST['media_group_id']))->set_order_status($_REQUEST['order_item_status']);
    }
} else {
    header("location: admin.php?tab=orders");
}

$page_title = "Order Detail";
include 'header.php';

include 'admin_nav.php';

?>

<h2>Order Details</h2>
<table class="vertical">
    <?php
    echo $order->get_table_entry(
        array(
            'order_id',
            'order_status',
            'user_email',
            'user_name',
            'shipping_address',
            'billing_address',
            'order_date',
            'completion_date',
            'ship_date'
        ),
        true,
        true
    );
    ?>
</table>

<br><br>

<h2>Order Items</h2>
<?php
echo $order->get_item_table(array("product_image", "product_name", "quantity", "order_status", "ship_product", "attached_media", "delete_media"), true);
?>

<?php
include 'footer.php';
?>