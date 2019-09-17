<?php
require_once 'my_sql.php';
require_once 'address.php';

class user
{
    private $user_email, $profile_image_id, $first_name, $last_name, $shipping_address_id, $billing_address_id;
    private $detail_link = "user_detail.php";
    private $relevance = null;
    private static $ADMIN = 2;
    private static $STAFF = 1;
    private static $USER = 0;

    public function __construct($user_email)
    {
        if (my_sql::exists("useraccount", "userEmail=:0", array($user_email))) {
            $user_data = my_sql::select("*", "useraccount", "userEmail=:0", array("$user_email"));
            $user_data = $user_data[0];
            $this->user_email = $user_data['userEmail'];
            $this->profile_image_id = $user_data['profileImageID'];
            $this->first_name = $user_data['firstName'];
            $this->last_name = $user_data['lastName'];
            $this->shipping_address_id = $user_data['shippingAddressID'];
            $this->billing_address_id = $user_data['billingAddressID'];
        } else {
            //echo "User, $user_email, does not exist!";
            return null;
        }
    }


    /********************
     * Define the getters
     * ******************
     */

    public function get_user_email()
    {
        return $this->user_email;
    }

    public function get_profile_image_id()
    {
        return $this->profile_image_id;
    }


    public function get_profile_image()
    {
        $path = "pages/blank-profile-picture-973460_960_720.png";
        $result = my_sql::select("filename", "image", "imageID=:0", array($this->get_profile_image_id()));
        if ($result != false && count($result) == 1) {
            $path = $result[0]['filename'];
        }
        return "<img src='../$path' alt='No image available...'></img>";
    }

    public function get_small_profile_image()
    {
        return "<div class='img-profile-container'>" . $this->get_profile_image() . "</div>";
    }

    public function get_first_name()
    {
        return $this->first_name;
    }

    public function get_last_name()
    {
        return $this->last_name;
    }

    public function get_shipping_address_id()
    {
        return $this->shipping_address_id;
    }

    public function get_billing_address_id()
    {
        return $this->billing_address_id;
    }

    public function get_shipping_address()
    {
        return address::address_to_string($this->shipping_address_id);
    }

    public function get_billing_address()
    {
        return address::address_to_string($this->billing_address_id);
    }

    public function get_detail_link()
    {
        if (null !== $this->get_user_email()) {
            return ("<a href='javascript:' onclick='$.ajax(\"user_detail.php\", {type:\"POST\",data:{user_detail_email:\"" . $this->get_user_email() . "\"},success:function(data){window.location.assign(\"user_detail.php\");}});'>View User Profile</a>");
        } else return null;
    }

    public function get_relevance()
    {
        return $this->relevance;
    }

