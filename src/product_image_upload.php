<?php
require_once 'my_sql.php';
require_once 'my_sql_cred.php';

session_start();

//Ensure media_group_id is set
if (!isset($_SESSION['media_group_id'])) {
    $media_group_id = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null)); //Reserve a mediaGroupID
    if ($media_group_id == false) {
        //Alert error
        die('{"OK": 0, "info": "Bad SESSION variable: media_group_id"}');
    }
    $_SESSION['media_group_id'] = $media_group_id;
} else {
    $media_group_id = $_SESSION['media_group_id'];
}

//Ensure product_id is set
if (!isset($_SESSION['product_id'])) {
    //Alert error
    die('{"OK": 0, "info": "Bad SESSION variable: product_id"}');
} else {
    $product_id = $_SESSION['product_id'];
}

//Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    die('{"OK": 0, "info": "Bad SESSION variable: user_email"}');
}

$upload_directory = "product/$product_id/$media_group_id/";

//set directory
$directory = "../files/" . $upload_directory;

//include the upload script
include 'pluploader/upload.php';

//Add to database if upload is complete
if ($upload_complete) {
    //filePath from upload.php
    $file_root_name = substr($filePath, 3, strlen($filePath)); //Remove "../" from file path
    $insert = my_sql::insert("image", array("imageID", "mediaGroupID", "filename"), array(null, $media_group_id, $file_root_name));
}

//send feedback
die('{"OK": 1, "info": "Upload successful."}');
