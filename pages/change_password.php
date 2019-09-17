<?php
$page_title = 'Change Password';
include 'header.php';
include '../src/is_valid.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_email'])) {
        header("location:home.php");
    } else {
        $user_email = $_SESSION['user_email'];
        if ((empty($_POST['old_password'])) or (empty($_POST['new_password'])) or (empty($_POST['confirm_new_password']))) {
            echo "<br><div class='error'>Please fill in all the inputs</div><br>";
        } else {

            $old_password = $_POST['old_password'];
            $new_password = $_POST['new_password'];
            $confirm_new_password = $_POST['confirm_new_password'];

            $result = my_sql::select('*', 'password', "userEmail = :0", array("$user_email"), null, null, null);
            if (is_array($result) || is_object($result)) {
                foreach ($result as $value) {
                    //assuming hashed password is stored under column "hash"
                    $valid = password_verify($old_password, $value['hash']);
                    //After verifying old password, check new password validation
                    if ($valid) {
                        $account_invalid = false;
                        $pass_errors = is_valid::password($new_password);
                        if (!$pass_errors) {
                            $account_invalid = true;
                            echo "<br><div class='error'>Invalid password</div><br>";
                        }
                        if (!($new_password === $confirm_new_password)) {
                            $account_invalid = true;
                            echo "<br><div class='error'>Passwords do not match.</div><br>";
                        }
                        if (!$account_invalid) {
                            //update password where....
                            //update(password,hash,argon2(new_password),useremail=$_SESSION['user_email'])
                            $new_password = password_hash($new_password, PASSWORD_ARGON2I);
                            $update_password = my_sql::update("password", array("hash"), array($new_password), "userEmail = :0", array($user_email));
                            echo "<br>Password updated!";
                        } else {
                            echo "<br>";
                        }
                    } else {
                        echo "<br><div class='error'>Old password incorrect</div><br>";
                    }
                }
            }
        }
    }
}
?>
<br><br><br>
<h2>Change Password</h2>
<br>
<hr>
<br><br><br>
<form action="" method="post">
    <label class='label-left-long'>Old Password </label><input type="password" name="old_password" class="box" /><br /><br />
    <label class='label-left-long'>New Password </label><input type="password" name="new_password" class="box" /><br /><br />
    <label class='label-left-long'>Confirm New Password </label><input type="password" name="confirm_new_password" class="box" /><br /><br />
    <input type="submit" value=" Submit " />
</form>
<br>
<button onclick="window.location.href = 'update_profile.php'">Back</button><br>

<?php include 'footer.php'; ?>