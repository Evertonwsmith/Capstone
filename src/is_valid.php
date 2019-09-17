<?php
class is_valid
{
    public static function email($email)
    {
        $errors = "";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors .= "Please enter a valid email address.<br>";
        } else if (isset(my_sql::select("userEmail", "useraccount", "userEmail = :0", array("$email"))[0])) {
            if (my_sql::exists("useraccount", "userEmail=:0 AND isActive='0'", array($email))) {
                $errors .= "Email address is associated with a de-activated account. If you would like to re-active your account, please <a style='color:dodgerblue' href='forgot_password.php'>recover your password<a/>.";
            } else {
                $errors .= "Email address is already associated with an account.<br>";
            }
        }
        if (strlen($errors) == 0) return true;
        else return $errors;
    }

    public static function password($password)
    {
        $errors = "";
        $min_length = 8; // minimum number of characters needed
        if (strlen($password) < $min_length) $errors .= "Password must be at least $min_length characters in length.<br>";
        $min_num = 1; // minimum number of numbers needed
        $min_sym = 1; // minimum number of symbols needed
        $num_req = 0; // number count
        $sym_req = 0; // symbol count
        for ($i = 0; $i < strlen($password); $i++) {
            $char = substr($password, $i, 1);
            if (ctype_digit($char)) $num_req += 1;
            else if (ctype_alpha($char)); //ignore
            else $sym_req += 1;
        }
        if ($num_req < $min_num) $errors .= "Password must contain at least one numerical character.<br>";
        if ($sym_req < $min_sym) $errors .= "Password must contain at least one non-alphanumeric character.<br>";
        if (strlen($errors) == 0) return true;
        else return $errors;
    }
}
