<?php 
include('header.php'); 
include('../src/cart.php');
?>
	<h2>Shopping Cart</h2>
	<hr>
	<?php 
		$checkout_button_check = "disabled"; 
		$checkout_form_file = "#";
		if (isset($user_email)) {
			$user_cart = new cart($user_email);
			$user_cart->check_attr_update();
			$user_cart = new cart($user_email);

			$form_info = $user_cart->print_all_cart_items("update_delete");
			if ($user_cart->get_all_product_ids() != null) {
				$checkout_button_check = "enabled";
				$checkout_form_file = "checkout.php";
				echo (
					"<br>
					<h3>Items in cart</h3>
					<br>
					<table>
						<tr>
							<th style='max-width: 12rem; word-wrap: break-word;'>Product Name</th>
							<th style='max-width: 5rem;'>Quantity</th>
							<th>Price</th>
							<th style='text-align: center;'>Remove</th>
							<th style='text-align: center;'>Total Item Price</th>
						</tr>" . $form_info . 
						"<tr>
							<th></th>
							<th></th>
							<th></th>
							<th style='text-align: right; font-size: 1.5rem;'><strong>Subtotal:</strong></th>
							<th style='text-align: center; font-size: 1.5rem;'>$ " . $user_cart->get_total_cart_price() . "</th>
						</tr>
					</table><br>"
				);
				?>
				<hr>
				<div>
					<div class="d-flex flex-row mb-2">
						<div class="mr-auto p-2">
							<button class="return_button" onclick="window.location='store.php'">Continue Shopping</button>
						</div>
						<?php
						echo (
							"<div class='p-2 align-self-center'>
								<form action='$checkout_form_file' align='right'>
									<button class='return_button checkout_button' type='submit' $checkout_button_check>Checkout</button>
								</form>
							</div>"
						);
						} else {
							echo (
								"<h4>There are no items in your cart</h4>"
							);
						}
						} else {
							header("location: home.php");
						}
				
						?>
					</div>
				</div>	
<script>
$('.delete_item').on("click",
	function(event)
	{
		var id = $(this).attr('id');
		if (window.confirm("Are You sure you want to delete this item from your cart?")) {
			var data = id.split('_');
		
			event.preventDefault();
			var newForm = jQuery('<form>', {
				'action': '../pages/cart.php',
				'method': 'post',
				'target': '_top'
			}).append(jQuery('<input>', {
				'name': 'delete_item',
				'value': '1',
				'type': 'hidden'
			})).append(jQuery('<input>', {
				'name': 'product_id',
				'value': data[0],
				'type': 'hidden'
			})).append(jQuery('<input>', {
				'name': 'media_group_id',
				'value': data[1],
				'type': 'hidden'
			}));
			
			newForm.appendTo("body").submit();
		} 
	});	
</script>
<?php include('footer.php'); ?>