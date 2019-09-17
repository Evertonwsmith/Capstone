<?php
$page_title = 'Update Profile';
include 'header.php';
require_once "../src/eventpost.php";
require_once "../src/song.php";
require_once "../src/my_sql.php";

$isArtist = false;
$isVenue = false;
if (!isset($_SESSION['user_email'])) {
    header("location:home.php");
} else {
    $user_email = $_SESSION['user_email'];
    $user = new user($user_email);
    $isArtist = $user->is_artist();
    $isVenue = $user->is_venue();
    if ($isArtist) {
        $artist_name = my_sql::select("*", "artistaccount", "userEmail = :0", array($user_email));
        if ($artist_name) {
            $artist_name = $artist_name[0];
            $description = $artist_name['description'];
            $artist_name = $artist_name['artistName'];
        }
    }
    if ($isVenue) {
        $venue_name = my_sql::select("*", "venueaccount", "userEmail = :0", array($user_email));
        if ($venue_name) {
            $venue_name = $venue_name[0];
            $description = $venue_name['description'];
            $venue_name = $venue_name['venueName'];
        }
    }
    $opt = my_sql::select("isOnMailList, blogOptIn", "useraccount", "userEmail = :0", array($user_email));
    if ($opt) {
        $optIn = $opt[0]['isOnMailList'];
        $blog_notif_opt = $opt[0]['blogOptIn'];
    } else {
        $optIn = null;
        $blog_notif_opt = null;
    }
}
$user_image = $user->get_profile_image_id();
$profile_dir = my_sql::select("*", "image", "imageID = :0", array("$user_image"));
if (count($profile_dir) > 0) {
    $profile_dir = $profile_dir[0];
    $profile_dir = $profile_dir['filename'];
} else {
    $profile_dir = "";
}
$Saddress = $user->get_shipping_address_id();
$Baddress = $user->get_billing_address_id();
$ship_address = address::get_shipping_inputs($Saddress);
$bill_address = address::get_billing_inputs($Baddress);

if (isset($_POST['update_profile'])) {
    $user->update_field("firstName", $_POST['first_name']);
    $user->update_field("lastName", $_POST['last_name']);
    $update_name = 1;
    if (isset($_POST['artist_name']) && $isArtist) {
        $check = my_sql::exists("artistaccount", "artistName = :0", array($artist_name));
        if ($check) {
            $update_name = my_sql::update("artistaccount", array("artistName"), array($_POST['artist_name']), "userEmail = :0", array($user_email));
            if ($update_name) header("Refresh:0,url = update_profile.php?confirm=done");
        }
    }
    if (isset($_POST['venue_name']) && $isVenue) {
        $check = my_sql::exists("venueaccount", "venueName = :0", array($venue_name));
        if ($check) {
            $update_name = my_sql::update("venueaccount", array("venueName"), array($_POST['venue_name']), "userEmail = :0", array($user_email));
            if ($update_name) header("Refresh:0,url = update_profile.php?confirm=done");
        }
    }
    if (!isset($update_name)) {
        echo "<br>Artist/Venue name already in use";
    } else {
        header("Refresh:0,url = update_profile.php?confirm=done");
    }
}
if (isset($_POST['update_description'])) {
    if ($isArtist) {
        $update_des = my_sql::update("artistaccount", array("description"), array($_POST['description']), "userEmail = :0", array($user_email));
        if ($update_des) {
            header("Refresh:0,url = update_profile.php?confirm=done");
        }
    }
    if ($isVenue) {
        $update_des = my_sql::update("venueaccount", array("description"), array($_POST['description']), "userEmail = :0", array($user_email));
        if ($update_des) {
            header("Refresh:0,url = update_profile.php?confirm=done");
        }
    }
}
if (isset($_POST['update_address'])) {
    if (address::is_valid_address_input($_POST['ship_address'])) {
        if ($Saddress !== "") {
            $result = address::update_address($Saddress, $_POST['ship_address']);
        } else {
            $result = address::add_address($_POST['ship_address'], $user_email, "shippingAddressID");
            $Saddress = $user->get_shipping_address_id();
            $ship_address = address::get_shipping_inputs($Saddress);
        }
        header("Refresh:0,url = update_profile.php?confirm=done");
    } else {
        echo "Make sure all Shipping fields are filled in.";
    }
    if (address::is_valid_address_input($_POST['bill_address'])) {
        if ($Baddress !== "") {
            $result = address::update_address($Baddress, $_POST['bill_address']);
        } else {
            $result = address::add_address($_POST['bill_address'], $user_email, "billingAddressID");
            $Baddress = $user->get_billing_address_id();
            $bill_address = address::get_billing_inputs($Baddress);
        }
        header("Refresh:0,url = update_profile.php?confirm=done");
    } else {
        echo "Make sure all Billing fields are filled in.";
    }
}
if (isset($_POST['delete_post'])) {
    $event_post = new eventpost($_POST['delete_post']);
    if ($event_post) {
        if ($event_post->delete_from_database()) {
            echo "Post deleted succesfully";
        } else {
            echo "Error: Post could not be deleted";
        }
    }
}
if (isset($_POST['delete_song'])) {
    $song = new song($artist_name, null, null, $_POST['delete_song']);
    $result = $song->delete_from_database();
    if ($result) {
        echo "Song deleted succesfully";
        unlink("../files/" . $artist_name . "/" . $_POST['delete_song']);
    } else {
        echo "Error: Song not found";
    }
}
if (isset($_POST['confirm_delete'])) {
    if ($user->delete()) {
        header("location:logout.php");
    }
}
if (isset($_POST['newsletter_opt'])) {
    $opt = !$optIn ? "1" : "0";
    $update = my_sql::update("useraccount", array("isOnMailList"), array($opt), "userEmail = :0", array($user_email));
    if ($update) $optIn = !$optIn;
}
if (isset($_POST['blog_notif_opt'])) {
    $opt = !$blog_notif_opt ? "1" : "0";
    $update = my_sql::update("useraccount", array("blogOptIn"), array($opt), "userEmail = :0", array($user_email));
    if ($update) $blog_notif_opt = !$blog_notif_opt;
}
?>


