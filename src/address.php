<?php
require_once 'my_sql.php';

class address
{
    private $address_id, $street, $line_two, $city, $province, $postal_code;

    public function __construct($address_id)
    {
        if (isset($address_id)) {
            $address_data = my_sql::select("*", "address", "AddressID = :0", array("$address_id"));
            if ($address_data != false && count($address_data) == 1) {
                $address_data = $address_data[0];
                $this->address_id = $address_data["AddressID"];
                $this->street = $address_data["street"];
                $this->line_two = $address_data["lineTwo"];
                $this->city = $address_data["city"];
                $this->province = $address_data["province"];
                $this->postal_code = $address_data["postalCode"];
            }
        }
    }
    /**
     * Define Getters
     */
    public function get_address_id()
    {
        return $this->address_id;
    }
    public function get_street()
    {
        return $this->street;
    }
    public function get_line_two()
    {
        return $this->line_two;
    }
    public function get_city()
    {
        return $this->city;
    }
    public function get_province()
    {
        if (isset($this->province)) {
            return $this->province;
        } else {
            return null;
        }
    }
    public function get_postal_code()
    {
        return $this->postal_code;
    }
    /**
     * Define Setters
     */
    public function set_street($street)
    {
        $this->street = $street;
        return my_sql::update("address", array("street"), array($street), "AddressID=:0", array($this->get_address_id()));
    }
    public function set_line_two($line_two)
    {
        $this->street = $line_two;
        return my_sql::update("address", array("street"), array($line_two), "AddressID=:0", array($this->get_address_id()));
    }
    public function set_city($city)
    {
        $this->street = $city;
        return my_sql::update("address", array("street"), array($city), "AddressID=:0", array($this->get_address_id()));
    }
    public function set_province($province)
    {
        $this->street = $province;
        return my_sql::update("address", array("street"), array($province), "AddressID=:0", array($this->get_address_id()));
    }
    public function set_postal_code($postal_code)
    {
        $this->street = $postal_code;
        return my_sql::update("address", array("street"), array($postal_code), "AddressID=:0", array($this->get_address_id()));
    }

    /**
     * Static functions
     */

    public static function address_to_string($address_id)
    {
        $data = my_sql::select("*", "address", "AddressID=:0", array($address_id));
        if (count($data) != 1) return false; // Return false if address does not exist
        else $data = $data[0];

        $address = $data["street"] . ", " . (isset($data["lineTwo"]) && $data["lineTwo"] != "" ? ($data["lineTwo"] . ", ") : "") . $data["city"] . " " . $data["province"] . " " . $data["postalCode"];

        return $address;
    }

    public static function add_address($address_info, $email = null, $column_name = null)
    {
        $insert_table = "address";
        $insert_attr = array("street", "lineTwo", "city", "province", "postalCode");
        // if ($address_info["lineTwo"] == null) {
        //     $address_info["lineTwo"] = "APT#";
        // }
        if (!address::is_valid_province($address_info["province"])) {
            return 0;
        }
        $insert_values = [];
        foreach ($address_info as $value) {
            array_push($insert_values, $value);
        }
        $insert_result = my_sql::insert_get_last_id($insert_table, $insert_attr, $insert_values);
        // Update users information after inserting or return new address id
        if ($column_name == "shippingAddressID") {
            return address::set_user_shipping_address($email, $insert_result);
        } else if ($column_name == "billingAddressID") {
            return address::set_user_billing_address($email, $insert_result);
        } else if ($column_name == null) {
            return $insert_result;
        }
    }

    public static function is_valid_province($province)
    {
        switch ($province) {
            case "BC":
                return true;
            case "AB":
                return true;
            case "MB":
                return true;
            case "NB":
                return true;
            case "NL":
                return true;
            case "NS";
                return true;
            case "NU":
                return true;
            case "ON":
                return true;
            case "PE":
                return true;
            case "QC":
                return true;
            case "SK":
                return true;
            case "YT":
                return true;
            default:
                return false;
        }
    }

