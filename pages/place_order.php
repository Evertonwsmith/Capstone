<?php
include 'header.php';
require_once '../src/address.php';
require_once '../src/cart.php';

if (isset($user_email)) {
    $shipping_info = "";
    $billing_info = "";
    if (isset($_POST['checkout_submit_check'])) {
        address::check_attr_update_address();
    }
?>
    <div class="row justify-content-center">
        <div class="checkout_wrap">
			<ul class="checkout_bar">
				<li class="previous"><a href="cart.php">Cart</a></li>
				<li class="previous"><a href="checkout.php">Checkout</a></li>
				<li class="active"><p>Place Order</p></li>
				<li class="incomplete">Complete</li>
			</ul>
        </div>
        <div style="width: 80%;">
            <div>
                <br>
                <h1>Place Order</h1>
            </div>
            <div class="w-100"></div>
<?php
    $user_cart = new cart($user_email);
    if (count($user_cart->get_cart_items()) != 0) {
        $cart_info = $user_cart->print_all_cart_items();
        echo 
        "<div>
        <hr>
        <h2>Your Cart Items</h2>
        <a href='cart.php'>Edit Cart Items</a>
        <br><br>
        </div>
        <div class='w-100'></div>
        <div class='col-12'>
			<table style='width: 100%;'>
				<tr>
					<th></th>
					<th class='purchase_text'>Product Name</th>
					<th class='purchase_text'>Price</th>
					<th class='purchase_text'>Quantity</th>
					<th class='purchase_text'>Ship/Pickup</th>
                </tr>
                $cart_info 
                <tr>
                    <td></td>
                    <td class='purchase_text'>Subtotal:</td>
                    <td class='purchase_text'>$ " . $user_cart->get_total_cart_price() . "</td>
                    <td></td>
                    <td></td>
                </table>
            <br>
        </div>";
    } else {
        header("location: store.php");
    }
?>
        <br>
        <div>
            <h2>Address information</h2>
            <a href='checkout.php'>Edit Address Information</a>
            <br><br>
        </div>
        <div class="w-100"></div>
        <div class="col-12">
<?php
    if (isset($_SESSION['shipping_address_id']) && isset($_SESSION['billing_address_id'])) {
        if ($_SESSION['shipping_address_id'] != 0 && $_SESSION['billing_address_id'] != 0) {
            if (isset($_POST['bill_address'])) {
                if (address::is_valid_address_input($_POST['bill_address'])) {
                    $bill_check = true;
                } else {
                    $bill_check = false;
                }
            } else {
                $bill_check = true;
            }
            /**
             * **********
             * ** TODO **
             * **********
             * 
             * In the if statment below the input with the name 'place_order_declined' is a test button and should be
             * deleted before the website is officially deployed for public use.
             */
            if (address::is_valid_address_input($_POST['ship_address']) && $bill_check) {
                echo (
                    "
                    <div>
                        <p class='lead'>Shipping Address: " . address::address_to_string($_SESSION['shipping_address_id']) . "</p>
                    </div>
                    <div class='w-100'></div>
                    <div>
                        <p class='lead'>Billing Address: " . address::address_to_string($_SESSION['billing_address_id']) . "</p>
                    </div>
                    <div class='w-100'></div>
                    <hr>  
                    <div style='text-align: right;'>
                        <form method='post' action='order_result.php'>
                        <input type='hidden' name='ship_address' value=$shipping_info />
                        <input type='hidden' name='bill_address' value=$billing_info /><br>
                        <input type='submit' name='place_order_confirmed' value='Place Order'/>
                        <input type='submit' name='place_order_declined' value='Test Failed Payment'/>
                        </form>
                    </div>");
            } else {
                echo ("<p>"
                    . "Certain fields for your address information were left blank<br>"
                    . "Please return to the <a href='checkout.php' >checkout page</a> to re-enter your address information<br>"
                    . "</p>");
            }
        } else {
            if ($_SESSION['shipping_address_id'] == 0)
                echo ("<p>"
                    . "There is no shipping information listed for your order<br>"
                    . "Please return to the <a href='checkout.php' >checkout page</a> to re-enter your address information<br>"
                    . "</p>");
            if ($_SESSION['billing_address_id'] == 0) {
                echo ("<p>"
                    . "There is no billing information listed for your order<br>"
                    . "Please return to the <a href='checkout.php' >checkout page</a> to re-enter your address information<br>"
                    . "</p>");
            }
        }
    }
} else {
    header("location: home.php");
}
?>
        </div>
    </div>    
</div>
<?php include 'footer.php'; ?>