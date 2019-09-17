<?php
require_once 'my_sql.php';
require_once 'my_sql_cred.php';
require_once 'user.php';

session_start();

//Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    die('{"OK": 0, "info": "Bad SESSION variable: user_email"}');
} else {
    $user_email = $_SESSION['user_email'];
    if (!isset($_SESSION['email_hash'])) {
        $email_hash = hash('sha256', $user_email);
        $_SESSION['email_hash'] = $email_hash;
    } else {
        $email_hash = $_SESSION['email_hash'];
    }
}

$upload_directory = "profile/$email_hash/profile_pic/";

//set directory
$directory = "../files/" . $upload_directory;

//include the upload script
include 'pluploader/upload.php';

//Add to database if upload is complete
if ($upload_complete) {
    //filePath from upload.php
    $file_root_name = substr($filePath, 3, strlen($filePath)); //Remove "../" from file path
    $insert = my_sql::insert_get_last_id("image", array("imageID", "filename"), array(null, $file_root_name));
    $update = my_sql::update("useraccount", array("profileImageID"), array($insert), "userEmail = :0", array($user_email));
    $user = new user($user_email);
    $isArtist = $user->is_artist();
    $isVenue = $user->is_venue();
    if ($isArtist) {
        $update = my_sql::update("artistaccount", array("artistImageID"), array($insert), "userEmail = :0", array($user_email));
    }
    if ($isVenue) {
        $update = my_sql::update("venueaccount", array("venueImageID"), array($insert), "userEmail = :0", array($user_email));
    }
}

//send feedback
die('{"OK": 1, "info": "Upload successful."}');
