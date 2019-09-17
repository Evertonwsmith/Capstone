<?php
require_once 'my_sql.php';
require_once 'address.php';
require_once 'order_item.php';
require_once 'user.php';

class order
{
    private $order_id, $user_email, $shipping_address, $billing_address, $order_status, $order_date, $completion_date, $ship_date;

    private $order_items;

    private $order_detail_link = "order_detail.php";

    private $relevance = null;

    public function __construct($order_id)
    {
        $order_data = my_sql::select("*", "orders", "orderID=:0", array("$order_id"));
        if (count($order_data) == 1) {
            $order_data = $order_data[0];
            $this->order_id = $order_data['orderID'];
            $this->user_email = $order_data['userEmail'];
            $this->shipping_address = $order_data['shippingAddress'];
            $this->billing_address = $order_data['billingAddress'];
            $this->order_status = $order_data['orderStatus'];
            $this->order_date = $order_data['orderDate'];
            $this->completion_date = $order_data['completionDate'];
            $this->ship_date = $order_data['shipDate'];

            //Get the order items
            $this->order_items = array();
            $items = my_sql::select("productID, mediaGroupID", "orderitem", "orderID=:0", array($this->order_id));
            foreach ($items as $item) {
                array_push($this->order_items, new order_item($this->order_id, $item["productID"], $item["mediaGroupID"]));
            }
        } else {
            //echo "Order, $order_id, does not exist!";
            return null;
        }
    }

    /********************
     * Define the getters
     * ******************
     */

    public function get_order_id()
    {
        return $this->order_id;
    }

    public function get_user_email()
    {
        return $this->user_email;
    }

    public function get_shipping_address()
    {
        return address::address_to_string($this->shipping_address);
    }

    public function get_billing_address()
    {
        return address::address_to_string($this->billing_address);
    }

    public function get_order_status($status = null)
    {
        if (!isset($status)) $status = $this->order_status;
        switch ($status) {
            case "uncon":
                return "<b style='color:#ff0080'>Unconfirmed</b>";
                break;
            case "con":
                return "<b style='color:#dbcfb0'>Confirmed</b>";
                break;
            case "comp":
                return "<b style='color:#70c1b3'>Completed</b>";
                break;
            case "ship":
                return "<b style='color:#247ba0'>Shipped / Picked Up</b>";
                break;
            default:
                return "Unknown";
                break;
        }
    }

    public function get_order_status_form()
    {
        $current_status = $this->order_status;

        //Filter status options
        switch ($current_status) {
            case "uncon":
                break;
            case "con":
                break;
            case "comp":
                break;
            case "ship":
                break;
            default:
                return false;
        }

        //Add form and options
        $form = ("<form method='post' onchange='this.submit();'>"
            . "<select name='order_status'>"
            . "<option style='color:#FF0080' value='uncon' " . ($current_status == 'uncon' ? "selected='true'" : "") . ">" . $this->get_order_status("uncon") . "</option>"
            . "<option style='color:#dbcfb0' value='con' " . ($current_status == 'con' ? "selected='true'" : "") . ">" . $this->get_order_status("con") . "</option>"
            . "<option style='color:#70c1b3' value='comp' " . ($current_status == 'comp' ? "selected='true'" : "") . ">" . $this->get_order_status("comp") . "</option>"
            . "<option style='color:#247ba0' value='ship' " . ($current_status == 'ship' ? "selected='true'" : "") . ">" . $this->get_order_status("ship") . "</option>"
            . "</select>");

        //Add current page headers
        foreach ($_POST as $key => $val) {
            if ($key !== 'order_status') {
                $form .= "<input type='hidden' name='$key' value='$val' />";
            }
        }

        $form .= "</form>";
        return $form;
    }

    public function get_order_items()
    {
        return $this->order_items;
    }
    public function get_order_item($product_id, $media_group_id)
    {
        foreach ($this->order_items as $item) {
            if ($item->get_product_id() == $product_id && $item->get_media_group_id() == $media_group_id) {
                return $item;
            }
        }
        return null;
    }

    public function get_order_date()
    {
        return $this->order_date;
    }