    public static function get_shipping_address($user_email)
    {
        $select_attr = "*";
        $select_table = "address";
        $select_where = "AddressID = (SELECT shippingAddressID FROM useraccount WHERE userEmail = :0)";
        $select_where_attr = array("$user_email");
        $ship_address = my_sql::select($select_attr, $select_table, $select_where, $select_where_attr);
        if (isset($ship_address) && $ship_address != null) {
            return $ship_address[0];
        } else {
            return null;
        }
    }

    public static function get_shipping_address_by_id($address_id)
    {
        $select_attr = "street, lineTwo, city, province, postalCode";
        $select_table = "address";
        $select_where = "AddressID = :0";
        $select_where_attr = array("$address_id");
        $ship_address = my_sql::select($select_attr, $select_table, $select_where, $select_where_attr);
        if (isset($ship_address) && $ship_address != null) {
            return $ship_address[0];
        } else {
            return null;
        }
    }

    public static function get_shipping_address_id($user_email)
    {
        $select_attr = "shippingAddressID";
        $select_table = "useraccount";
        $select_where = "userEmail = :0";
        $select_where_attr = array("$user_email");
        $shipping_address_id = my_sql::select($select_attr, $select_table, $select_where, $select_where_attr);
        if (isset($shipping_address_id)) {
            return $shipping_address_id[0]["shippingAddressID"];
        } else {
            return 0;
        }
    }

    public static function get_billing_address($user_email)
    {
        $select_attr = "*";
        $select_table = "address";
        $select_where = "AddressID = (SELECT billingAddressID FROM useraccount WHERE userEmail = :0)";
        $select_where_attr = array("$user_email");
        $bill_address = my_sql::select($select_attr, $select_table, $select_where, $select_where_attr);
        if (isset($bill_address) && $bill_address != null) {
            return $bill_address[0];
        } else {
            return null;
        }
    }

    public static function get_billing_address_id($user_email)
    {
        $select_attr = "billingAddressID";
        $select_table = "useraccount";
        $select_where = "userEmail = :0";
        $select_where_attr = array("$user_email");
        $billing_address_id = my_sql::select($select_attr, $select_table, $select_where, $select_where_attr);
        if (isset($billing_address_id)) {
            return $billing_address_id[0]["billingAddressID"];
        } else {
            return 0;
        }
    }

    public static function get_address_form($address_type = null)
    {
        $form = "<form method='post' name='shipping'>";
        if ($address_type == "shipping") {
            $form = $form . (
                // Add onsubmit='return validate_form(" . $address_type . ")'
                address::get_shipping_inputs());
        } else if ($address_type == "billing") {
            $form = $form . (
                // Add onsubmit='return validate_form(" . $address_type . ")'
                address::get_billing_inputs());
        }
        return $form . "<input type='submit' name='submit_address_info'><br></form>";
    }

    public static function get_shipping_inputs($address_id = null)
    {

        $inputs = ("<input type='hidden' name='shipping_info'/>
            <p class='address_type' align='left'>Shipping Address</p><hr><br>"
            . address::get_address_inputs($address_id, "ship_address") . "<br>");
        return $inputs;
    }

    public static function get_billing_inputs($address_id = null)
    {
        $inputs = ("<input type='hidden' name='billing_info'/>
            <p class='address_type' align='left'>Billing Address</p><hr><br>"
            . address::get_address_inputs($address_id, "bill_address")
            . (!isset($address_id) ? "<input type='checkbox' name='bill_ship_same' onclick='disableInputs(\"bill_address\")' id='bill_ship_same'> Billing and shipping info are the same <br>" : "")
            . "<input type='hidden' name='hidden_container' id='hidden_container' value='bill_ship_diff'>");
        return $inputs;
    }

