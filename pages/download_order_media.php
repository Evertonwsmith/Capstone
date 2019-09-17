<?php
require_once '../src/my_sql.php';
include_once '../src/my_sql_cred.php';
include_once 'admin_gateway.php';

if (!isset($_REQUEST['media_group_id'])) {
    exit("Missing media group ID!");
}

$media_group_id = $_REQUEST['media_group_id'];

if (my_sql::exists("image", "mediaGroupID=:0", array($media_group_id))) {
    $image_files = my_sql::select("filename", "image", "mediaGroupID=:0", array($media_group_id));
} else {
    $image_files = null;
}
if (my_sql::exists("audio", "mediaGroupID=:0", array($media_group_id))) {
    $audio_files = my_sql::select("filename", "audio", "mediaGroupID=:0", array($media_group_id));
} else {
    $audio_files = null;
}
$files = array();
if (isset($image_files)) {
    foreach ($image_files as $file) {
        array_push($files, "../" . $file['filename']);
    }
}
if (isset($audio_files)) {
    foreach ($audio_files as $file) {
        array_push($files, "../" . $file['filename']);
    }
}
if (!(isset($files) && count($files) > 0)) {
    exit("No attached files.");
}

if (!file_exists("../files/tmp")) {
    mkdir("../files/tmp", 0777, true);
}

$zipname = "../files/tmp/order_media_$media_group_id.zip";
$zip = new ZipArchive;
$zip->open($zipname, ZipArchive::CREATE);
foreach ($files as $file) {
    $file_name = explode("/", $file);
    $file_name = count($file_name) > 1 ? $file_name[count($file_name) - 1] : $file_name[0];
    $zip->addFile($file, $file_name);
}
$zip->close();

header('Content-Type: application/zip');
header("Content-disposition: attachment; filename=order_media_$media_group_id.zip");
header('Content-Length: ' . filesize($zipname));
readfile($zipname);
unlink($zipname); //delete zip
