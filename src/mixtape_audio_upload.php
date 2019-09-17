<?php
require_once 'my_sql.php';
require_once 'my_sql_cred.php';

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

//Add to database if upload is complete
if ($upload_complete) {
    //filePath from upload.php
    $file_root_name = substr($filePath, 3, strlen($filePath)); //Remove "../" from file path
    $path_info = pathinfo($file_root_name);
    $extension = $path_info['extension'];
    $audio_types = ["mp3", "wav", "FLAC"];
    $image_types = ["jpg", "jpeg", "png", "gif", "bmp"];
    $table = "image"; //default for now (some txt files might end up in here :/ )
    if (in_array($extension, $audio_types)) $table = "audio";
    if (in_array($extension, $image_types)) $table = "image";
    $insert = my_sql::insert("$table", array($table . "ID", "mediaGroupID", "filename"), array(null, $media_group_id, $file_root_name));
}

//send feedback
die('{"OK": 1, "info": "Upload successful."}');