    public function get_completion_date()
    {
        return $this->completion_date;
    }

    public function get_ship_date()
    {
        return $this->ship_date;
    }

    public function get_relevance()
    {
        return $this->relevance;
    }

    public function get_order_detail_link()
    {
        if (null !== $this->get_order_id()) {
            return "<a href='" . $this->order_detail_link . "?order_id=" . $this->get_order_id() . "'>View / Manage Order</a>";
        } else return false;
    }

    public function get_order_images()
    {
        $images = [];
        foreach ($this->get_order_items() as $key => $value) {
            $product = $value->get_product();
            $images[$value->get_product_id()] = $product->get_image_path();
        }
        return $images;
    }

    public function get_user_name()
    {
        $user = new user($this->get_user_email());
        return $user->get_first_name() . " " . $user->get_last_name();
    }

    public static function get_all_ids($order_by_attr, $asc = "1", $limit = null, $where = null, $where_attr = null)
    {
        $sort_attr = order::get_attr_name($order_by_attr);
        $id = order::get_attr_name("order_id");

        $sel = $id . (isset($sort_attr) ? ",$sort_attr" : "");
        $frm = "orders";
        $ord = order::get_attr_order_by($order_by_attr, $asc);

        // query the database using the my_sql api
        $result = my_sql::select($sel, $frm, $where, $where_attr, $ord, null, null, $limit);

        // create orders for each row
        $orders = array();
        foreach ($result as $row) {
            array_push($orders, $row[$id]);
        }

        return $orders;
    }

    public static function get_all($order_by_attr, $asc = "1", $limit = null, $where = null, $where_attr = null)
    {
        $orders = order::get_all_ids($order_by_attr, $asc, $limit, $where, $where_attr);
        foreach ($orders as $index => $order_id) {
            $orders[$index] = new order($order_id);
        }
        return $orders;
    }

    public static function get_attr_order_by($col, $asc)
    {
        switch ($col) {
            case "order_id":
            case "order_detail":
                return "orderID" . ($asc ? " ASC" : " DESC");
                break;
            case "user_email":
                return "userEmail" . ($asc ? " ASC" : " DESC");
                break;
            case "shipping_address":
                return "shippingAddress" . ($asc ? " ASC" : " DESC");
                break;
            case "billing_address":
                return "billingAddress" . ($asc ? " ASC" : " DESC");
                break;
            case "order_date":
                return "orderDate" . ($asc ? " ASC" : " DESC");
                break;
            case "completion_date":
                return "completionDate" . ($asc ? " ASC" : " DESC");
                break;
            case "ship_date":
                return "shipDate" . ($asc ? " ASC" : " DESC");
                break;
            case "search_relevance":
                return null;
            case "order_status_form":
            case "order_status":
            default:
                return "FIELD(orderStatus, 'uncon', 'con', 'comp', 'ship')" . ($asc ? " ASC" : " DESC") . ", orderDate" . ($asc ? " DESC" : " ASC");
                break;
        }
    }

    public static function get_table_header($col)
    {
        switch ($col) {
            case "order_id":
                return "Order ID";
                break;
            case "order_detail":
                return "Order Details";
                break;
            case "user_email":
                return "User Email";
                break;
            case "shipping_address":
                return "Shipping Address";
                break;
            case "billing_address":
                return "Billing Address";
                break;
            case "order_date":
                return "Order Date";
                break;
            case "completion_date":
                return "Completion Date";
                break;
            case "ship_date":
                return "Date Shipped or Picked Up";
                break;
            case "order_status_form":
                return "Change Order Status";
                break;
            case "order_status":
                return "Order Status";
                break;
            case "search_relevance":
                return "Search Relevance";
                break;
            case "user_name":
                return "Customer Name";
                break;
            default:
                return null;
        }
    }

    public static function get_attr_name($attr)
    {
        switch ($attr) {
            case "order_id":
                return "orderID";
                break;
            case "order_detail":
                return "orderDetails";
                break;
            case "user_email":
                return "userEmail";
                break;
            case "shipping_address":
                return "shippingAddress";
                break;
            case "billing_address":
                return "billingAddress";
                break;
            case "order_date":
                return "orderDate";
                break;
            case "completion_date":
                return "completionDate";
                break;
            case "ship_date":
                return "shipDate";
                break;
            case "order_status":
                return "orderStatus";
                break;
            default:
                return null;
        }
    }

