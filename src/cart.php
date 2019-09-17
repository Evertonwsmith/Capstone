<?php
require_once 'cart_item.php';
require_once 'my_sql.php';

class cart
{

	private $cart_items = [], $user_email;

	public function __construct($user_email)
	{
		$cart = my_sql::select("*", "cartitem", "userEmail = :0", array("$user_email"));
		$this->user_email = $user_email;
		if (isset($cart) && count($cart) != 0) {
			foreach ($cart as $row) {
				$key = $row["productID"] . "_" . $row["mediaGroupID"];
				$this->cart_items[$key] = new cart_item($row["userEmail"], $row["productID"], $row["mediaGroupID"]);
			}
		} else {
			return null;
		}
	}

	public function get_user_email()
	{
		return $this->user_email;
	}

	public function get_cart_items()
	{
		return $this->cart_items;
	}

	public function get_cart_item($product_id, $media_group_id)
	{
		// For some reason $item_id must be an int, string values use to work but no longer do
		$item_id = (int) $product_id . '_' . $media_group_id;
		if (isset($this->cart_items[$item_id])) {
			return $this->cart_items[$item_id];
		} else {
			return null;
		}
	}

	public function get_all_product_ids()
	{
		$all_item_ids = [];
		foreach ($this->cart_items as $item) {
			array_push($all_item_ids, $item->get_product_id());
		}
		return $all_item_ids;
	}

	public function get_product_keys()
	{
		if (!empty($this->cart_items)) {
			return array_keys($this->get_cart_items());
		} else {
			return null;
		}
	}

	public function get_total_cart_price()
	{
		$total = 0;
		foreach ($this->cart_items as $item) {
			$total = $total + $item->get_item_price();
		}
		return $total;
	}

	public function add_cart_item($product_id, $quantity, $media_group_id)
	{
		$check = my_sql::exists("product", "productID=:0 AND isPublic='1'", array("$product_id"));
		if ($check) {
			$item_in_cart = $this->get_cart_item($product_id, $media_group_id);
			if (!isset($item_in_cart)) {
				//cart item does not exist make a new one
				$query = my_sql::insert("cartitem", array("productID", "userEmail", "quantity", "mediaGroupID"), array($product_id, $this->get_user_email(), $quantity, $media_group_id));
				if ($query) {
					array_push($this->cart_items, new cart_item($this->get_user_email(), $product_id, $media_group_id));
					return 1;
				}
			} else {
				//cart item does exist, update quantity
				$current_quantity = $item_in_cart->get_quantity();
				return $this->update_cart_item_quantity($product_id, $current_quantity + $quantity, $media_group_id);
			}
		}
		return 0;
	}

	public function update_cart_item_pickup($product_id, $media_group_id, $ship_product)
	{
		$item = $this->get_cart_item($product_id, $media_group_id);
		if ($item != null) {
			$update = $item->update_item_pickup($ship_product);
			return $update;
		} else {
			return false;
		}
	}

	public function update_cart_item_quantity($product_id, $new_quantity, $media_group_id)
	{
		$item = $this->get_cart_item($product_id, $media_group_id);
		if ($item != null) {
			$update = $item->update_item_quantity($new_quantity);
			return $update;
		} else {
			return false;
		}
	}

	public function delete_cart_item($product_id, $media_group_id)
	{
		$item = $this->get_cart_item($product_id, $media_group_id);
		if ($item != null) {
			$delete = $item->delete_cart_item();
			unset($this->cart_items[$product_id . "_" . $media_group_id]);
			return $delete;
		} else {
			return false;
		}
	}

	public function print_all_cart_items($display_info = null)
	{
		$print_form = "";
		if ($display_info == "update_delete") {
			foreach ($this->cart_items as $item) {
				$print_form .= $item->get_item_form($display_info);
			}
		} else if ($display_info == "confirmation") {
			foreach ($this->cart_items as $item) {
				$print_form .= $item->get_item_form($display_info);
			}
		} else {
			foreach ($this->cart_items as $item) {
				$print_form .= $item->get_item_info();
			}
		}
		return $print_form;
	}

	public function check_attr_update()
	{
		foreach ($_POST as $key => $value) {
			switch ($key) {
				case "update_item":
					$product_id = $_POST['product_id'];
					$media_group_id = $_POST['media_group_id'];
					if (isset($product_id) && isset($media_group_id)) {
						$this->update_cart_item_quantity($product_id, $_POST['update_item'], $media_group_id);
					}
					break;
				case "delete_item":
					$product_id = $_POST['product_id'];
					$media_group_id = $_POST['media_group_id'];
					if (isset($product_id) && isset($media_group_id)) {
						$this->delete_cart_item($product_id, $media_group_id);
					}
					break;
				case "ship_option":
					$product_id = $_POST['product_id'];
					$media_group_id = $_POST['media_group_id'];
					if (isset($product_id) && isset($media_group_id)) {
						$this->update_cart_item_pickup($_POST["product_id"], $_POST["media_group_id"], $value);
					}
					break;
				case "cart_submit_button":
					if (isset($_SESSION['cart_test']) && $_SESSION['cart_test'] == 0) {
						if (isset($_POST['item_amount']) && isset($_POST['store_item'])) {
							$product_id = $_POST['store_item'];
							$check = $this->get_cart_item($product_id, 1);
							$quantity = $_POST['item_amount'];
							if ($quantity != 0) {
								if ($check == null) {
									$result = $this->add_cart_item($product_id, $quantity, 1);
									if ($result) {
										echo ("Item was added to your cart");
										$_SESSION['cart_test'] = 1;
									}
								} else {
									$update = $this->update_cart_item_quantity($product_id, $quantity, 1);
									if ($update) {
										echo "<h5>Item in cart updated</h5>";
									}
								}
							} else {
								echo "Item quantity must be greater than 0";
							}
						}
					}
				case "mixtape_cart_item":
					if (isset($_SESSION['product_id']) && $_SESSION['media_group_id'] && $_SESSION['quantity']) {
						$result = $this->add_cart_item($_SESSION['product_id'], $_SESSION['quantity'], $_SESSION['media_group_id']);
						unset($_SESSION['product_id']);
						unset($_SESSION['quantity']);
						unset($_SESSION['media_group_id']);
					}
					break;
			}
		}
	}
}
