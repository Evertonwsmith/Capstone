<?php
$page_title = "Artist Page";
include "header.php";
require_once '../src/eventpost.php';
require_once "../src/mail/mail.php";
require_once "../src/mail/mail_cred.php";

//Check if artist name is set
if (!$_GET['artist_name']) {
    header("location:artists.php");
} else {
    $artist_name = $_GET['artist_name'];
    //Check that artist_name in artistaccount table
    //TODO: Change to user class isArtist check once implemented
    $result1 = my_sql::select('*', '`artistaccount`', "artistName = :0", array("$artist_name"), null, null, null);
    if (is_array($result1) || is_object($result1)) {
        foreach ($result1 as $value1) {
            $artist_image_ID = print_r($value1['artistImageID'], true);
            $artist_email = print_r($value1['userEmail'], true);
            $artist_des = print_r($value1['description'], true);
            $user = new user($artist_email);
        }
    }
    if (!$artist_email) {
        //Redirect if no valid artist email found
        header("location:artists.php");
    }
}
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    $result = my_sql::select("*", "venueaccount", "useremail = :0", array($user_email));
    if ($result) {
        $result = $result[0];
        $venue_name = $result['venueName'];
        $venue_email = $result['userEmail'];
        $venue_address = $result['addressID'];
    }
}
if (isset($_POST['contact_artist'])) {
    $mail = new mail($mail_sender_email, $mail_sender_password, "artist_notification");
    $mail->set_recipient($artist_email);
    $mail->set_subject("Sleepovers - Booking Request");
    $mail->set_port(587);
    $mail->set_body("Hello " . $artist_name . ", a venue wants to book you! " . $venue_name . " wants you to play at their location.<br><br>Contact them at the email address below for further details.<br><br>" . $venue_email . "<br><br>Sleepovers For Life.");
    $mail->send_mail();
    echo "Artist has been sent your contact information!";
}
$event_posts = new eventpost(1);
$event_posts = $event_posts->get_all('eventID', 0, null, "userEmail = :0", array("$artist_email"));

$song_list = my_sql::select("*", "artistsong", "artistName = :0", array("$artist_name"), "`songNumber` ASC");
$profile_image = my_sql::select("*", "image", "imageID = :0", array("$artist_image_ID"));
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
            <td><button id="artist_info_btn" onclick="openView('artist_info','artist_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                    <h3>About the Artist</h3>
                </button>
            <td><button id="song_info_btn" onclick="openView('song_list','song_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                    <h3>Song List</h3>
                </button>
            <td><button id="post_info_btn" onclick="openView('post_list','post_info_btn');" class="btn btn-outline-dark" style="width:100%;color:#FF0080;">
                    <h3><?php echo $artist_name; ?>'s Posts</h3>
                </button>

    </table>
    <br>
    <hr><br>

    <div id="artist_info">
        <div class="row">
            <div id="profile_image" class="col-sm-5">
                <h1><?php echo $artist_name ?></h1>
                <br>
                <?php
                echo "<div id='artist_pic' class='img-profile-container-large'>" . $user->get_profile_image() . "</div>";
                ?>
            </div>
            <?php
            if (isset($venue_name)) {
                echo "<form action=\"\" method=\"POST\" onsubmit=\"return confirm('Artist will be sent an email with your contact info. Continue?');\">";
                echo "<button type=\"submit\" name=\"contact_artist\" >Contact artist for booking</button></h3>";
                echo "</form>";
            }
            ?>

        </div><br>
        <hr><br>
        <h2>About <?php echo $artist_name; ?></h2>
        <p><?php echo $artist_des; ?></p>
    </div>

    <?php
    echo "<div id=\"song_list\" style=\"display:none\"><h2>Songs by " . $artist_name . "</h2>";
    //If any songs exist, print them out as playable
    if (!empty($song_list)) {
        foreach ($song_list as $song) {
            echo "<br><hr><br>";
            $dir = "../" . $song['title'] . "";
            $pieces = explode("/", $song['title']);
            echo $pieces[count($pieces) - 1] . "<br><br>";
            echo "<audio class='default-audio' controls>";
            echo "<source src='$dir' type='audio/mpeg'>";
            echo "</audio><br><br>";
        }
    }
    echo "<br><hr><br>";
    echo "</div>";
    ?>

    <div id="post_list" style="display:none">
        <h2>Posts by <?php echo $artist_name; ?></h2>
        <?php
        $i = 0;
        foreach ($event_posts as $event_post) {
            echo "<br><hr><br>";
            $i++;
            echo "<div id='event" . $i . "'";
            echo "Post #" . $event_post->get_event_id() . "<br>";
            echo "<h3>" . $event_post->get_title() . "</h3><br>";
            //echo "$event_post->get_timestamp()";
            echo $event_post->get_text() . "<br><br>";
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
        echo "<br><hr><br>";
        ?>
    </div>

</div>

<?php
include "footer.php";
?>
<script>
    window.onload = function() {
        document.getElementById("artist_info_btn").click();
    };

    function openView(selection, button) {
        var list = ['artist_info', 'song_list', 'post_list'];
        var list2 = ['artist_info_btn', 'song_info_btn', 'post_info_btn'];

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