<?php
require_once 'my_sql.php';
require_once 'general_item.php';
require_once 'product.php';

class order_item extends general_item
{
    private $order_id, $ship_product, $order_status;

    public function __construct($order_id, $product_id, $media_group_id)
    {
        //Query the item data
        $order_item_data = my_sql::select("*", "orderitem", "orderID=:0 AND productID=:1 AND mediaGroupID=:2", array("$order_id", "$product_id", "$media_group_id"));
        if (count($order_item_data) == 1) {
            $order_item_data = $order_item_data[0];
            //Call the parent constructor
            parent::__construct($order_item_data['productID'], $order_item_data['mediaGroupID'], $order_item_data['quantity']);

            //Set the remaining variables
            $this->order_id = $order_item_data['orderID'];
            $this->ship_product = $order_item_data['shipProduct'];
            $this->order_status = $order_item_data['orderStatus'];
        } else {
            echo "Order item identified by $order_id, $product_id, and $media_group_id, does not exist!";
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

    private function get_where_condition()
    {
        return "orderID=:0 AND productID=:1 AND mediaGroupID=:2";
    }

    private function get_where_attr()
    {
        return array($this->get_order_id(), $this->get_product_id(), $this->get_media_group_id());
    }

    public function get_order_status($status = null)
    {
        $status = (isset($status) ? $status : $this->order_status);
        switch ($status) {
            case 'uncomp':
                return "<b style='color:#ff0080'>Incomplete</b>";
                break;
            case 'comp':
                return "<b style='color:#70c1b3'>Completed</b>";
                break;
            default:
                return "Unknown";
        }
    }

    public function get_order_status_form()
    {
        $current_status = $this->order_status;

        //Filter status options
        switch ($current_status) {
            case "uncomp":
                break;
            case "comp":
                break;
            default:
                return false;
        }

        //Add form and options
        $form = ("<form method='post' onchange='this.submit();'>"
            . "<select name='order_item_status'>"
            . "<option style='color:#ff0080' value='uncomp' " . ($current_status == 'uncomp' ? "selected='true'" : "") . ">" . $this->get_order_status("uncomp") . "</option>"
            . "<option style='color:#70c1b3' value='comp' " . ($current_status == 'comp' ? "selected='true'" : "") . ">" . $this->get_order_status("comp") . "</option>"
            . "</select>"
            . "<input type='hidden' name='media_group_id' value='" . $this->get_media_group_id() . "' />"
            . "<input type='hidden' name='product_id' value='" . $this->get_product_id() . "' />");

        //Add current page headers
        foreach ($_POST as $key => $val) {
            if ($key !== 'order_item_status' && $key !== 'media_group_id' && $key !== 'product_id') {
                $form .= "<input type='hidden' name='$key' value='$val' />";
            }
        }

        $form .= "</form>";
        return $form;
    }

    public function has_attached_media()
    {
        $has_media = my_sql::exists("image", "mediaGroupID=:0", array($this->get_media_group_id())) || my_sql::exists("audio", "mediaGroupID=:0", array($this->get_media_group_id()));
        return $has_media;
    }

    public function get_attached_media_download()
    {
        if ($this->get_media_group_id() !== "1" && $this->has_attached_media()) {
            $link = "<a href='download_order_media.php?media_group_id=" . $this->get_media_group_id() . "' >Download Attached Media</a>";
            return $link;
        } else {
            return null;
        }
    }

    public function get_delete_media_link()
    {
        if ($this->get_media_group_id() !== "1" && $this->has_attached_media()) {
            $link = "<a href='javascript:' onclick='
                $.ajax(\"delete_order_media.php\", {
                    type:\"POST\",
                    data:{
                        media_group_id:\"" . $this->get_media_group_id() . "\"
                    },
                    success:function(data){
                        console.log(data);
                        alert(\"All media for order item has been deleted.\");
                        location.reload();
                    }
                });
            '>Delete Attached Media</a>";
            return $link;
        } else {
            return null;
        }
    }

    public function get_table_data($column)
    {
        switch ($column) {
            case "order_id":
                return $this->get_order_id();
                break;
            case "product_id":
                return $this->get_user_email();
                break;
            case "quantity":
                return $this->get_quantity();
                break;
            case "media_group_id":
                return $this->get_media_group_id();
                break;
            case "media_audio":
                return "<audio src=''>order item audio files</audio>";
                break;
            case "media_image":
                return "<image src=''>order item image</image>";
                break;
            case "ship_product":
                return $this->is_ship_product() ? "Shipment" : "Store Retrieval";
                break;
            case "order_status":
                return $this->get_order_status();
                break;
            case "product_name":
                return ($this->get_product())->get_name();
                break;
            case "product_description":
                return ($this->get_product())->get_description();
                break;
            case "product_price":
                return ($this->get_product())->get_price();
                break;
            case "product_image":
                return ($this->get_product())->get_small_main_image();
                break;
            case "attached_media":
                return $this->get_attached_media_download();
                break;
            case "delete_media":
                return $this->get_delete_media_link();
                break;
        }
    }

    public function get_editable_table_data($column)
    {
        switch ($column) {
            case "media_audio":
                return "<a>Download Audio</a>";
                break;
            case "media_image":
                return "<a>Download Image</a>";
                break;
            case "order_status":
                return $this->get_order_status_form();
                break;
            default:
                return null;
        }
    }

    public static function get_table_header($column)
    {
        switch ($column) {
            case "order_id":
                return "Order ID";
                break;
            case "product_id":
                return "Product ID";
                break;
            case "quantity":
                return "Quantity";
                break;
            case "media_group_id":
                return "Media Group ID";
                break;
            case "media_audio":
                return "Included Audio Files";
                break;
            case "media_image":
                return "Included Image Files";
                break;
            case "ship_product":
                return "Delivery Method";
                break;
            case "order_status":
                return "Order Status";
                break;
            case "product_name":
                return "Product Name";
                break;
            case "product_description":
                return "Product Description";
                break;
            case "product_price":
                return "Product Price";
                break;
            case "product_image":
                return "Product Image";
                break;
            case "attached_media":
                return "Attached Media";
                break;
            case "delete_media":
                return "Delete Media";
                break;
            default:
                return null;
                break;
        }
    }

    public function get_horizontal_table_entry($columns, $editable = false)
    {
        $row = "<tr>";
        foreach ($columns as $col) {
            $row .= ("<td>"
                . $this->get_table_data($col)
                . ($editable ? $this->get_editable_table_data($col) : "")
                . "</td>");
        }
        $row .= "</tr>";
        return $row;
    }

    public function get_vertical_table($columns, $editable = false)
    {
        $table = "<table>";
        foreach ($columns as $col) {
            $table .= ("<tr>"
                . "<th>" . $this->get_table_header($col) . "</th>"
                . "<td>" . $this->get_table_data($col, $editable) . "</td>"
                . "</tr>");
        }
        $table .= "</table>";
        return $table;
    }

    public function is_ship_product()
    {
        return (isset($this->ship_product) && $this->ship_product ? true : false);
    }


    /********************
     * Define the setters
     * ******************
     */

    public function set_order_status($status = 'uncomp')
    {
        //filter status options
        switch ($status) {
            case 'uncomp':
                break;
            case 'comp':
                break;
            default:
                return false;
        }

        //Set the status
        $this->order_status = $status;
        return my_sql::update("orderitem", array("orderStatus"), array($status), $this->get_where_condition(), $this->get_where_attr());
    }
}
