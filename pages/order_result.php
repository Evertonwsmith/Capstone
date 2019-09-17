<?php

use PHPMailer\PHPMailer\Exception;

/**
 * /////////////////////
 * // Order Confirmed //
 * /////////////////////
 * 
 * @desc this page is after the user has paid for their items and creates an order in the database and gives confirmation to the user.
 * @author Joshua Henderson joshuahenderson00@gmail.com
 */
include('header.php');
require_once '../src/order.php';
require_once '../src/cart.php';
require_once '../src/mail/mail_cred.php';
require_once '../src/mail/mail.php';

$order_completed = false;
$email_sent = false;
$order_check = false;
$payment_confirmed = false;
if (isset($user_email)) {
    /**
     * **********
     * ** TODO **
     * **********
     * 
     * To whomever it may concern this is where checks will need to be done for the results of the payment API
     * This page was tested using different buttons from the place order page that would send different information
     * (essentially the checks would see if which button was clicked from the previous page and prints based on that
     * information).
     */
    if (isset($_POST['place_order_confirmed'])) {
        if (isset($_SESSION['shipping_address_id']) && isset($_SESSION['billing_address_id'])) {
            $ship_id = $_SESSION['shipping_address_id'];
            $bill_id = $_SESSION['billing_address_id'];
            $payment_confirmed = true;

            $user = new user($user_email);
            $user_cart = new cart($user_email);

            $order_id = my_sql::insert_get_last_id("orders", array("userEmail", "shippingAddress", "billingAddress"), array("$user_email", "$ship_id", "$bill_id"));

            $order = new order($order_id);
            $order->set_order_date();
            $order_built = $order->add_cart_items($user_cart);

            if ($order_id != 0 && $order_built) {
                try {
                    unset($_SESSION['shipping_address_id']);
                    unset($_SESSION['billing_address_id']);
                    $body = $order->get_order_email($user);
                    $mailer = new mail($mail_sender_email, $mail_sender_password, "confirm_order");
                    $mailer->set_email_images($order->get_order_images());
                    $mailer->set_recipient($user_email);
                    // The subject of the email can be changed in the line below
                    $mailer->set_subject("Your Sleepovers order has been placed");
                    $mailer->set_body($body);
                    $mailer->set_port(587);
                    $email_sent = $mailer->send_mail();
                    $email_sent = true;
                    $order_check = true;
                } catch (Exception $e) {
                    echo ("<h3>An error has occured</h3>
                        <p>Your confirmation email was not sent</p>");
                    debug_print_backtrace();
                    $email_sent = false;
                }
            }
        } else {
            header("location: home.php");
        }
    } else if (isset($_POST['place_order_declined'])) {
        $payment_confirmed = false;
    } else {
        header("location: home.php");
    }
} else {
    header("location: home.php");
}
if ($payment_confirmed && $email_sent && $order_check) {
    $order_completed = true;
    echo
        "<div class='row' style='padding-top: 5%;'>
            <div class='checkout_wrap'>
                <ul class='checkout_bar'>
                    <li class='complete'>Cart</li>
                    <li class='complete'>Checkout</li>
                    <li class='complete'>Place Order</li>
                    <li class='complete'>Complete</li>
                </ul>
            </div>
        </div>";
}
?>
<br>
<div class="d-flex-fill p-2 justify-content-center">
    <div class="jumbotron jumbotron-fluid" align="center">
        <div class="container" style="color: #000;">
            <?php
            if ($order_completed) {
                echo ("<h1 class='display-4' style='font-size: 3rem;'>Order Confirmed</h1>
                    <p class='lead' style='font-size: 1.5rem;'>Thank you for your purchase!</p>
                    <p class='lead' style='font-size: 1.5rem;'>You have been sent an email containing your order information.</p>");
            } else {
                echo ("<h1 class='display-4' style='font-size: 3rem;'>Order Declined</h1>
                    <br><p class='lead' style='font-size: 1.5rem;'>An error has occured.</p>");
                if (!$payment_confirmed) {
                    echo ("<p class='lead' style='font-size: 1.5rem;'>Your payment has been declined.</p>");
                }
                if (!$email_sent) {
                    echo ("<p class='lead' style='font-size: 1.5rem;'>Please try again or contact Sleepovers to resolve this issue.</p>
                    ");
                }
                if (!$order_check) { }
            }
            ?>
        </div>
    </div>
</div>

<div class="order_info container">
    <?php
    if ($order_completed) {
        echo $order->get_order_confirmed_page($user);
    }
    ?>
    <div class="row justify-content-around" align="center" style="padding-top: 3%;">
        <div class="col-2"></div>
        <div class="col-3">
            <button class="return_button" onclick="window.location='home.php'">
                <p class="button_text">Home</p>
            </button>
        </div>
        <div class="col-3">
            <button class="return_button" onclick="window.location='store.php'">
                <p class="button_text">Store</p>
            </button>
        </div>
        <div class="col-2"></div>
    </div>
</div>

<?php include 'footer.php'; ?>