<?php
require_once 'my_sql.php';
require_once 'my_sql_cred.php';
require_once 'user.php';
require_once 'eventpost.php';
session_start();
//Ensure upload directory is set
if (!isset($_SESSION['upload_directory'])) {
    die('{"OK": 0, "info": "Bad SESSION variable: upload_directory"}');
}

//Ensure media_group_id is set
if (!isset($_SESSION['media_group_id'])) {
    die('{"OK": 0, "info": "Bad SESSION variable: media_group_id"}');
} else {
    $media_group_id = $_SESSION['media_group_id'];
}

//Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    die('{"OK": 0, "info": "Bad SESSION variable: user_email"}');
}

//set directory
$directory = "../files/" . $_SESSION['upload_directory'];

//include the upload script
include 'pluploader/upload.php';

fwrite($debug, $filePath);

//Add to database if upload is complete
if ($upload_complete) {
    //filePath from upload.php
    $file_root_name = substr($filePath, 3, strlen($filePath)); //Remove "../" from file path
    $insert = my_sql::insert_get_last_id("image", array("imageID", "mediaGroupID", "filename"), array(null, $media_group_id, $file_root_name));
       
    /**
     * TODO: get SESSION variable for orderID and set it's media_group_id to the one in this script
     */
}

//send feedback
die('{"OK": 1, "info": "Upload successful."}');
