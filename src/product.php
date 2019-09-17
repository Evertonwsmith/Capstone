<?php
require_once 'my_sql.php';
include_once 'my_sql_cred.php';

class product
{
    private $product_id, $media_group_id, $name, $description, $price, $max_quantity, $requires_media_group, $is_public;
    private $relevance = null;

    public static $disabled_message = "The product must be unpublished before editing this information.";

    function __construct($product_id)
    {
        if ($product_id == "new") {
            $this->product_id = null;
            $this->media_group_id = null;
            $this->name = "New Product Name";
            $this->description = "";
            $this->price = 0.00;
            $this->max_quantity = null;
            $this->requires_media_group = "0";
            $this->is_public = "0";
            $product_id = $this->insert_into_database();
        }

        //Construct product
        $product_data = my_sql::select("*", "product", "productID=:0", array("$product_id"));
        if (count($product_data) == 1) {
            $product_data = $product_data[0];
            //Set the attributes
            $this->product_id = isset($product_data['productID']) ? $product_data['productID'] : null;
            $this->media_group_id = isset($product_data['mediaGroupID']) ? $product_data['mediaGroupID'] : null;
            $this->name = isset($product_data['name']) ? $product_data['name'] : null;
            $this->description = isset($product_data['description']) ? $product_data['description'] : null;
            $this->price = isset($product_data['price']) ? $product_data['price'] : null;
            $this->max_quantity = isset($product_data['maxQuantity']) ? $product_data['maxQuantity'] : null;
            $this->requires_media_group = isset($product_data['requiresMediaGroup']) ? $product_data['requiresMediaGroup'] : null;
            $this->is_public = isset($product_data['isPublic']) ? $product_data['isPublic'] : null;
        } else {
            //echo "Product, $product_id, does not exist!";
            return null;
        }
    }

    /********************
     * Define the getters
     * ******************
     */

    public function get_product_id()
    {
        return $this->product_id;
    }