    public function is_admin()
    {
        $result = my_sql::select("userEmail", "adminaccount", "userEmail=:0", array($this->get_user_email()));
        if ($result != false && count($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function is_staff()
    {
        $result = my_sql::select("userEmail", "staffaccount", "userEmail=:0", array($this->get_user_email()));
        if ($result != false && count($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function is_artist()
    {
        $result = my_sql::select("userEmail", "artistaccount", "userEmail=:0", array($this->get_user_email()));
        if ($result != false && count($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function is_venue()
    {
        $result = my_sql::select("userEmail", "venueaccount", "userEmail=:0", array($this->get_user_email()));
        if ($result != false && count($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function update_field($field, $newatr)
    {
        $email = $this->get_user_email();
        return my_sql::update("useraccount", array($field), array($newatr), "userEmail = :0", array("$email"));
    }

    public static function login($user_email, $password)
    {
        $result = my_sql::select('*', 'password', "userEmail = :0", array("$user_email"));
        if (isset($result) && !empty($result)) {
            foreach ($result as $row) {
                //assuming hashed password is stored under column "hash"
                $valid = password_verify($password, $row['hash']);
                if ($valid == 1) {
                    //Set user email as session variable
                    $_SESSION['user_email'] = $user_email;
                    return true;
                }
            }
        }
        return false;
    }

    public function get_permission_level()
    {
        return $this->is_admin() ? user::$ADMIN : ($this->is_staff() ? user::$STAFF : user::$USER);
    }

    public static function get_all_ids($order_by_attr, $asc = "1", $limit = null, $table = null, $where = "isActive='1'", $where_attr = null)
    {
        $sort_attr = user::get_attr_name($order_by_attr);
        $id = user::get_attr_name("user_email");

        $sel = (isset($table) ? "useraccount." : "") . $id . (isset($sort_attr) ? "," . (isset($table) ? "useraccount." : "") . $sort_attr : "");
        $frm = isset($table) ? "useraccount RIGHT JOIN $table ON useraccount.userEmail=$table.userEmail" : "useraccount";
        $ord = user::get_attr_order_by($order_by_attr, $asc);

        // query the database using the my_sql api
        $result = my_sql::select($sel, $frm, $where, $where_attr, $ord, null, null, $limit);

        // create users for each row
        $users = array();
        foreach ($result as $row) {
            array_push($users, $row[$id]);
        }

        return $users;
    }

    public static function get_all($order_by_attr, $asc = "1", $limit = null, $table = null, $where = null, $where_attr = null)
    {
        $products = product::get_all_ids($order_by_attr, $asc, $limit, $table, $where, $where_attr);
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
                . "<th>" . $this->get_table_header($col) . "</th>"
                . "<td>" . $this->get_table_data($col, $editable) . "</td>"
                . "</tr>");
        }
        $table .= "</table>";
        return $table;
    }

    public static function get_table_header($column)
    {
        switch ($column) {
            case "user_email":
                return "User Email";
                break;
            case "profile_image_id":
                return "Profile Image ID";
                break;
            case "first_name":
                return "First Name";
                break;
            case "last_name":
                return "Last Name";
                break;
            case "shipping_address":
                return "Shipping Address";
                break;
            case "billing_address":
                return "Billing Address";
                break;
            case "profile_image":
                return "Profile Image";
                break;
            case "user_detail":
                return "User Details";
                break;
            case "shipping_address_id":
                return "Shipping Address ID";
                break;
            case "billing_address_id":
                return "Billing Address ID";
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
            case "user_email":
                return $this->get_user_email();
                break;
            case "profile_image_id":
                return $this->get_profile_image_id();
                break;
            case "first_name":
                return $this->get_first_name();
                break;
            case "last_name":
                return $this->get_last_name();
                break;
            case "shipping_address":
                return $this->get_shipping_address();
                break;
            case "billing_address":
                return $this->get_billing_address();
                break;
            case "profile_image":
                return $this->get_small_profile_image();
                break;
            case "user_detail":
                return $this->get_detail_link();
                break;
            case "shipping_address_id":
                return $this->get_shipping_address_id();
                break;
            case "billing_address_id":
                return $this->get_billing_address_id();
                break;
            case "search_relevance":
                return $this->get_relevance();
                break;
            default:
                return null;
        }
    }

    public static function get_attr_name($column)
    {
        switch ($column) {
            case "user_email":
                return "userEmail";
                break;
            case "profile_image_id":
                return "profileImageID";
                break;
            case "first_name":
                return "firstName";
                break;
            case "last_name":
                return "lastName";
                break;
            case "shipping_address_id":
                return "shippingAddressID";
                break;
            case "billing_address_id":
                return "billingAddressID";
                break;
            case "artist_name":
                return "artistName";
                break;
            case "venue_name":
                return "venueName";
                break;
            default:
                return null;
        }
    }

    public static function get_attr_order_by($column, $asc)
    {
        switch ($column) {
            case "profile_image_id":
                return "profileImageID" . ($asc ? " ASC" : " DESC");
                break;
            case "first_name":
                return "firstName" . ($asc ? " ASC" : " DESC");
                break;
            case "last_name":
                return "lastName" . ($asc ? " ASC" : " DESC");
                break;
            case "shipping_address":
            case "shipping_address_id":
                return "shippingAddressID" . ($asc ? " ASC" : " DESC");
                break;
            case "billing_address":
            case "billing_address_id":
                return "billingAddressID" . ($asc ? " ASC" : " DESC");
                break;
            case "search_relevance":
                return null;
            case "user_detail":
            case "profile_image":
            case "user_email":
            default:
                return "userEmail" . ($asc ? " ASC" : " DESC");
                break;
        }
    }

    public function get_set_privelege_form($permission_cap)
    {
        $current_perm = $this->get_permission_level();
        $form = "<select id='set_privelege_form' " . ($permission_cap < user::$ADMIN ? "disabled='true' aria-disabled" : "") . " >"
            . "<option value='" . user::$USER . "' " . ($current_perm == user::$USER ? "selected='true' " : "") . ">User (Default)</option>"
            . "<option value='" . user::$STAFF . "' " . ($current_perm == user::$STAFF ? "selected='true' " : "") . ">Staff</option>"
            . "<option value='" . user::$ADMIN . "' " . ($current_perm == user::$ADMIN ? "selected='true' " : "") . ">Administrator</option>"
            . "</select>";
        if ($permission_cap >= user::$ADMIN) {
            $form .= "<script>
                $('#set_privelege_form').on('change',
                    function(event) {
                        let level = $('#set_privelege_form').val();
                        let permission = level == " . user::$ADMIN . " ? 'ADMIN' : (level == " . user::$STAFF . " ? 'STAFF' : 'USER');
                        if(confirm('Setting permission level for " . $this->get_first_name() . " " . $this->get_last_name() . " to ' + permission + '. Are you sure?')){
                            $.ajax('user_set_attr.php', {
                                type: 'POST',  // http method
                                data: {
                                    user_email : '" . $this->get_user_email() . "',
                                    user_privelege : $('#set_privelege_form').val()
                                },  // data to submit
                                success: function (data, status, xhr) {
                                    //console.log('status: ' + status + ', data: ' + data);
                                },
                                error: function (jqXhr, textStatus, errorMessage) {
                                    //console.log('Error' + errorMessage);
                                }
                            });
                        }else{
                            location.reload();
                        }
                    }
                );
            </script>";
        }
        return $form;
    }

    public function get_delete_account_button()
    {
        $button = "<button style='background-color: #FF0080;' onclick='if(confirm(\"Are you sure you want to delete this account?\")){ $.ajax(\"delete_user.php\", {type:\"POST\",data:{user_detail_email:\"" . $this->get_user_email() . "\"},success:function(data){console.log(data);alert(\"Account, " . $this->get_user_email() . ", has been deleted.\");window.location.assign(\"admin.php?tab=useraccount\");}});}'>Delete Account</button>";
        return $button;
    }

    /****************
     * DEFINE SETTERS
     * **************
     */

    public function set_relevance($value)
    {
        $this->relevance = $value;
    }

    public function set_staff($state)
    {
        $table = "staffaccount";
        $where = "userEmail=:0";
        $where_attr = array($this->get_user_email());
        $current_state = my_sql::exists($table, $where, $where_attr);
        if ($state) {
            if ($current_state) return true;
            else return my_sql::insert($table, array("userEmail"), array($this->get_user_email()));
        } else {
            if (!$current_state) return true;
            else return my_sql::delete($table, $where, $where_attr);
        }
    }

    public function set_admin($state)
    {
        $table = "adminaccount";
        $where = "userEmail=:0";
        $where_attr = array($this->get_user_email());
        $current_state = my_sql::exists($table, $where, $where_attr);
        if ($state) {
            if ($current_state) return true;
            else return my_sql::insert($table, array("userEmail"), array($this->get_user_email()));
        } else {
            if (!$current_state) return true;
            else return my_sql::delete($table, $where, $where_attr);
        }
    }

    public function set_permission_level($level, $permission_cap)
    {
        //Only let admin set permission levels
        if ($permission_cap >= user::$ADMIN) {
            // apply the permission cap to the level
            $level = $level > $permission_cap ? $permission_cap : $level;
            // set user permission
            switch ($level) {
                case user::$USER:
                    $this->set_admin(false);
                    $this->set_staff(false);
                    break;
                case user::$STAFF:
                    $this->set_admin(false);
                    $this->set_staff(true);
                    break;
                case user::$ADMIN:
                    $this->set_admin(true);
                    $this->set_staff(false);
                    break;
            }
        }
    }

    public function delete()
    {
        if (my_sql::exists("useraccount", "userEmail=:0", array($this->get_user_email()))) {
            $result = my_sql::update("useraccount", array("isActive"), array("0"), "userEmail=:0", array($this->get_user_email()));
            return $result;
        } else {
            return 0;
        }
    }

    public function ban()
    {
        if (my_sql::exists("useraccount", "userEmail=:0", array($this->get_user_email()))) {
            $result = my_sql::update("useraccount", array("banned"), array("1"), "userEmail=:0", array($this->get_user_email()));
            $result = $result && my_sql::delete("password", "userEmail=:0", array($this->get_user_email()));
            return $result;
        } else {
            return 0;
        }
    }

    /**********************
     * ADDITIONAL FUNCTIONS
     * ********************
     */

    public function check_attr_update($permission_cap = 0)
    {
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case "user_privelege":
                    $this->set_permission_level($val, $permission_cap);
                    break;
            }
        }
    }
}
