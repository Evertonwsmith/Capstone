<?php
$page_title = "Profile Page";
include "header.php";
require_once "../src/user.php";
if (!isset($_SESSION['user_email'])) {
    header("location:home.php");
} else {
    $user_email = $_SESSION['user_email'];
    $user = new user($user_email);
}
$user_image = $user->get_profile_image_id();
$profile_dir = my_sql::select("*", "image", "imageID = :0", array("$user_image"));
if (count($profile_dir) > 0) {
    $profile_dir = $profile_dir[0];
    $profile_dir = $profile_dir['filename'];
} else {
    $profile_dir = "";
}
$user_ship_address = address::get_shipping_address($user->get_user_email());
$user_bill_address = address::get_billing_address($user->get_user_email());
?>

<div>
    <div>

        <table>
            <thead>
                <tr>
                    <td>
                        <div><button id="personal_info_btn" onclick="openView('personal_info','personal_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                                <h3>Personal Info</h3>
                            </button></div>
                    <td>
                        <div><button id="update_info_btn" onclick="window.location.href = 'update_profile.php'" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                                <h3>Update Profile</h3>
                            </button></div>
                </tr>
            </thead>
        </table>
        <br>
        <hr><br>
        <div id="welcome" class="row">
            <h1>Welcome <?php echo $user->get_first_name(); ?>!</h1>
        </div><br>
        <div id="profile_image" class="row">
            <?php
            echo "<div id='user_pic' class='img-profile-container-large'>" . $user->get_profile_image() . "</div>";
            ?>
        </div>
        <br>
        <hr>
        <br>
        <div id="profile_info" class="row">
            <div id="personal_info" class="col-sm-4">
                <h3><?php echo $user->get_first_name() . " " . $user->get_last_name(); ?></h3>
                Email Address: <?php echo $user_email; ?>
            </div>
            <div id="ship_info" class="col-sm-4">
                <h3>Shipping Address:</h3>
                <?php
                if ($user_ship_address['street'] === "") {
                    echo "No information available";
                } else {
                    echo $user_ship_address['street'] . ", " . $user_ship_address['city'] . ", " . $user_ship_address['province'] . ", " . $user_ship_address['postalCode'];
                }
                ?>
            </div>
            <div id="bill_info" class="col-sm-4">
                <h3>Billing Address:</h3>
                <?php
                if ($user_bill_address['street'] === "") {
                    echo "No information available";
                } else {
                    echo $user_bill_address['street'] . ", " . $user_bill_address['city'] . ", " . $user_bill_address['province'] . ", " . $user_bill_address['postalCode'];
                }
                ?>
            </div>
        </div>
    </div>

</div>

</body>

<?php
include "footer.php";
?>
<script>
    window.onload = function() {
        document.getElementById("personal_info_btn").click();
    };

    function openView(selection, button) {
        var list = ['personal_info'];
        var list2 = ['personal_info_btn'];

        for (var i = 0; i < list.length; i++) {
            var div = document.getElementById(list[i]);
            var div2 = document.getElementById(list2[i]);
            if (div) {
                if (selection === list[i]) {
                    div.style.display = "block";
                    div2.style.backgroundColor = "#333";
                } else {
                    div.style.display = "none";
                    div2.style.backgroundColor = "";

                }
            }
        }
    }
</script>
<style>
    tr:nth-child(odd):hover {
        background-color: rgba(17, 17, 17, 0.5);
    }
</style>