    public function get_table_entry($columns, $vertical = false, $editable = false)
    {
        $entry = ($vertical ? "" : "<tr>");
        foreach ($columns as $col) {
            $entry .= ($vertical ? "<tr>" : "");
            switch ($col) {
                case "order_id":
                    $entry .= ($vertical ? "<th>Order ID</th>" : "") . "<td>" . ($this->get_order_id()) . "</td>";
                    break;
                case "user_email":
                    $entry .= ($vertical ? "<th>User Email</th>" : "") . "<td>" . ($this->get_user_email()) . "</td>";
                    break;
                case "shipping_address":
                    $entry .= ($vertical ? "<th>Shipping Address</th>" : "") . "<td>" . ($this->get_shipping_address()) . "</td>" . ($vertical && $editable && false ? "<td><button>Change Address</button></td>" : "");
                    break;
                case "billing_address":
                    $entry .= ($vertical ? "<th>Billing Address</th>" : "") . "<td>" . ($this->get_billing_address()) . "</td>" . ($vertical && $editable && false ? "<td><button>Change Address</button></td>" : "");
                    break;
                case "order_status":
                    $entry .= ($vertical ? "<th>Order Status</th>" : "") . "<td>" . ($this->get_order_status()) . "</td>" . ($vertical && $editable ? ("<td>" . ($this->get_order_status_form()) . "</td>") : "");
                    break;
                case "order_status_form":
                    $entry .= ($vertical ? "<th>Order Status</th>" : "") . "<td>" . ($this->get_order_status_form()) . "</td>";
                    break;
                case "order_date":
                    $entry .= ($vertical ? "<th>Order Date</th>" : "") . "<td>" . ($this->get_order_date()) . "</td>";
                    break;
                case "completion_date":
                    $entry .= ($vertical ? "<th>Completion Date</th>" : "") . "<td>" . ($this->get_completion_date()) . "</td>";
                    break;
                case "ship_date":
                    $entry .= ($vertical ? "<th>Ship / Pickup Date</th>" : "") . "<td>" . ($this->get_ship_date()) . "</td>";
                    break;
                case "order_detail":
                    $entry .= ($vertical ? "<th>Order Details</th>" : "") . "<td>" . ($this->get_order_detail_link()) . "</td>";
                    break;
                case "search_relevance":
                    $entry .= ($vertical ? "<th>" . $this->get_table_header($col) . "</th>" : "") . "<td>" . $this->get_relevance() . "</td>";
                    break;
                case "user_name":
                    $entry .= ($vertical ? "<th>" . $this->get_table_header($col) . "</th>" : "") . "<td>" . $this->get_user_name() . "</td>";
                    break;
            }
            $entry .= ($vertical ? "</tr>" : "");
        }
        $entry .= ($vertical ? "" : "</tr>");
        return $entry;
    }

    public function get_item_table($columns, $editable = false)
    {
        $table = "<table><tr>";
        foreach ($columns as $col) {
            $table .= ("<th>" . order_item::get_table_header($col) . "</th>");
        }
        $table .= "</tr>";
        foreach ($this->order_items as $item) {
            $table .= ("<tr>" . $item->get_horizontal_table_entry($columns, $editable) . "</tr>");
        }
        $table .= "</table>";
        return $table;
    }


    /********************
     * Define the setters
     * ******************
     */

    public function set_shipping_address($address_id)
    {
        $this->shipping_address = $address_id;
        return my_sql::update("orders", array("shippingAddress"), array($address_id), "orderID=:0", array($this->get_order_id()));
    }

    public function set_billing_address($address_id)
    {
        $this->billing_address = $address_id;
        return my_sql::update("orders", array("billingAddress"), array($address_id), "orderID=:0", array($this->get_order_id()));
    }

