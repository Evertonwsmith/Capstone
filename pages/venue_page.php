<?php
$page_title = "Venue Page";
include "header.php";
require_once '../src/eventpost.php';
require_once "../src/user.php";
require_once "../src/address.php";


//Check if venue name is set
if (!$_GET['venue_name']) {
    header("location:venues.php");
} else {
    $venue_name = stripslashes($_GET['venue_name']);
    //Check that venue_name in venueaccount table
    //TODO: Change to user class isvenue check once implemented
    $result = my_sql::select("*", "venueaccount", "venueName = :0", array("$venue_name"));
    if (is_array($result) || is_object($result)) {
        foreach ($result as $value) {
            $venue_image_ID = $value['venueImageID'];
            $venue_address = $value['addressID'];
            $venue_des = $value['description'];
            $venue_email = $value['userEmail'];
            $venue_address = address::get_shipping_address_by_id($venue_address);
            $user = new user($venue_email);
        }
    }
    if (!$venue_email) {
        //Redirect if no valid venue email found
        header("location:venues.php");
    }
}
$event_posts = new eventpost(1);
$event_posts = $event_posts->get_all('eventID', 0, null, "userEmail = :0", array("$venue_email"));
$profile_image = my_sql::select("*", "image", "imageID = :0", array("$venue_image_ID"));
if ($profile_image) {
    $profile_image = $profile_image[0];
    $profile_dir = $profile_image['filename'];
} else {
    $profile_dir = "";
}
?>
<style>
    table,
    td,
    tr,
    th {
        border: 1px solid black;
    }
</style>
<div>
    <table class="buttons">
        <tr>
            <td><button id="venue_info_btn" onclick="openView('venue_info','venue_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                    <h3>About the venue</h3>
                </button>
            <td><button id="post_info_btn" onclick="openView('post_list','post_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                    <h3><?php echo $venue_name; ?>'s Posts</h3>
                </button>

    </table><br>
    <hr><br>
    <div>



    </div>
    <div id="venue_info">
        <div class="row">
            <div class="col-sm-5">
                <h1><?php echo $venue_name ?></h1>
                <br>
                <?php
                echo "<div id='venue_pic' class='img-profile-container-large'>" . $user->get_profile_image() . "</div>";
                ?>
            </div>

            <!-- <div class="col-sm-5">
                <h4>Address:</h4>
                <?php
                echo address::address_to_string($venue_address);
                // if ($venue_address['street'] === "") {
                //     echo "Not address available at this time";
                // } else {
                //     echo $venue_address['street'] . ", " . $venue_address['city'] . ", " . $venue_address['province'] . ", " . $venue_address['postalCode'];
                // }
                ?>
            </div> -->
        </div>
        <br>
        <hr><br>
        <h2>About <?php echo $venue_name; ?></h2>
        <p><?php echo $venue_des; ?></p><br>
    </div>
</div>

<div id="post_list" style="display:none">
    <h2><?php echo $venue_name; ?>'s Posts</h2>
    <?php
    $i = 0;
    foreach ($event_posts as $event_post) {
        $i++;
        echo "<br><hr><br>";
        echo "<div id='event" . $i . "'";
        echo "Post #" . $event_post->get_event_id() . "<br>";
        echo "<h3>" . $event_post->get_title() . "</h3><br>";
        //echo "$event_post->get_timestamp()";
        echo $event_post->get_text() . "";
        $imgID = $event_post->get_media();
        $images = my_sql::select("*", "image", "mediaGroupID = :0", array($imgID));
        if ($images) {
            foreach ($images as $image) {
                echo "<img class=\"img-thumbnail\" src=\"../" . $image['filename'] . "\" alt=\"No image available..\"'></img><br><br>";
                //echo "<img src=\"../" . $image['filename'] . "\" style=\"width:150px;height:150px;padding:3px;\" alt=\"Post Picture\">";
            }
        }
        echo "</div>";
    }

    ?>
</div>

<?php
include "footer.php";
?>
<script>
    window.onload = function() {
        document.getElementById("venue_info_btn").click();
    };

    function openView(selection, button) {
        var list = ['venue_info', 'post_list'];
        var list2 = ['venue_info_btn', 'post_info_btn'];

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
    td,
    table,
    tr {
        border: rgba(17, 17, 17, 0.5);
    }

    tr:nth-child(odd):hover {
        background-color: rgba(17, 17, 17, 0.5);
    }
</style>