<div id="section_nav" class="row">
    <table id="section_nav_table" class="buttons">
        <tr>
            <td class="nav_button_td"><button id="personal_info_btn" onclick="openView('personal_info','personal_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                    <h3>Personal</h3>
                </button>
            <td class="nav_button_td"><button id="address_info_btn" class="btn btn-outline-dark" onclick="openView('address_info','address_info_btn');" style="width:100%;color:#FF0080;">
                    <h3>Address</h3>
                </button>
                <?php if ($isArtist) {
                    echo "<td class=\"nav_button_td\"><button id=\"song_info_btn\" class=\"btn btn-outline-dark\" onclick=\"openView('song_info','song_info_btn');\" style=\"width:100%;color:#FF0080;\">";
                    echo "<h3>Songs</h3>";
                    echo "</button>";
                }
                ?><?php if ($isArtist || $isVenue) {
                        echo "<td class=\"nav_button_td\"><button id=\"post_info_btn\" class=\"btn btn-outline-dark\" onclick=\"openView('post_info','post_info_btn');\" style=\"width:100%;color:#FF0080;\">";
                        echo "<h3>Posts</h3>";
                        echo "</button>";
                    }
                    ?>
            <td class="nav_button_td"><button id="control_info_btn" class="btn btn-outline-dark" onclick="openView('control_info','control_info_btn');" style="width:100%;color:#FF0080;">
                    <h3>Account Controls</h3>
                </button>
            <td class="nav_button_td">
                <button class="btn btn-outline-dark" style="width:100%;color:#FF0080;" onclick="window.location.href = 'profile_page.php'">
                    <h3>Back to Profile Page</h3>
                </button>
    </table>
</div>