    public function set_order_status($status = "uncon")
    {
        //Filter status options
        switch ($status) {
            case "uncon":
                break;
            case "con":
                break;
            case "comp":
                $this->set_completion_date();
                break;
            case "ship":
                $this->set_ship_date();
                break;
            default:
                return false;
        }

        $this->order_status = $status;
        return my_sql::update("orders", array("orderStatus"), array($status), "orderID=:0", array($this->get_order_id()));
    }

    public function set_order_date($reset = false)
    {
        $date = $reset ? null : (new DateTime())->setTimezone(new DateTimeZone('America/Vancouver'));
        $datetime = $reset ? null : $date->format('Y-m-d H:i:s');
        $this->order_date = $datetime;
        return my_sql::update("orders", array("orderDate"), array($datetime), "orderID=:0", array($this->get_order_id()));
    }

    public function set_completion_date($reset = false)
    {
        $date = $reset ? null : (new DateTime())->setTimezone(new DateTimeZone('America/Vancouver'));
        $datetime = $reset ? null : $date->format('Y-m-d H:i:s');
        $this->completion_date = $datetime;
        return my_sql::update("orders", array("completionDate"), array($datetime), "orderID=:0", array($this->get_order_id()));
    }

    public function set_ship_date($reset = false)
    {
        $date = $reset ? null : (new DateTime())->setTimezone(new DateTimeZone('America/Vancouver'));
        $datetime = $reset ? null : $date->format('Y-m-d H:i:s');
        $this->ship_date = $datetime;
        return my_sql::update("orders", array("shipDate"), array($datetime), "orderID=:0", array($this->get_order_id()));
    }

    public function set_relevance($value)
    {
        $this->relevance = $value;
    }

    public function add_order_item($product_id, $quantity, $media_group_id, $ship_product)
    {
        // mysql query to create order item
        $query = my_sql::insert("orderitem", array("productID", "orderID", "quantity", "mediaGroupID", "shipProduct"), array($product_id, $this->get_order_id(), $quantity, $media_group_id, $ship_product));
        // Add item to orderitem array
        if ($query != false) {
            array_push($this->order_items, new order_item($this->get_order_id(), $product_id, $media_group_id));
            return 1;
        } else {
            return 0;
        }
    }

    public function add_cart_items($user_cart)
    {
        $confirm = "";
        // Get all cart items
        $cart_items = $user_cart->get_cart_items();
        // Get productID, mediaGroupID, quantity and shipProduct
        // Create orderitem for each
        foreach ($cart_items as $item) {
            $result = $this->add_order_item($item->get_product_id(), $item->get_quantity(), $item->get_media_group_id(), $item->get_ship_product());
            $confirm = false;
            // Check that item was successfully added
            // If true then delete the cart item
            if ($result) {
                $delete = $user_cart->delete_cart_item($item->get_product_id(), $item->get_media_group_id());
                if (!$delete) {
                    return false;
                } else {
                    $confirm = true;
                }
            } else {
                return false;
            }
        }
        return $confirm;
    }

