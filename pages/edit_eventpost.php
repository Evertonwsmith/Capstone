<?php
include "header.php";
require_once "../src/eventpost.php";
$isArtist = false;
$isVenue = false;
//Check if artist or venue
if (!isset($_SESSION['user_email'])) {
    header("location:home.php");
} else {
    $user_email = $_SESSION['user_email'];
}
$result = my_sql::select('*', 'artistaccount', "userEmail = :0", array("$user_email"));
if (isset($result) && !empty($result)) {
    foreach ($result as $row) {
        $_SESSION['artist_name'] = $row['artistName'];
    }
    $isArtist = true;
}
$result = my_sql::select('*', 'venueaccount', "userEmail = :0", array("$user_email"));
if (isset($result) && !empty($result)) {
    foreach ($result as $row) {
        $_SESSION['venue_name'] = $row['venueName'];
    }
    $isVenue = true;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $deleted_images = $_POST['image_delete'];
    $deleted_images = explode(",", $deleted_images);
    foreach ($deleted_images as $img) {
        $deleted = my_sql::update("image", array("mediaGroupID"), array(null), "filename = :0", array($img));
    }
    $id = $_POST['submit'];
    $event = new eventpost($id);
    $old_title = $event->get_title();
    $old_text = $event->get_text();
    if (!empty($_POST["title"])) {
        $old_title = $_POST['title'];
    }
    if (!empty($_POST["text"])) {
        $old_text = $_POST['text'];
    }
    $event->set_timestamp(true);
    $event->set_title($old_title);
    $event->set_text($old_text);
    echo "Post Successfully Updated<br>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $id = $_POST['event_id'];
    $event = new eventpost($id);
    $old_title = $event->get_title();
    $old_text = $event->get_text();
    $mediaGroup = $event->get_media();
    if (empty($mediaGroup)) {
        $mediaGroup = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null));
        $event->set_media_group_id($mediaGroup);
    }
    $_SESSION['media_group_id'] = $mediaGroup;
}


?>
<h2>Edit Event Post</h2><br>
<hr><br>
<div id="new_event_post">
    <?php
    if (isset($mediaGroup)) {
        $images = my_sql::select("*", "image", "mediaGroupID = :0", array($mediaGroup));
        if ($images) {
            foreach ($images as $image) {
                echo "<div class='img-profile-container'><img class='img-thumbnail' id=\"" . $image['filename'] . "\" src=\"../" . $image['filename'] . "\" alt=\"Post Picture\"></div><button onclick=\"deleteImg('" . $image['filename'] . "');document.getElementById('" . $image['filename'] . "').style.display='none';this.style.display='none'\">Remove Image</button><br>";
                echo "<br>";
            }
        }
    }
    echo "<br>(Optional) Add more photos to event<br>";
    include "event_media_uploader.php";
    ?><br>
    <hr><br>
    <form id="edited_post" action="" method="POST">
        <label>Title:</label><br><input type="text" name="title" placeholder="<?php echo $old_title; ?>"><br>
        <label>Post:</label><textarea form="edited_post" maxlength="4000" rows="8" cols="16" name="text"><?php echo $old_text; ?></textarea><br><br>
        <button class="btn-success" type="submit" name="submit" value="<?php echo $id ?>">Submit</button>
    </form>
    <button onclick="window.location.href = 'update_profile.php'">Back</button>
</div>
<script>
    var value = "";

    function deleteImg(name) {
        value = value + name + ",";
        console.log(value);
    }
    $('#edited_post').submit(function() { //listen for submit event
        $('<input />').attr('type', 'hidden')
            .attr('name', 'image_delete')
            .attr('value', value)
            .appendTo('#edited_post');
        return true;
    });
</script>