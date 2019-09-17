<?php
require_once 'my_sql.php';
require_once 'my_sql_cred.php';
require_once 'blogpost.php';

session_start();
//$debug = fopen("debug.txt", "w");

//Ensure blog_id is set
if (!isset($_SESSION['blog_id'])) {
    //Alert error
    die('{"OK": 0, "info": "Bad SESSION variable: blog_id"}');
} else {
    $blog_id = $_SESSION['blog_id'];
    $blog = new blogpost($blog_id);
}

//Ensure media_group_id is set
if (!isset($_SESSION['media_group_id'])) {
    $media_group_id = $blog->get_media_group_id();
    if (!isset($media_group_id) || !my_sql::exists("mediagroup", "mediaGroupID=:0", array($media_group_id))) {
        //create a new mediaGroupID
        $media_group_id = my_sql::insert_get_last_id("mediagroup", array("mediaGroupID"), array(null)); //Reserve a mediaGroupID
        if ($media_group_id == false) {
            //Alert error
            die('{"OK": 0, "info": "Bad SESSION variable: media_group_id"}');
        } else {
            $blog->set_media_group_id($media_group_id);
        }
    }
    $_SESSION['media_group_id'] = $media_group_id;
} else {
    $media_group_id = $_SESSION['media_group_id'];
}

//Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    die('{"OK": 0, "info": "Bad SESSION variable: user_email"}');
}

$upload_directory = "blogpost/$blog_id/$media_group_id/";

//set directory
$directory = "../files/" . $upload_directory;

//fwrite($debug, $directory . "\n");

//include the upload script
include 'pluploader/upload.php';

//Add to database if upload is complete
if ($upload_complete) {
    //filePath from upload.php
    $file_root_name = substr($filePath, 3, strlen($filePath)); //Remove "../" from file path
    //fwrite($debug, $file_root_name . "\n");
    $path_info = pathinfo($file_root_name);
    $extension = $path_info['extension'];
    $audio_types = ["mp3", "wav", "FLAC"];
    $image_types = ["jpg", "jpeg", "png", "gif", "bmp"];
    if (in_array($extension, $audio_types)) $table = "audio";
    if (in_array($extension, $image_types)) $table = "image";
    $insert = my_sql::insert($table, array($table . "ID", "mediaGroupID", "filename"), array(null, $media_group_id, $file_root_name));
}

//fclose($debug);

//send feedback
die('{"OK": 1, "info": "Upload successful."}');