    private function get_order_id_email_string()
    {
        $order_id_info = ("<tr style='margin: 0; padding: 0; position: relative; top: 30%; padding-left: 2%; text-align: center;'>
            <td><h1 style='margin: 0; padding: 0; position: relative; font-family: \"Ubuntu Condensed\";' >Your order number is: " . $this->get_order_id() . "</h1></td>	
            </tr>");
        return $order_id_info;
    }

    private function get_order_info_print($print_display)
    {
        $date = date_create($this->get_order_date());
        $o_d = date_format($date, "Y-m-d");
        $order_info = "";
        if ($print_display == "email") {
            $order_info = ("<table style='margin: 0; padding: 0; width: 100%; padding-top: 2%; color: #000;'>
                <tr style='margin: 0; padding: 0; text-align: center;' >
                <td style='margin: 0; padding: 0; width:37%;'>
                <p  style='margin: 0; padding: 0; display: inline;padding-right: 0.5%; font-size: 20px; font-family: \"Ubuntu Condensed\";' >Order number: " . $this->get_order_id() . " </p>
                </td>
                <td style='margin: 0; padding: 0; width:30%; text-align: left;'>
                <p  style='margin: 0; padding: 0; display: inline; padding-left: 1%; padding-right: 0.5%; font-size: 20px; font-family: \"Ubuntu Condensed\";'> Date Ordered: " . $o_d .  " </p>
                </td>
                <td style='margin: 0; padding: 0; width:30%; text-align: center;'>
                <p  style='margin: 0; padding: 0; display: inline; padding-left: 1%; font-size: 20px; font-family: \"Ubuntu Condensed\";'> Order Status: " . $this->get_order_status() . "</p>
                </td>
                </tr>
                </table>");
        }
        if ($print_display == "page") {
            $order_info = ('<div class="row justify-content-center order_confirmed_page" id="order_top" align="center">
                <div class="col-sm-12 col-xl-12">
                <h4 class="h4 order_header" align="left">Order Information</h4>
                </div>
                <div class="col-sm-12 col-xl-12">
                <hr class="order_hr">
                <br>
                </div>
                <div class="col-12 col-sm-12 col-md-3 col-xl-4" >
                <h3 class="h3 order_item_cat">Order ID: ' . $this->get_order_id() . '</h3>
                </div>
                <div class="col-12 col-sm-12 col-md-3 col-xl-4" >
                <h3 class="h3 order_item_cat">Date Ordered: ' . $o_d . '</h3>
                </div>
                <div class="col-12 col-sm-12 col-md-3 col-xl-4" >
                <h3 class="h3 order_item_cat">Order Status: ' . $this->get_order_status() . ' </h3>
                </div>
                </div>');
        }
        return $order_info;
    }

    private function get_order_items_print($print_display)
    {
        $order_info_items = "";
        $total_price = 0;
        if ($print_display == "email") {
            foreach ($this->order_items as $item) {
                $total_price = ($item->get_product()->get_price() * $item->get_quantity()) + $total_price;
                $order_info_items .=
                    "<tr style='margin: 0; padding: 0;' >
                    <td style='margin: 0; padding: 0; width: 20%; height: 20%; min-width: 100px; min-height: 100px;'>
                        <div style='margin: 0; padding: 0; position: relative; width: 100%; height: 100%;'>
                            <img alt='PHPMAiler' style='margin: 0; padding: 0; width: 100%;' src='cid:" . $item->get_product()->get_product_id() . "'>
                        </div>
                    </td>
                    <td style='margin: 0; padding: 0; width: 35%; height: 20%;'>
                        <div style='margin: 0; padding: 0;' >
                            <p style='margin: 0; padding: 0; padding-top: 5%; padding-left: 1%; font-family: \"Ubuntu Condensed\";'>" . $item->get_product()->get_name() . "</p>
                        </div>
                    </td>
                    <td style='margin: 0; padding: 0; width: 15%; height: 15%;'>
                        <div style='margin: 0; padding: 0; padding-left:5%;'>
                            <p style='margin: 0; padding: 0; padding-top: 10%; font-family: \"Ubuntu Condensed\";'>" . $item->get_quantity() . "</p>
                        </div>
                    </td>
                    <td style='margin: 0; padding: 0; width: 15%; height: 15%;'>
                        <div style='margin: 0; padding: 0; padding-left: 5%;'>
                            <p style='margin: 0; padding: 0; padding-top: 10%; font-family: \"Ubuntu Condensed\";'>" . $item->get_product()->get_price() . "</p>
                        </div>
                    </td>
                    <td style='margin: 0; padding: 0; width: 15%; height: 15%;'>
                        <div style='margin: 0; padding: 0; padding-left: 5%;'>
                            <p style='margin: 0; padding: 0; padding-top: 10%; font-family: \"Ubuntu Condensed\";'>" . ($item->is_ship_product() ? "Shipped" : "Pickup") . "</p>
                        </div>
                    </td>
                </tr>";
            }
            $order_info_items .= ("<tr style='margin: 0; padding: 0;'>
                <th style='margin: 0; padding: 0;'></th>
                <th style='margin: 0; padding: 0;'></th>
                <th style='margin: 0; padding: 0; padding-top: 4%; padding-right: 1%; font-family: \"Ubuntu Condensed\";'>Total Price: </th>
                <th style='margin: 0; padding: 0; padding-top: 4%; padding-right: 6%; font-family: \"Ubuntu Condensed\";'> $ " . $total_price . "</th>
                </tr>");
        }
        if ($print_display == "page") {
            $order_info_items = '
            <div class="row justify-content-center order_item_title_container order_confirmed_page" align="center">
                <div class="col-12">
                    <h4 class="h4 order_header" align="left">Items Ordered</h4>
                </div>
                <div class="col-12">
                    <hr class="order_hr" >
                    <br>
                </div>
                <div class="col-3"></div>
                <div class="col-2" >
                    <h4 class="h4 order_item_cat">Store Item</h4>
                </div>
                <div class="col-2">
                    <h4 class="h4 order_item_cat">Quantity</h4>
                </div>
                <div class="col-2">
                    <h4 class="h4 order_item_cat">Price</h4>
                </div>
                <div class="col-2">
                    <h4 class="h4 order_item_cat">Shipped</h4>
                </div>
            </div>';
            foreach ($this->order_items as $item) {
                $total_price = ($item->get_product()->get_price() * $item->get_quantity()) + $total_price;
                $order_info_items .=
                    '<div class="row justify-content-center order_items_holder order_confirmed_page" align="center">
                        <div class="col-3 order_img">
                            <img src="' . $item->get_product()->get_image_path() . '" class="img-thumbnail" alt="...">
                        </div>
                        <div class="col-2 order_detail">
                            <p class="p order_item">' . $item->get_product()->get_name() . '</p>
                        </div>
                        <div class="col-2 order_detail">
                            <p class="p order_item">' . $item->get_quantity() . '</p>
                        </div>
                        <div class="col-2 order_detail">
                            <p class="p order_item">' . $item->get_product()->get_price() . '</p>
                        </div>
                        <div class="col-2 order_detail">
                            <p class="p order_item">' . ($item->is_ship_product() ? "Shipped" : "Pickup") . '</p>
                        </div>
                    </div>';
            }
            $order_info_items .=
                '<div class="row justify-content-center order_info_container order_confirmed_page" align="center">
                    <div class="col-2 order_img">
                    </div>
                    <div class="col-2 order_detail">
                        <p class="p order_header">Total: </p>
                    </div>
                    <div class="col-2 order_detail">
                        <p class="p order_header">$ ' . $total_price . '</p>
                    </div>
                </div>';
        }
        return $order_info_items;
    }

    private function get_order_user_info($user, $print_display)
    {
        $order_user_info = "";
        if ($print_display == "email") {
            $order_user_info =
                "<div style='margin: 0; padding: 0; font-size: 18px; padding-top: 5%; color: #000;'>
            <h3 style='margin: 0; padding: 0; color: #000; font-family: \"Ubuntu Condensed\";'><strong style='margin: 0; padding: 0;' >Name</strong></h3> 
            <div  style='margin: 0; padding: 0; padding-top: 2%; font-size: 15px;'>
            <p style='margin: 0; padding: 0; font-family: \"Ubuntu Condensed\";'>" . $user->get_first_name() . " " . $user->get_last_name() .  "</p>
            </div>
            </div>
            <div  style='margin: 0; padding: 0; font-size: 18px; padding-top: 5%; color: #000;'>
            <h3 style='margin: 0; padding: 0; color: #000; font-family: \"Ubuntu Condensed\";'><strong style='margin: 0; padding: 0;' >Email</strong></h3>
            <div  style='margin: 0; padding: 0; padding-top: 2%; font-size: 15px;'>
            <p style='margin: 0; padding: 0; font-family: \"Ubuntu Condensed\";'>" . $user->get_user_email() . "</p>
            </div>
            </div>
            </div>
            <div style='margin: 0; padding: 0; padding-bottom: 3%; background-color: white; min-height: 15rem; border-radius: 5px 5px 0px 0px; min-height: 15rem; max-width: 80%; border-radius: 0px 0px 5px 5px;'>
            <div style='margin: 0; padding: 0; color: #000;' >
            <h2  style='margin: 0; padding: 0; text-align: left; padding-top: 3%; padding-bottom: 1%; padding-left: 2%; font-size: 25px; position: relative; font-family: \"Ubuntu Condensed\";'>Address Information</h2>
            <hr style='margin: 0; padding: 0; width: 95%;'>
            </div>
            <div>
            <div  style='margin: 0; padding: 0; font-size: 18px; padding-top: 5%; color: #000;'>
            <h3 style='margin: 0; padding: 0; color: #000; font-family: \"Ubuntu Condensed\";'><strong style='margin: 0; padding: 0;' >Shipping Address</strong></h3>
            <div  style='margin: 0; padding: 0; padding-top: 2%; font-size: 15px;'>
            <p style='margin: 0; padding: 0; font-family: \"Ubuntu Condensed\";'>" . $this->get_shipping_address() . "</p>
            </div>
            </div>
            <div  style='margin: 0; padding: 0; font-size: 18px; padding-top: 5%; color: #000;'>
            <h3 style='margin: 0; padding: 0; color: #000; font-family: \"Ubuntu Condensed\";'  ><strong style='margin: 0; padding: 0;' >Billing Address</strong></h3>
            <div  style='margin: 0; padding: 0; padding-top: 2%; font-size: 15px;'>
            <p style='margin: 0; padding: 0; font-family: \"Ubuntu Condensed\";' >" . $this->get_billing_address() . "</p>
            </div>
            </div>";
        }
        if ($print_display == "page") {
            $order_user_info =
                '<div class="row justify-content-center order_confirmed_page" align="center">
                <div class="col-12">
                    <h4 class="h4 order_header" align="left">Customer Information</h4>
                </div>
                <div class="col-12">
                    <hr class="order_hr">
                    <br>
                </div>
                <div class="col-xs-1 col-lg-12">
                    <h3 class="h3 order_header">Name</h3>
                </div>
                <div class="col-xs-1 col-lg-12">
                    <p class="p order_item_cat">' . $user->get_first_name() . " " . $user->get_last_name() .  '</p>
                </div>
                <div class="col-xs-1 col-lg-12">
                    <h3 class="h3 order_header">Email</h3>
                </div>
                <div class="col-xs-1 col-lg-12">
                    <p class="p order_item_cat">' . $user->get_user_email() . '</p>
                </div>
            </div>
            <div class="row order_confirmed_page" id="order_bottom" align="center">
                <div class="col-12">
                    <h4 class="h4 order_header" style="padding-top: 2%;" align="left">Address Information</h4>
                </div>
                <div class="col-12">
                    <hr style="border-color: #FF0080; margin: 0;">
                    <br>
                </div>
                <div class="col-12">
                    <h3 class="h3 order_header">Shipping Address</h3>
                </div>
                <div class="col-12">
                    <p class="p order_item_cat">' . $this->get_shipping_address() . '</p>
                </div>
                <div class="col-12">
                    <h3 class="h3 order_header">Billing Address</h3>
                </div>
                <div class="col-12">
                    <p class="p order_item_cat">' . $this->get_billing_address() . '</p>
                </div>
            </div>';
        }

        return $order_user_info;
    }

    public function get_order_email($user)
    {
        $email = file_get_contents("../src/mail/mail_templates/order_confirmed/order_confirmed_head.html");

        $email .= $this->get_order_id_email_string("email") . file_get_contents("../src/mail/mail_templates/order_confirmed/order_confirmed_email_pt_1.html");

        $email .= $this->get_order_info_print("email") . file_get_contents("../src/mail/mail_templates/order_confirmed/order_confirmed_email_pt_2.html");

        $email .= $this->get_order_items_print("email") . file_get_contents("../src/mail/mail_templates/order_confirmed/order_confirmed_email_pt_3.html");

        $email .= $this->get_order_user_info($user, "email") . file_get_contents("../src/mail/mail_templates/order_confirmed/order_confirmed_email_foot.html");

        return $email;
    }

    public function get_order_confirmed_page($user)
    {
        return $this->get_order_info_print("page") . $this->get_order_items_print("page") . $this->get_order_user_info($user, "page");
    }
}