<div id="personal_info" style="display:block" sclass="row">
    <br>
    <hr><br>
    <h2>Update profile for <?php echo $user_email ?></h2><br>
    <?php
    echo "<div id='user_pic'>" . $user->get_small_profile_image() . "</div>";

    include "profile_picture_uploader.php";
    echo "<br><hr><br>";
    echo "<div class=\"row\">";
    echo "<div id=\"name\" class=\"col-sm-4\">";
    echo "<form id=\"profile_update\" method=\"POST\" >";
    if ($isArtist) {
        echo "<input type='text' name='artist_name' value=\"" . $artist_name . "\" /><label> &nbsp;Artist Name</label><br>";
    }
    if ($isVenue) {
        echo "<input type='text' name='venue_name' value=\"" . $venue_name . "\" /><label> &nbsp;Venue Name</label><br>";
    }
    echo "<input type='text' name='first_name' value='" . $user->get_first_name() . "' /><label> &nbsp;	First Name</label><br>";
    echo "<input type='text' name='last_name' value='" . $user->get_last_name() . "' /><label> &nbsp;	Last Name</label><br>";
    echo "<button class=\"btn-success\" type=\"submit\" name=\"update_profile\">Save Name</button>";
    echo "</form>";
    echo "</div>";
    if ($isArtist || $isVenue) {
        echo "<div id=\"desc\" class=\"col-sm-8\">";
        echo "<form id=\"description_update\" method=\"POST\" >";
        echo "<label>Description: </label><textarea name='description' form='description_update' rows='8' cols='16' maxlength='4000'>$description</textarea>";
        echo "<button class=\"btn-success\" type=\"submit\" name=\"update_description\">Save Description</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>
</div>
</div>

<div id="address_info" style="display:none" class="row"><br>
    <hr><br>
    <?php
    echo "<form id=\"address_update\" method=\"POST\" >";
    echo $ship_address;
    echo "<br>
    <hr><br>";
    echo $bill_address;
    echo "<button class=\"btn-success\" type=\"submit\" name=\"update_address\">Save Changes</button>";
    echo "</form>";
    ?>
</div>

<?php if ($isArtist) {

    echo "<div id=\"song_info\" style=\"display:none\" class=\"row\">";
    echo "<br>
    <hr><br>";
    echo "<h3>Songs</h3>";
    echo "<button onclick=\"window.location.href = 'song_uploader.php'\">Add New Songs Here</button><br>";
    //List songs
    $songs = my_sql::select("*", "artistsong", "artistName = :0", array("$artist_name"));
    if ($songs > 0) {
        foreach ($songs as $song) {
            echo "<br>
            <hr><br>";
            $title = $song['title'];
            $pieces = explode("/", $title);
            $song = new song($artist_name, null, null, $title);
            echo "<form action=\"\" onsubmit=\"return confirm('Are you sure you want to delete this song?');\" method=\"POST\"><label>" . $pieces[count($pieces) - 1] . " </label><button class=\"btn-danger\" id=\"confirm_$title\" type=\"submit\" name=\"delete_song\" value=\"$title\">Delete</button></form>";
            echo "<audio class='default-audio' controls>";
            echo "<source src='../$title' type='audio/mpeg'>";
            echo "</audio>";
        }
    }
    echo "</div>";
}

if ($isArtist || $isVenue) {
    echo "<div style=\"display:none\" id=\"post_info\" class=\"row\">";
    echo "<br>
    <hr><br>";
    echo "<h3>Posts</h3>";
    echo "<button onclick=\"window.location.href = 'event_post_uploader.php'\">Add New Event Posting Here</button>";
    //List Posts
    $posts = my_sql::select("*", "eventpost", "userEmail = :0", array("$user_email"));
    if ($posts > 0) {

        foreach ($posts as $post) {
            echo "<br>
    <hr><br>";
            //TODO: Add delete function
            $title = $post['title'];
            $id = $post['eventID'];
            $post = new eventpost($post['eventID']);
            echo "<br><form action=\"\" onsubmit=\"return confirm('Are you sure you want to delete this post?');\" method=\"POST\"><label>" . $title . "</label><br><button class=\"btn-danger\" id=\"confirm_$title\" type=\"submit\" name=\"delete_post\" value=\"$id\">Delete</button></form>
            <form action=\"edit_eventpost.php\" method=\"POST\"><button type=\"submit\" name=\"event_id\" value=\"$id\" >Edit</button></form>";
        }
    }
    echo "</div>";
}
echo "<br>
    <hr><br>";
?>

<div id="control_info" style="display:none">
    <?php
    if ($optIn == 0) {
        $message = "Confirm: Opt in for Sleepovers For Life Newsletter";
        $button = "Sign Up For Newsletter";
    } elseif ($optIn == 1) {
        $message = "Confirm: Opt out of Sleepovers For Life Newsletter";
        $button = "Stop Recieving Newsletter";
    } else {
        $message = "error";
        $button = "error";
    }

    if ($blog_notif_opt == 0) {
        $blog_message = "Confirm: Opt in for Sleepovers For Life Blog Notifications";
        $blog_button = "Sign Up For Blog Notifications";
    } elseif ($blog_notif_opt == 1) {
        $blog_message = "Confirm: Opt out of Sleepovers For Life Blog Notifications";
        $blog_button = "Stop Recieving Blog Notifications";
    } else {
        $blog_message = "error";
        $blog_button = "error";
    }

    //newletter opt-in
    echo "<form action=\"\" method=\"POST\" onsubmit=\"return confirm('" . $message . "');\">";
    echo "<button type=\"submit\" name=\"newsletter_opt\" >" . $button . "</button>";
    echo "</form>";

    //blog notification opt-in
    echo "<form action=\"\" method=\"POST\" onsubmit=\"return confirm('" . $blog_message . "');\">";
    echo "<button type=\"submit\" name=\"blog_notif_opt\" >" . $blog_button . "</button>";
    echo "</form>";
    ?>
    <button onclick="window.location.href = 'change_password.php'">Change Password</button><br>
    <?php
    echo "<form action=\"\" method=\"POST\" onsubmit=\"return confirm('Are you sure your want to delete your account? Your information will not be deleted, but your account be deactivated. This can only be undone by an administrator.');\">";
    echo "<button class=\"btn-danger\" type=\"submit\" name=\"confirm_delete\" >Delete Account</button>";
    echo "</form>";
    ?>

</div>

<?php include 'footer.php'; ?>
<script>
    window.onload = function() {
        var div = document.getElementById('personal_info_btn');
        div.style.backgroundColor = '#333';
    };

    function openView(selection, button) {
        var list = ['personal_info', 'address_info', 'song_info', 'post_info', 'control_info'];
        var list2 = ['personal_info_btn', 'address_info_btn', 'song_info_btn', 'post_info_btn', 'control_info_btn'];

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