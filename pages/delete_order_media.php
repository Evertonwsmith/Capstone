<?php
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';
include_once 'admin_gateway.php';

if (!isset($_REQUEST['media_group_id'])) {
    exit("Missing media group ID!");
}

$media_group_id = $_REQUEST['media_group_id'];

//Remove files from database
if (my_sql::exists("image", "mediaGroupID=:0", array($media_group_id))) {
    $image_files = my_sql::select("filename", "image", "mediaGroupID=:0", array($media_group_id));
    my_sql::delete("image", "mediaGroupID=:0", array($media_group_id));
} else {
    $image_files = null;
}
if (my_sql::exists("audio", "mediaGroupID=:0", array($media_group_id))) {
    $audio_files = my_sql::select("filename", "audio", "mediaGroupID=:0", array($media_group_id));
    my_sql::delete("audio", "mediaGroupID=:0", array($media_group_id));
} else {
    $audio_files = null;
}

//Delete files from server
if (isset($image_files)) {
    foreach ($image_files as $file) {
        if (file_exists("../" . $file['filename'])) {
            unlink("../" . $file['filename']);
        }
    }
}
if (isset($audio_files)) {
    foreach ($audio_files as $file) {
        if (file_exists("../" . $file['filename'])) {
            unlink("../" . $file['filename']);
        }
    }
}