    public function get_media_group_id()
    {
        return $this->media_group_id;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_description()
    {
        return $this->description;
    }

    public function get_tagless_description()
    {
        return strip_tags($this->description);
    }

    public function get_price()
    {
        return $this->price;
    }

    public function get_max_quantity()
    {
        return isset($this->max_quantity) && $this->max_quantity !== "" ? $this->max_quantity : "-1";
    }

    public function get_image_path()
    {
        $path = "../src/mail/mail_templates/black-vinyl-record-store-day-flat-concept-vector-illustration.jpg";
        $result = my_sql::select("filename", "image", "mediaGroupID=:0", array($this->get_media_group_id()));
        if ($result != false && count($result) > 0) {
            $path = "../" . $result[0]['filename'];
        }
        return $path;
    }

    public function get_main_image()
    {
        $path = "pages/blank-profile-picture-973460_960_720.png";
        $result = my_sql::select("filename", "image", "mediaGroupID=:0", array($this->get_media_group_id()));
        if ($result != false && count($result) > 0) {
            $path = $result[0]['filename'];
        }
        return "<img src='../$path' alt='No image available...'></img>";
    }

    public function get_small_main_image()
    {
        return "<div class='img-profile-container'>" . $this->get_main_image() . "</div>";
    }

    public function requires_media_group()
    {
        return isset($this->requires_media_group) && $this->requires_media_group ? true : false;
    }

    public function is_public()
    {
        return isset($this->is_public) && $this->is_public ? true : false;
    }

    public function get_relevance()
    {
        return $this->relevance;
    }

    public function is_artist_only()
    {
        return my_sql::exists("artistonlyproduct", "productID=:0", array($this->get_product_id()));
    }

    public static function get_new_product_button()
    {
        $form = ("<form action='product_detail.php' method='post'>"
            . "<input type='submit' name='sumbit' value='Add New Product' />"
            . "<input type='hidden' name='product_id' value='new' />"
            . "</form>");
        return $form;
    }

    public static function get_all_ids($order_by_attr, $asc = "1", $limit = null, $where = null, $where_attr = null, $artist_only = false)
    {
        $sort_attr = product::get_attr_name($order_by_attr);
        $id = product::get_attr_name("product_id");

        $sel = ($artist_only ? "product." : "") . $id . (isset($sort_attr) ? "," . ($artist_only ? "product." : "") . $sort_attr : "");
        $frm = $artist_only ? "product RIGHT JOIN artistonlyproduct ON product.productID=artistonlyproduct.productID" : "product";
        $ord = product::get_attr_order_by($order_by_attr, $asc);

        // query the database using the my_sql api
        $result = my_sql::select($sel, $frm, $where, $where_attr, $ord, null, null, $limit);

        // create products for each row
        $ids = array();
        foreach ($result as $row) {
            array_push($ids, $row[$id]);
        }

        return $ids;
    }

    public static function get_all($order_by_attr, $asc = "1", $limit = null, $where = null, $where_attr = null)
    {
        $products = product::get_all_ids($order_by_attr, $asc, $limit, $where, $where_attr);
        foreach ($products as $index => $prod_id) {
            $products[$index] = new product($prod_id);
        }
        return $products;
    }

    public function get_table_entry($columns)
    {
        $row = "<tr>";
        foreach ($columns as $col) {
            $row .= "<td>" . $this->get_table_data($col) . "</td>";
        }
        $row .= "</tr>";
        return $row;
    }

    public function get_vertical_table($columns, $editable = false)
    {
        $table = "<table class='vertical'>";
        foreach ($columns as $col) {
            $table .= ("<tr>"
                . "<th>" . $this->get_table_header($col, $editable) . "</th>"
                . "<td>" . $this->get_table_data($col, $editable) . "</td>"
                . "</tr>");
        }
        $table .= "</table>";
        //Add autosave script
        $table .= "<script>
            function product_save(post, reload){
                $.ajax('product_save.php', {
                    type: 'POST',  // http method
                    data: post,
                    success: function (data, status, xhr) {
                        console.log(data);
                        if(reload){
                            location.reload();
                        }
                    },
                    error: function (jqXhr, textStatus, errorMessage) {
                        //console.log('Error' + errorMessage);
                    }
                });
            }
        </script>";
        return $table;
    }

    public static function get_attr_order_by($col, $asc)
    {
        switch ($col) {
            case "media_group_id":
                return "mediaGroupID" . ($asc ? " ASC" : " DESC");
                break;
            case "name":
                return "name" . ($asc ? " ASC" : " DESC");
                break;
            case "description":
                return "description" . ($asc ? " ASC" : " DESC");
                break;
            case "price":
                return "price" . ($asc ? " ASC" : " DESC");
                break;
            case "max_quantity":
                return "maxQuantity" . ($asc ? " ASC" : " DESC");
                break;
            case "requires_media_group":
                return "requiresMediaGroup" . ($asc ? " ASC" : " DESC");
                break;
            case "is_public":
                return "isPublic" . ($asc ? " ASC" : " DESC");
                break;
            case "search_relevance":
                return null;
            case "product_id":
            case "product_detail":
            case "main_image":
            default:
                return "productID" . ($asc ? " ASC" : " DESC");
                break;
        }
    }

    public static function get_table_header($column, $editable = false)
    {
        switch ($column) {
            case "product_id":
                return "Product ID";
                break;
            case "media_group_id":
                return "Media Group ID";
                break;
            case "name":
                return $editable ? "<span class='required'>*</span> Name" : "Name";
                break;
            case "description":
                return $editable ? "<span class='required'>*</span> 
                    Description 
                    <div class='help-hover'><b>[?]</b>
                        <div>
                            To format the product text, you can use HTML code and inline CSS. Help on this topic can be found <a href='https://www.w3schools.com/html/html_basic.asp'>here</a>.
                        </div>
                    </div>" : "Description";
                break;
            case "price":
                return $editable ? "<span class='required'>*</span> Price" : "Price";
                break;
            case "max_quantity":
                return $editable ? "<span class='required'>*</span> Max Quantity" : "Max Quantity";
                break;
            case "requires_media_group":
                return $editable ? "<span class='required'>*</span> Media Required" : "Media Required";
                break;
            case "artist_only":
                return $editable ? "<span class='required'>*</span> Artist-Only Product" : "Artist-Only Product";
                break;
            case "main_image":
                return "Product Image";
                break;
            case "is_public":
                return "Product Visibility";
                break;
            case "product_detail":
                return "Product Details";
                break;
            case "search_relevance":
                return "Search Relevance";
                break;
            default:
                return null;
        }
    }

    public function get_table_data($column, $editable = false)
    {
        switch ($column) {
            case "product_id":
                return $this->get_product_id();
                break;
            case "media_group_id":
                return $this->get_media_group_id();
                break;
            case "name":
                return $editable ? $this->get_name_edit_input() : $this->get_name();
                break;
            case "description":
                return $editable ? $this->get_description_edit_input() : $this->get_description();
                break;
            case "price":
                return $editable ? $this->get_price_edit_input() : ("$" . $this->get_price());
                break;
            case "max_quantity":
                return $editable ? $this->get_max_quantity_edit_input() : ($this->get_max_quantity() == -1 ? "-" : $this->get_max_quantity());
                break;
            case "requires_media_group":
                return $editable ? $this->get_media_requirement_edit_input() : (($this->requires_media_group() !== null && $this->requires_media_group()) ? "yes" : "-");
                break;
            case "artist_only":
                return $editable ? $this->get_artist_only_edit_input() : ($this->is_artist_only() ? "yes" : "-");
                break;
            case "main_image":
                return $editable ? $this->get_main_image_edit_input() : $this->get_small_main_image();
                break;
            case "is_public":
                return ($this->is_public() ? "<b style='color:#70c1b3'>Published</b>" : "<b style='color:#ff0080'>Unpublished</b>");
                break;
            case "product_detail":
                return $this->get_product_detail_link();
                break;
            case "search_relevance":
                return $this->get_relevance();
                break;
            default:
                return null;
        }
    }

    public static function get_attr_name($attr)
    {
        switch ($attr) {
            case "product_id":
                return "productID";
                break;
            case "media_group_id":
                return "mediaGroupID";
                break;
            case "name":
                return "name";
                break;
            case "description":
                return "description";
                break;
            case "price":
                return "price";
                break;
            case "max_quantity":
                return "maxQuantity";
                break;
            case "requires_media_group":
                return "requiresMediaGroup";
                break;
            case "is_public":
                return "isPublic";
                break;
            default:
                return null;
        }
    }

    public function get_product_detail_link()
    {
        return "<a href=\"product_detail.php?product_id=" . $this->get_product_id() . "\">View / Edit Product</a>";
    }

    public function get_name_edit_input()
    {
        $attr_name = "product_name";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='" . product::$disabled_message . "'" : "";
        $autosave = "onchange='product_save({product_id: " . $this->get_product_id() . ", $attr_name: this.value})'";
        $input = "<input type='text' name='$attr_name' value='' placeholder='" . $this->get_name() . "' $disabled $autosave />";
        $input .= $this->is_public() ? "<div class='error'>" . product::$disabled_message . "</div>" : "";
        return $input;
    }

    public function get_description_edit_input($rows = 12, $cols = 80)
    {
        $attr_name = "product_description";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='" . product::$disabled_message . "'" : "";
        $autosave = "onchange='product_save({product_id: " . $this->get_product_id() . ", $attr_name: this.value})'";
        $input = ("<textarea rows='$rows' cols='$cols' maxlength='4000' name='$attr_name' $disabled $autosave>"
            . $this->get_description()
            . "</textarea>");
        $input .= $this->is_public() ? "<div class='error'>" . product::$disabled_message . "</div>" : "";
        $input .= "<button class='' onclick='toggle_quick_copy(this);'>Quick Copy Menu</button><br>
            <div id='quick-copy' style='display:none;border:1px solid #666;border-radius:0.5rem;padding:1rem;margin:0.5rem;'>
                <div class='row justify-content-center'>";
        $copy_text = ["New Line" => "<br>", "Bold" => "<strong>  </strong>", "Italics" => "<i>  </i>", "Underline" => "<ins>  </ins>", "Strike Through" => "<del>  </del>", "Subscript" => "<sub>  </sub>", "Superscript" => "<sup>  </sup>", "Bullet Point" => "<li>  </li>"];
        foreach ($copy_text as $name => $text) {
            $id_name = str_replace(" ", "_", $name);
            $input .= "
                <div style='margin:0.5rem;'>
                    <div class='copy-text-title'>$name</div>
                    <div class='text-copy-container'>
                        <div id='" . $id_name . "_toast' class='toast hide'>
                            <div class='toast-body'>Selection has been copied to clipboard!</div>
                        </div>
                        <div class='text-copy' onmousedown='$(this).find(\"input\").click();'>
                            <input class='copy-only' disable='true' aria-disable='true' value ='$text' onclick='this.select();document.execCommand(\"copy\");$(\"#" . $id_name . "_toast\").toast(\"show\");' />
                        </div>
                    </div>
                </div>";
        }
        $input .= "<script>
        function toggle_quick_copy(elem){
            if($('#quick-copy').css('display')==='none'){
                $('#quick-copy').css({'display':'inline-block'});
            } else {
                $('#quick-copy').css({'display':'none'});
            }
            $(elem).toggleClass('current-page');
        }
        </script>";
        $input .= "</div>
            </div><br>";
        return $input;
    }

    public function get_price_edit_input()
    {
        $attr_name = "product_price";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='" . product::$disabled_message . "'" : "";
        $autosave = "onchange='product_save({product_id: " . $this->get_product_id() . ", $attr_name: this.value})'";
        $input = "<input type='number' step='0.01' name='$attr_name' min='0' value='" . $this->get_price() . "' $disabled $autosave />";
        $input .= $this->is_public() ? "<div class='error'>" . product::$disabled_message . "</div>" : "";
        return $input;
    }

    public function get_max_quantity_edit_input()
    {
        $attr_name = "product_max_quantity";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='" . product::$disabled_message . "'" : "";
        $autosave = "onchange='product_save({product_id: " . $this->get_product_id() . ", $attr_name: this.value})'";
        $input = "<input type='number' step='1' name='$attr_name' min='-1' value='" . $this->get_max_quantity() . "' $disabled $autosave /> (set to '-1' for unlimited)";
        $input .= $this->is_public() ? "<div class='error'>" . product::$disabled_message . "</div>" : "";
        return $input;
    }

    public function get_media_requirement_edit_input()
    {
        $attr_name = "product_media_required";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='" . product::$disabled_message . "'" : "";
        $autosave = "onchange='product_save({product_id: " . $this->get_product_id() . ", $attr_name: ($(this).is(\":checked\") ? \"1\":\"0\")})'";
        $input = "<input type='checkbox' name='$attr_name' " . ($this->requires_media_group() ? "checked" : "") . " $disabled $autosave />";
        $input .= $this->is_public() ? "<div class='error'>" . product::$disabled_message . "</div>" : "";
        return $input;
    }

    public function get_artist_only_edit_input()
    {
        $attr_name = "product_artist_only";
        $disabled = $this->is_public() ? "aria-disabled='true' disabled='true' title='" . product::$disabled_message . "'" : "";
        $autosave = "onchange='product_save({product_id: " . $this->get_product_id() . ", $attr_name: ($(this).is(\":checked\") ? \"1\":\"0\")})'";
        $input = "<input type='checkbox' name='$attr_name' " . ($this->is_artist_only() ? "checked" : "") . " $disabled $autosave />";
        $input .= $this->is_public() ? "<div class='error'>" . product::$disabled_message . "</div>" : "";
        return $input;
    }

    public function get_main_image_edit_input()
    {
        return $this->get_small_main_image();
    }

    public function get_is_public_edit_form()
    {
        $attr_name = "product_is_public";
        $message = "if(confirm(\"Publish product: " . $this->get_name() . " ?\")){";
        $save =  "onclick='" . ($this->is_public() ? "" : $message) . "product_save({product_id: " . $this->get_product_id() . ", $attr_name: this.value}, true);" . ($this->is_public() ? "" : "}") . "'";
        $input = "<button $save value='" . ($this->is_public() ? "0" : "1") . "'>" . ($this->is_public() ? "Unpublish" : "Publish") . "</button>";
        return $input;
    }

    public function get_deletion_form()
    {
        $attr_name = "delete_product";
        $form = ("<form method='post'>"
            . "<input type='button' onclick='if(confirm(\"Are you sure? Deleting a product removes all its data from the database. This action is irreversible.\")){this.form.submit();}' value='Delete' />"
            . "<input type='hidden' name='$attr_name' value='1' />"
            . "<input type='hidden' name='product_id' value='" . $this->get_product_id() . "' />"
            . "</form>");
        return $form;
    }


    /********************
     * Define the setters
     * ******************
     */

    public function set_name($name)
    {
        if (isset($name) && strlen($name) > 0) {
            $result = my_sql::update("product", array("name"), array($name), "productID=:0", array($this->get_product_id()), true);
            if (isset($result) && $result != false) $this->name = $name;
            return $result;
        } else return false;
    }

    public function set_description($desc)
    {
        $result = my_sql::update("product", array("description"), array($desc), "productID=:0", array($this->get_product_id()), true);
        if (isset($result) && $result != false) $this->description = $desc;
        return $result;
    }

    public function set_price($price)
    {
        $price = number_format($price, 2);
        $result = my_sql::update("product", array("price"), array($price), "productID=:0", array($this->get_product_id()));
        if (isset($result) && $result != false) $this->price = $price;
        return $result;
    }

    public function set_max_quantity($max)
    {
        $max = ($max == -1 ? null : $max);
        $result = my_sql::update("product", array("maxQuantity"), array($max), "productID=:0", array($this->get_product_id()));
        if (isset($result) && $result != false) $this->max_quantity = $max;
        return $result;
    }

    public function set_media_requirement($required)
    {
        $result = my_sql::update("product", array("requiresMediaGroup"), array(($required ? "1" : "0")), "productID=:0", array($this->get_product_id()));
        if (isset($result) && $result != false) $this->requires_media_group = $required;
        return $result;
    }

    public function set_main_image()
    {
        if (isset($_SESSION['media_group_id']) && isset($_SESSION['product_id'])) {
            $mgid = $_SESSION['media_group_id'];
            $pid = $_SESSION['product_id'];
            if ($pid != $this->get_product_id()) exit("<div class='error'>ERROR: Product ID's did not match when setting product image.</div>");
            //Query for current img's
            $current_mgid = $this->get_media_group_id();
            if (isset($current_mgid) && my_sql::exists("mediagroup", "mediaGroupID=:0", array($current_mgid))) {
                $table = "image, mediagroup";
                $where = "mediagroup.mediaGroupID=image.mediaGroupID && mediagroup.mediaGroupID=:0";
                $where_attr = array($current_mgid);
                $exists = my_sql::exists($table, $where, $where_attr);
                if ($exists) {
                    //Delete previous images
                    $current_img = my_sql::select("filename, imageID", $table, $where, $where_attr);
                    foreach ($current_img as $img) {
                        //delete file
                        unlink("../" . $img['filename']);
                        //remove from database
                        my_sql::delete("image", "imageID=:0", array($img['imageID']));
                    }
                }
                my_sql::delete("mediagroup", "mediaGroupID=:0", array($current_mgid));
                $current_dir = "../files/product/$pid/$current_mgid";
                if (file_exists($current_dir)) {
                    rmdir($current_dir);
                }
            }
            my_sql::update("product", array("mediaGroupID"), array($mgid), "productID=:0", array($pid));
            unset($_SESSION['media_group_id']);
            unset($_SESSION['product_id']);
            header("location: product_detail.php?product_id=$pid");
        }
    }

    public function set_public($is_public = 1)
    {
        $result = my_sql::update("product", array("isPublic"), array($is_public), "productID=:0", array($this->get_product_id()));
        if (isset($result) && $result != false) $this->is_public = $is_public;
        return $result;
    }

    public function set_artist_only($artist_only)
    {
        $exists = my_sql::exists("artistonlyproduct", "productID=:0", array($this->get_product_id()));
        if ($exists) {
            if ($artist_only) return true;
            else {
                return my_sql::delete("artistonlyproduct", "productID=:0", array($this->get_product_id()));
            }
        } else {
            if ($artist_only) {
                return my_sql::insert("artistonlyproduct", array("productID"), array($this->get_product_id()));
            } else {
                return true;
            }
        }
    }

    public function set_relevance($value)
    {
        $this->relevance = $value;
    }


    /******************************
     * Define additional functions
     * ****************************
     */

    public function insert_into_database()
    {
        $result = my_sql::insert_get_last_id(
            "product",
            array("mediaGroupID", "name", "description", "price", "maxQuantity", "requiresMediaGroup", "isPublic"),
            array($this->media_group_id, $this->name, $this->description, $this->price, $this->max_quantity, $this->requires_media_group, $this->is_public)
        );
        return $result;
    }

    public function check_attr_update()
    {
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case "product_name":
                    $this->set_name($val);
                    break;
                case "product_description":
                    $this->set_description($val);
                    break;
                case "product_price":
                    $this->set_price($val);
                    break;
                case "product_max_quantity":
                    $this->set_max_quantity($val);
                    break;
                case "product_main_image":
                    $this->set_main_image();
                    break;
                case "product_is_public":
                    $this->set_public($val);
                    break;
                case "product_media_required":
                    $this->set_media_requirement($val);
                    break;
                case "product_artist_only":
                    $this->set_artist_only($val);
                    break;
                case "delete_product":
                    $this->delete_from_database();
                    break;
            }
        }
    }

    public function delete_from_database()
    {
        $result = my_sql::delete("product", "productID=:0", array($this->get_product_id()));
        if ($result != false) {
            echo "<script type='text/javascript'>window.onload=function(){alert(\"Product successfully deleted.\"); window.location.assign(\"admin.php?tab=product\");};</script>";
        }
        return $result;
    }
}