    public static function get_address_inputs($address_id, $attribute_name)
    {
        $address = new address($address_id);
        $inputs = ("<div class='d-flex flex-row' style='border-bottom: 1px solid white;'>
                <label class='align-self-center address_label'>Street: </label>
                <input type='text' name='" . $attribute_name . "[street]' id='" . $attribute_name . "_street' class='$attribute_name ml-auto p-2' value='" . $address->get_street() . "' style='width: 50%;' placeholder='Street'/>
            </div>
            <br>
            <div class='d-flex flex-row' style='border-bottom: 1px solid white;'>
                <label class='align-self-center address_label'>Apt number: </label>
                <input type='text' name='" . $attribute_name . "[lineTwo]' id='" . $attribute_name . "_lineTwo' class='$attribute_name ml-auto p-2' value='" . $address->get_line_two() . "' style='width: 30%;' placeholder='Apt Number'/>
            </div>
            <br>
            <div class='d-flex flex-row' style='border-bottom: 1px solid white;'>
                <label class='align-self-center address_label'>City: </label>
                <input type='text' name='" . $attribute_name . "[city]' id='" . $attribute_name . "_city' class='$attribute_name ml-auto p-2' value='" . $address->get_city() . "' style='width: 30%;' placeholder='City'/><br>
            </div>
            <br>
            <div class='d-flex flex-row' style='border-bottom: 1px solid white;'>
                <label class='align-self-center address_label'>Province: </label>
                <select name='" . $attribute_name . "[province]' size='1' id='" . $attribute_name . "_province' class='$attribute_name ml-auto p-2' style='width: 15%;'>
                    <option " . ($address->get_province() == 'BC' ? "selected = 'selected'" : "") . " value='BC'>BC</option>
                    <option " . ($address->get_province() == 'AB' ? "selected = 'selected'" : "") . " value='AB'>AB</option>
                    <option " . ($address->get_province() == 'MB' ? "selected = 'selected'" : "") . " value='MB'>MB</option>
                    <option " . ($address->get_province() == 'NB' ? "selected = 'selected'" : "") . " value='NB'>NB</option>
                    <option " . ($address->get_province() == 'NL' ? "selected = 'selected'" : "") . " value='NL'>NL</option>
                    <option " . ($address->get_province() == 'NT' ? "selected = 'selected'" : "") . " value='NT'>NT</option>
                    <option " . ($address->get_province() == 'NS' ? "selected = 'selected'" : "") . " value='NS'>NS</option>
                    <option " . ($address->get_province() == 'NU' ? "selected = 'selected'" : "") . " value='NU'>NU</option>
                    <option " . ($address->get_province() == 'ON' ? "selected = 'selected'" : "") . " value='ON'>ON</option>
                    <option " . ($address->get_province() == 'PE' ? "selected = 'selected'" : "") . " value='PE'>PE</option>
                    <option " . ($address->get_province() == 'QC' ? "selected = 'selected'" : "") . " value='QC'>QC</option>
                    <option " . ($address->get_province() == 'SK' ? "selected = 'selected'" : "") . " value='SK'>SK</option>
                    <option " . ($address->get_province() == 'YT' ? "selected = 'selected'" : "") . " value='YT'>YT</option>
                </select>
            </div>
            <br>
            <div class='d-flex flex-row' style='border-bottom: 1px solid white;'>
                <label class='align-self-center address_label'>Postal Code: </label>
                <input type='text' name='" . $attribute_name . "[postalCode]' id='" . $attribute_name . "_postalCode' class='$attribute_name ml-auto p-2' value='" . $address->get_postal_code() . "' style='width: 20%;' placeholder='A1A1A1'/>
            </div><br>");
        return $inputs;
    }

    public static function set_user_shipping_address($user_email, $address_id)
    {
        $update_table = "useraccount";
        $update_attr = array("shippingAddressID");
        $update_value = array($address_id);
        $update_where = "userEmail = :0";
        $update_where_attr = array("$user_email");
        $update_result = my_sql::update($update_table, $update_attr, $update_value, $update_where, $update_where_attr);
        if (isset($update_result)) return $update_result;
        else return 0;
    }

    public static function set_user_billing_address($user_email, $address_id)
    {
        $update_table = "useraccount";
        $update_attr = array("billingAddressID");
        $update_value = array($address_id);
        $update_where = "userEmail = :0";
        $update_where_attr = array("$user_email");
        $update_result = my_sql::update($update_table, $update_attr, $update_value, $update_where, $update_where_attr);
        if (isset($update_result)) return $update_result;
        else return 0;
    }

    public static function is_valid_address_input($address_array)
    {
        $array_check = false;
        foreach ($address_array as $key => $line) {
            if ($key == "lineTwo") {
                continue;
            }
            if ($line != null) {
                $array_check = true;
            } else {
                return false;
            }
        }
        if (!empty($address_array) && $array_check) {
            return true;
        } else {
            return false;
        }
    }

    public static function compare_addresses($address_id, $address_info)
    {
        if ($address_id == 0) {
            return false;
        }
        if (empty($address_info)) {
            return false;
        }
        $address_one = new address($address_id);
        $street = $line_two = $city = $province = $postal_code = false;
        if ($address_one->get_street() == $address_info["street"]) {
            $street = true;
        }
        if ($address_one->get_line_two() == $address_info["lineTwo"]) {
            $line_two = true;
        }
        if ($address_one->get_city() == $address_info["city"]) {
            $city = true;
        }
        if ($address_one->get_province() == $address_info["province"]) {
            $province = true;
        }
        if ($address_one->get_postal_code() == $address_info["postalCode"]) {
            $postal_code = true;
        }
        if ($street && $line_two && $city && $province && $postal_code) {
            return true;
        } else {
            return false;
        }
    }

    public static function check_attr_update_address()
    {
        foreach ($_POST as $attr => $value) {
            switch ($attr) {
                case "ship_address":
                    if (isset($_SESSION['shipping_address_id'])) {
                        if (address::is_valid_address_input($_POST['ship_address'])) {
                            if (!address::compare_addresses($_SESSION['shipping_address_id'], $_POST['ship_address'])) {
                                if ($_SESSION['shipping_address_id'] == 0) {
                                    $_SESSION['shipping_address_id'] = address::add_address($_POST['ship_address']);
                                } else {
                                    $check = address::update_address($_SESSION['shipping_address_id'], $_POST['ship_address']);
                                    if (!$check) {
                                        //$_SESSION['shipping_address_id'] = 0;
                                    }
                                }
                            }
                        }
                    } else {
                        if (address::is_valid_address_input($_POST['ship_address'])) {
                            $_SESSION['shipping_address_id'] = address::add_address($_POST['ship_address']);
                        } else {
                            $_SESSION['shipping_address_id'] = 0;
                        }
                    }
                    break;
                case "bill_address":
                    if (isset($_SESSION['billing_address_id'])) {
                        if (address::is_valid_address_input($_POST['bill_address'])) {
                            if (!address::compare_addresses($_SESSION['billing_address_id'], $_POST['bill_address'])) {
                                if ($_SESSION['billing_address_id'] == 0) {
                                    $_SESSION['billing_address_id'] = address::add_address($_POST['bill_address']);
                                } else {
                                    $check = address::update_address($_SESSION['billing_address_id'], $_POST['bill_address']);
                                    if (!$check) {
                                        $_SESSION['billing_address_id'] = 0;
                                    }
                                }
                            }
                        }
                    } else {
                        if (address::is_valid_address_input($_POST['bill_address'])) {
                            $_SESSION['billing_address_id'] = address::add_address($_POST['bill_address']);
                        } else {
                            $_SESSION['billing_address_id'] = 0;
                        }
                    }
                    break;
                case "bill_ship_same":
                    if (isset($_SESSION['shipping_address_id'])) {
                        $ship_temp = address::get_shipping_address_by_id($_SESSION['shipping_address_id']);
                        $_SESSION['billing_address_id'] = address::add_address($ship_temp);
                    } else if (isset($_POST['ship_address'])) {
                        $_SESSION['billing_address_id'] = address::add_address($_POST['ship_address']);
                    } else {
                        $_SESSION['billing_address_id'] = 0;
                    }
                    break;
            }
        }
    }

    public static function update_address($address_id, $address_array)
    {
        $result = my_sql::update("address", array_keys($address_array), array_values($address_array), "AddressID = :0", array("$address_id"));
        if ($result) {
            return $result;
        } else {
            return 0;
        }
    }
    public static function delete_address($address_id)
    {
        $result = my_sql::delete("address", "AddressID = :0", array("$address_id"));
        if ($result) {
            return $result;
        } else {
            return 0;
        }
    }
}
