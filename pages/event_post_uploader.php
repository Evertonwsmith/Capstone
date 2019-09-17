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
    $newMGID = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null));
    $_SESSION['media_group_id'] = $newMGID;
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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["title"]) || empty($_POST["text"])) {
        echo "Please fill out both boxes";
    } else {
        $timestamp = date('Y-m-d G:i:s');
        $mgid = $_SESSION['media_group_id'];
        $id = my_sql::insert_get_last_id("eventpost", array("eventID", "userEmail", "mediaGroupID", "title", "timestamp", "text", "isPublic"), array(null, $user_email, $mgid, $_POST['title'], $timestamp, $_POST['text'], 1));
        echo "Post Successfully Added";
    }
}

?><br>
<br>
<h2>New Event Post</h2><br>

<div id="new_event_post">
    (Optional) Add photos to post
    <?php
    include "event_media_uploader.php";
    ?>
    <hr><br>
    <form id="new_post" action="" method="POST">
        <label>Title:</label><br><input type="text" name="title"><br>
        <label>Post:</label><textarea form="new_post" maxlength="4000" rows="8" cols="16" name="text"></textarea><br><br>
        <br>
        <hr><br>

        <button class="btn-success" type="submit" name="submit">Submit</button>
    </form>
    <button onclick="window.location.href = 'update_profile.php'">Back</button><br>
    ***Make sure to hit [Save Photo(s) To Post] before [Submit] if adding photos***
</div>