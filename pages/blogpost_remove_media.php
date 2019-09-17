<?php
require_once '../src/my_sql.php';
require_once '../src/my_sql_cred.php';
include 'admin_gateway.php';

if (isset($_POST['table'], $_POST['id'], $_POST['filename'])) {
    $table = $_POST['table'];
    $id = $_POST['id'];
    $filename = $_POST['filename'];

    //delete file
    if (file_exists("../$filename")) {
        unlink("../$filename");
    }

    //remove media
    my_sql::delete($table, $table . "ID=:0", array($id));
} else {
    die();
}
