<?php
require_once '../src/user.php';
require_once '../src/order.php';
require_once '../src/page_manager.php';

//Verify user permissions
include_once 'admin_gateway.php';

$page_title = "User Detail";
include 'header.php';

//Check if Pre-Request for setting SESSION variable was sent
if (isset($_POST['user_detail_email'])) {
    if (session_status() == PHP_SESSION_NONE) {
        //Start session
        session_start();
    }
    // set session variable
    $_SESSION['user_detail_email'] = $_POST['user_detail_email'];
    exit();
}

if (isset($_SESSION['user_detail_email'])) {
    // set user
    $user = new user($_SESSION['user_detail_email']);
} else {
    header("location: admin.php?tab=useraccount");
    exit();
}

// navigation bar
include 'admin_nav.php';

/******************
 * User Information
 * ****************
 */
?>

<h2>User Information</h2>

<?php echo $user->get_vertical_table(array("profile_image", "profile_image_id", "user_email", "last_name", "first_name", "shipping_address", "shipping_address_id", "billing_address", "billing_address_id"), true); ?>

<br><br>
<hr>

<br>
<h2>Set User Permission Level</h2>
<br>
<div class='row'>
    <?php echo $user->get_set_privelege_form($permission_level); ?>
    <div class='split-margin'></div>
    <?php echo $user->get_delete_account_button(); ?>
</div>
<br><br>
<hr>
<br>

<?php
include 'footer.php';
?>