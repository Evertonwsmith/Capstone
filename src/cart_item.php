<?php
require_once 'general_item.php';
require_once 'my_sql.php';

class cart_item extends general_item
{

	private $user_email, $ship_product;

	public function __construct($user_email, $product_id, $media_group_id)
	{

		//Query information
		$item_data = my_sql::select("*", "cartitem", "userEmail=:0 AND productID=:1 AND mediaGroupID=:2", array("$user_email", "$product_id", "$media_group_id"));

		if (count($item_data) == 1) {
			$item_data = $item_data[0];

			//Set the variables
			$this->user_email = $item_data['userEmail'];
			$this->ship_product = $item_data['shipProduct'];

			parent::__construct($product_id, $media_group_id, $item_data['quantity']);
		} else {
			echo "cartitem identified by $user_email, $product_id, and $media_group_id, does not exist!";
			return null;
		}
	}

	public function get_user_email()
	{
		return $this->user_email;
	}

	public function get_ship_product()
	{
		return $this->ship_product;
	}

	public function get_item_price()
	{
		$price = $this->get_quantity() * ($this->get_product())->get_price();
		return $price;
	}

	private function get_where_condition()
	{
		return "userEmail=:0 AND productID=:1 AND mediaGroupID=:2";
	}

	private function get_where_attr()
	{
		return array($this->get_user_email(), $this->get_product_id(), $this->get_media_group_id());
	}

	public function get_update_form()
	{
		$form = (
			"<tr>
				<td style='max-width: 12rem; word-wrap: break-word;'><a href=\"item.php?store_item=" . $this->get_product_id() . "\">" . ($this->get_product())->get_name() . "</a></td>
				<td style='max-width: 5rem;'>
					<form method='post'>
						<div class='update_values'>
							<input type='hidden' name='confirm' value='false' id='confirm_" . $this->get_product_id() . "_" . $this->get_media_group_id() . "'/>
							<input type='hidden' name='product_id' value='" . $this->get_product_id() . "' />
							<input type='hidden' name='media_group_id' value='" . $this->get_media_group_id() . "' />
						</div>
						<div>
							<input type='number' style='width: 2.5rem;' name='update_item' min='1' value='" . $this->get_quantity() . "' onblur='this.form.submit()'/>
							<input type='submit' name='update_button' value='Update'/>
						</div>
					</form>
				</td>
				<td>" . ($this->get_product())->get_price() . "</td>
				<td style='text-align: center;'><input type='button' name='delete_item' value='Delete' class='delete_item' id=' " . $this->get_product_id() . "_" . $this->get_media_group_id() . "'/></td>
				<td style='text-align: center;'> $ " . $this->get_item_price() . "</td>
			</tr>"
			);
		return $form;
	}

	public function get_item_form($display_info = null)
	{
		$form = "";
		if ($display_info == "update_delete") {
			return $this->get_update_form();
			//return $this->get_delete_form() . $this->get_update_form();
		} else if ($display_info == "confirmation") {
			$form = (
				"<div>
					<tr>
						<td>
							<div class='cart_item_image_container'>
								<img class='cart_item_image' src='" . $this->get_product()->get_image_path() . "' />
							</div>
						</td>
						<td><p class='lead'>" . ($this->get_product())->get_name() . " </p></td>
						<td><p class='lead'>$ " . ($this->get_product())->get_price() . " </p></td>
						<td><p class='lead'>" . $this->get_quantity() . " </p></td>
						<td>
							<form method='post'>
								<div class='p-2 flex-grow-1 bd-highlight' id='ship_selector' >
								<label style='font-size: 1.3rem;'>Ship</label><input type='radio' class='ship_selector' name='ship_option' value='1' " . ($this->get_ship_product() ? 'checked' : '') . " onclick='if(confirm(\"You are agreeing to have this product shipped to your shipping address given above. Are you sure this is correct?\")){this.form.submit()}' />
								<label style='font-size: 1.3rem;'>Pickup</label><input type='radio' class='ship_selector' name='ship_option' value='0' " . ($this->get_ship_product() ? '' : 'checked') . " onclick='if(confirm(\"You are agreeing to pick up this product from Sleepovers. This item will not be shipped to you as a result. Are you sure this is correct?\")){this.form.submit()}'/>
								</div>
								<input type='hidden' name='product_id' value='" . $this->get_product_id() . "' />
								<input type='hidden' name='media_group_id' value='" . $this->get_media_group_id() . "' />
							</form>
						</td>
					</tr>
					<br>
				</div>");
		}
		return $form;
	}

	public function get_item_info()
	{
		$ship_pickup = ($this->get_ship_product() ? ' Shipped ' : ' Pickup ');
		$info = ("<tr>
				<td>
					<div class='cart_item_image_container'>
						<img class='cart_item_image' src='" . $this->get_product()->get_image_path() . "' />
					</div>
				</td>
				<td>" . ($this->get_product())->get_name() . " </td>
				<td>" . ($this->get_product())->get_price() . "</td>
				<td>" . $this->get_quantity() . "</td>
				<td>" . $ship_pickup . "</td>
			</tr>
			<tr>
				<td></td>
				<td>Total Item Price:</td>
				<td>$ " . $this->get_item_price() . "</td>
				<td></td>
				<td></td>
			</tr>");
		return $info;
	}

	public function set_ship_product($ship_product)
	{
		if (isset($ship_product)) {
			$this->ship_product = $ship_product;
		} else {
			$this->ship_product = 1;
		}
	}

	public function update_item_quantity($new_quantity)
	{
		$update = my_sql::update("cartitem", array("quantity"), array($new_quantity), $this->get_where_condition(), $this->get_where_attr());
		if ($update) {
			$this->set_quantity($new_quantity);
		}
		return $update;
	}

	public function update_item_pickup($ship_product)
	{
		$update = my_sql::update("cartitem", array("shipProduct"), array($ship_product), $this->get_where_condition(), $this->get_where_attr());
		if ($update) {
			$this->set_ship_product($ship_product);
		}
		return $update;
	}

	public function delete_cart_item()
	{
		$delete = my_sql::delete("cartitem", $this->get_where_condition(), $this->get_where_attr());
		return $delete;
	}
}