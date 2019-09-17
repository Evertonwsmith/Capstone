<?php
$page_title = "Forgot password";
include "header.php";
require_once "../src/my_sql.php";
require_once "../src/user.php";
require_once "../src/is_valid.php";
require_once "../src/mail/mail.php";
require_once "../src/mail/mail_cred.php";

//Check emails match and exist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (isset($_POST['email_address']) && isset($_POST['confirm_email_address'])) {
        if (empty($_POST["email_address"]) || empty($_POST["confirm_email_address"])) {
            echo "<br>Please fill out both boxes";
        }
        if ($_POST['email_address'] !== $_POST['confirm_email_address']) {
            echo "<br>Please enter matching email addresses";
        }
        if ($_POST['email_address'] === $_POST['confirm_email_address']) {
            $email_address = $_POST['email_address'];
            $result = my_sql::select("userEmail", "useraccount", "userEmail = :0", array($email_address));
            if ($result) {
                $user = new user($email_address);
                $CH1 = '1234567890';
                $newPass = '';
                for ($i = 0; $i < 8; $i++) {
                    $newPass .= $CH1[rand(0, 9)];
                }
                $new_password = password_hash($newPass, PASSWORD_ARGON2I);
                $timestamp = date('Y-m-d G:i:s');
                $exists = my_sql::select("*", "passwordresetkey", "userEmail = :0", array($email_address));
                if (!$exists) {
                    $insert_key = my_sql::insert("passwordresetkey", array("userEmail", "hash", "timestamp"), array($email_address, $new_password, $timestamp));
                } else {
                    $update_key = my_sql::update("passwordresetkey", array("hash", "timestamp"), array($new_password, $timestamp), "userEmail = :0", array($email_address));
                    echo "<br>update<br>";
                }
                echo "The key to reset your password has been emailed to you!<br>Retrieve it and return to this page to create a new password.";
                $mail = new mail($mail_sender_email, $mail_sender_password, "reset_password");
                $mail->set_recipient($email_address);
                $mail->set_subject("Sleepovers - Password Reset");
                $mail->set_port(587);
                $mail->set_body("Hello,<br>" . $user->get_first_name() . " " . $user->get_last_name() . "<br><br>A request to reset your password has recently been made. If you did not request a password reset key, please contact Sleepovers staff.<br><br> Your password key is: " . $newPass . "<br><br>Within 5 minutes, return to the forgot password page with your key to set a new password.<br><br>Thank you!<br>Sleepovers for Life");
                $mail->send_mail();
            } else {
                echo "User Email does not exist";
            }
        }
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    if (isset($_POST['email']) && isset($_POST['reset_key']) && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
        if (empty($_POST["email"]) || empty($_POST["reset_key"]) || empty($_POST["new_password"]) || empty($_POST["confirm_new_password"])) {
            echo "<br>Please fill in all four fields to change password";
        } elseif ($_POST['new_password'] !== $_POST['confirm_new_password']) {
            echo "<br>Please enter the matching passwords";
        }
        $user_email = $_POST['email'];
        $password_valid = is_valid::password($_POST['new_password']);
        $check_key = my_sql::select("*", "passwordresetkey", "userEmail = :0", array($user_email));
        if ($password_valid && $check_key) {
            $check_key = $check_key[0];
            $check_key_hash = $check_key['hash'];
            $time_check = $check_key['timestamp'];
            $time_check = strtotime($time_check);
            $entered_key = $_POST['reset_key'];
            //$entered_key = password_hash($_POST['reset_key'], PASSWORD_ARGON2I);
            if ((strtotime(date('Y-m-d G:i:s')) - $time_check) > 300 || (strtotime(date('Y-m-d G:i:s')) - $time_check) < -300) {
                echo "<br>5 minutes since key generation. Please request a new key";
            } elseif (password_verify($entered_key, $check_key_hash)) {
                if (my_sql::exists("useraccount", "userEmail=:0 AND banned=0", array($user_email))) {
                    echo "<br>Password has been updated to new password";
                    $new_password = password_hash($_POST['new_password'], PASSWORD_ARGON2I);
                    $update_password = my_sql::update("password", array("hash"), array($new_password), "userEmail = :0", array($user_email));
                    $reactive_account = my_sql::update("useraccount", array("isActive"), array("1"), "userEmail = :0", array($user_email));
                } else {
                    //user is banned, do not allow them to re-activate
                    echo "<br><br><br><div class='error'><h4>This account has been deleted by a Sleeopovers Staff or Admin and cannot be re-activated. If you believe this is a mistake, please contact Sleepovers Staff.</h4></div>";
                    include 'footer.php';
                    exit();
                }
            } else {
                echo "<br><br>";
                echo "Key does not match.";
            }
        } else {
            echo "ERROR 1";
        }
    }
}

?>
<br><br><br>
<hr>
<br>
<h1>Password Recovery</h1>
<br>
<hr>
<br>
<br><br><br>
<div id="forgot_password">
    <form id="confirm_useremail" method="POST">
        <label class='label-left-long'>Enter Email Address:</label>
        <input type="text" name="email_address"><br>
        <label class='label-left-long'>Re-enter Email Address:</label>
        <input type="text" name="confirm_email_address"><br><br>
        <input type="submit" name="submit" value="Send Recovery Email">
    </form>
</div>
<div id="change_password">
    <br><br><br>
    <hr>
    <br>
    <h1>Password Reset</h1>
    <br>
    <hr>
    <br>
    <br><br><br>
    <form id="change_password" method="POST">
        <label class='label-left-long'>Enter Email Address:</label>
        <input type="text" name="email"><br>
        <label class='label-left-long'>Enter Key Provided:</label>
        <input type="password" name="reset_key"><br>
        <br><br>
        <label class='label-left-long'>Enter New Password:</label>
        <input type="password" name="new_password"><br>

        <label class='label-left-long'>Re-enter New Password:</label>
        <input type="password" name="confirm_new_password"><br><br>
        <input type="submit" name="reset" value="Reset Password">
    </form>
</div>
<?php
include("footer.php");
?>