<?php 
	/**
	 * ////////////////////////
	 * // Checkout Page Info //
	 * ////////////////////////
	 * 
	 * @desc this page gets all of the users purchase info including their cart, shipping and billing info and displays it form them so they can confirm their order 
	 * @author Joshua Henderson joshuahenderson00@gmail.com
	 * @required my_sql.php, store_function.php, header.php, footer.php
	 */
	include 'header.php';
	require_once '../src/address.php';
	require_once '../src/cart.php';
	
	$form_action = null;
	$order_ready = false;
	$confirm_items = false;
	$button_enable = "disabled";
	// There must be a user email in the session
	if (isset($user_email)) {
		$shipping_id = ((isset($_SESSION['shipping_address_id']) && $_SESSION['shipping_address_id'] != 0) ? $_SESSION['shipping_address_id'] : (address::get_shipping_address_id($user_email) != false ? address::get_shipping_address_id($user_email) : null));
		$billing_id = ((isset($_SESSION['billing_address_id']) && $_SESSION['billing_address_id'] != 0) ? $_SESSION['billing_address_id'] : (address::get_billing_address_id($user_email) != false ? address::get_billing_address_id($user_email) : null));
		// Get items in cart
		$user_cart = new cart($user_email);
		// Check that the customer has items to purchase
		if (isset($user_cart) && count($user_cart->get_cart_items()) != 0) {
			$confirm_items = true;
		} else {
			header("location: store.php");
		}
		$user_cart->check_attr_update();
	} else {
		header("location: home.php");
	}
?>
	<div class="row justify-content-center" align="center">
		<div class="checkout_wrap" style="margin-top: 2%;">
			<ul class="checkout_bar">
				<li class="previous"><a href="cart.php">Cart</a></li>
				<li class="active"><p>Checkout</p></li>
				<li class="next">Place Order</li>
				<li class="incomplete">Complete</li>
			</ul>
		</div>
		<div style='width: 80%;'>
			<br>
			<div class="row justify-content-center">
				<h1>Checkout</h1>
			</div>
			<hr>
			<h3>Your Cart Items</h3>
			<a href="cart.php">Edit Cart</a>
			<table>
				<tr>
					<td></td>
					<td style="font-size: 1.3rem;">Item Name</td>
					<td style="font-size: 1.3rem;">Price</td>
					<td style="font-size: 1.3rem;">Quantity</td>
					<td style="font-size: 1.3rem;">Ship/Pickup</td>
				</tr>
			<?php 
			if (isset($user_cart) && !empty($user_cart)) {
				 echo $form_info = $user_cart->print_all_cart_items("confirmation");
			}
			?>
			</table>
			<br>
			<br>
			<br>
			<div style="width: 60%; border: 1px solid white; padding: 2%; border-radius: 0.5rem; border-radius: 0.5rem; box-shadow: 0 0 3.5rem 0.1rem rgba(255, 0, 128, 0.5);">
			<?php
			if ($confirm_items) {
				$form_action = "place_order.php";
				$button_enable = "enable name='checkout_submit_check'";
				$order_ready = true;
				echo (
					"<fieldset>"
					."<form action='$form_action' method='post'>"
					.address::get_shipping_inputs($shipping_id)
					.address::get_billing_inputs($billing_id)
					."<div>"
					."<br><button type='submit' $button_enable>Confirm Order</button>"
					."</div>"
					."</form>"
					."</fieldset>"
				);
			}
			?>
			</div>
		</div>
	</div>
<script type='text/javascript'>
	// This function will disable the billing address inputs if the user wants their billing and shipping information to be the same
	function disableInputs(class_name) {
		var inputs;
		// If the checkbox is checked then change all items with the class name 'bill_address' to disabled
		if (document.getElementById("bill_ship_same").checked) {
			inputs = document.getElementsByClassName(class_name);
			for (var i = 0;i < inputs.length; i++) {
				inputs[i].disabled = true;
			}
		} 
		// If the checkbox is unchecked then change all items with the class name 'bill_address' to enabled
		if (!document.getElementById("bill_ship_same").checked) {
			inputs = document.getElementsByClassName(class_name);
			for (var i = 0;i < inputs.length; i++) {
				inputs[i].disabled = false;
			}
		} 
	}
	$(document).ready(function(){
		$("#bill_ship_same").click(function(){
			$('#bill_address_street').val($('#ship_address_street').val());
			$('#bill_address_lineTwo').val($('#ship_address_lineTwo').val());
			$('#bill_address_city').val($('#ship_address_city').val());
			$('#bill_address_province').val($('#ship_address_province').val());
			$('#bill_address_postalCode').val($('#ship_address_postalCode').val());
		});
	});
</script>
<?php include('footer.php');?>