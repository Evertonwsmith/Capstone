<?php
session_start();

if (isset($_SESSION['user_email'])) {
    foreach ($_POST as $key => $value) {
        switch ($key) {
            case "quantity":
                $_SESSION['quantity'] = $value;
                break;
            case "product_id":
                $_SESSION['product_id'] = $value;
                break;
        }
    }
    exit("success");
} else {
    exit("User not logged in.");
}
