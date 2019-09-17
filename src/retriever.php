<?php
require_once 'user.php';
require_once 'my_sql.php';
require_once 'search_catalog.php';
include 'my_sql_cred.php';

if (isset($_REQUEST['table'])) {
    $table = $_REQUEST['table'];
    $is_protected = in_array($table, search_catalog::get_catalog_list(true));

    if ($is_protected) {
        //Start session
        session_start();
        //Get user information
        if (isset($_SESSION['user_email'])) {
            //Verify user is allowed to be here
            $user = new user($_SESSION['user_email']);
            $is_admin_or_staff = $user->is_staff() || $user->is_admin();
            if (!$is_admin_or_staff) {
                exit("<h3 class='error'>You do not have the required permissions to view this content.</h3>");
            }
        } else {
            exit("<h3 class='error'>You do not have the required permissions to view this content.</h3>");
        }

        //Access and set filename
        $results = my_sql::select("pass", "folderpass", "name=:0", array("$table"));
        if ($results != false && count($results) == 1) {
            $pass = $results[0]['pass'];
            $filename = search_catalog::$root_dir . "$pass/$table" . search_catalog::$extension;;
        }
    } else {
        $filename = search_catalog::$root_dir . "$table" . search_catalog::$extension;
    }

    $file = fopen($filename, "r") or die("Unable to open file.");
    $data = fread($file, filesize($filename));
    fclose($file);
    echo $data;
} else {
    exit();
}
