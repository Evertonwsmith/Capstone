<?php

use PHPMailer\PHPMailer\Exception;

require_once 'tsc.php';

class search_catalog
{
    private static $catalogs = [
        "product",
        "blogpost",
        "eventpost",
        "artistaccount",
        "venueaccount"
    ];

    private static $protected_catalogs = [
        "useraccount",
        "orders"
    ];

    public static $update_frequency = 60; //will not update catalog if it has been updated in this amount of seconds
    public static $root_dir = "../catalog/"; //where to store the catalog files on the server
    public static $extension = ".tsc"; //stands for Trie Search Catalog

    public static function get_catalog_list($protected = false)
    {
        return $protected ? search_catalog::$protected_catalogs : search_catalog::$catalogs;
    }

    public static function update_catalog($table)
    {
        try {
            if (in_array($table, search_catalog::$catalogs)) {
                $filename = search_catalog::$root_dir . $table . search_catalog::$extension;

                //Check if file needs to be updated
                if (file_exists($filename)) {
                    $file_last_edit = filemtime($filename);
                    $time_diff = ($file_last_edit != false ? time() - $file_last_edit : time());
                } else {
                    $time_diff = search_catalog::$update_frequency + 1;
                }

                //Update file
                if ($time_diff > search_catalog::$update_frequency) {
                    $tsc = new tsc($table);
                    $file = fopen($filename, "w");
                    fwrite($file, $tsc->to_string(true));
                    fclose($file);
                }
            } else if (in_array($table, search_catalog::$protected_catalogs)) {
                /**
                 * YOU HAVE ENTERED THE DANGER ZONE OF FILE DELETION.
                 * PLEASE THINK BEFORE YOU CHANGE ANYTHING HERE
                 */

                //Access file dir
                $results = my_sql::select("pass", "folderpass", "name=:0", array("$table"));
                if ($results != false && count($results) == 1) {
                    $pass = $results[0]['pass'];
                    $dir = search_catalog::$root_dir . "$pass";
                    $filename = "$dir/$table" . search_catalog::$extension;
                } else {
                    $pass = search_catalog::generate_pass(search_catalog::$root_dir);
                    $dir = search_catalog::$root_dir . "$pass";
                    $filename = "$dir/$table" . search_catalog::$extension;
                    my_sql::insert("folderpass", array("name", "pass"), array($table, $pass));
                }

                //Check if file needs to be updated
                if (file_exists($filename)) {
                    $file_last_edit = filemtime($filename);
                    $time_diff = ($file_last_edit != false ? time() - $file_last_edit : time());
                } else {
                    $time_diff = search_catalog::$update_frequency + 1;
                }

                //update file
                if ($time_diff > search_catalog::$update_frequency) {
                    //generate new folderpass
                    $new_pass = search_catalog::generate_pass(search_catalog::$root_dir);

                    //add new pass to database
                    my_sql::update("folderpass", array("pass"), array($new_pass), "name=:0", array($table));

                    //Create new tsc
                    $tsc = new tsc($table);

                    //Create new dir
                    $new_dir = search_catalog::$root_dir . $new_pass;
                    mkdir($new_dir, 0777, true);

                    $new_filename = "$new_dir/$table" . search_catalog::$extension;

                    //Create file
                    $file = fopen($new_filename, "w");
                    fwrite($file, $tsc->to_string(true));
                    fclose($file);

                    if (file_exists($filename)) {
                        //remove old file and folder
                        unlink($filename);
                        rmdir($dir);
                    }
                }

                /**
                 * END OF DANGER ZONE
                 */
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public static function generate_pass($dir = "")
    {
        //make sure we don't get a duplicate string (very rare)
        do {
            $pass = sha1(time());
        } while (file_exists($dir . $pass));

        return sha1(time()); //40 character random hexadecimal
    }

    public static function get_search_keys()
    {
        if (isset($_POST['search_key'])) {
            $ids =  $_POST['search_key'];
            //Un-stringify search keys
            $ids = str_replace("[", "", $ids);
            $ids = str_replace("]", "", $ids);
            $ids = str_replace("\"", "", $ids);
            $ids = explode(",", $ids);
            return $ids;
        } else {
            return null;
        }
    }

    public static function filter_sort_results($search_keys, $results, $search_order)
    {
        if (isset($search_keys) && is_array($search_keys)) {
            // Arrange results so index is resultID
            $flipped_results = array_flip($results);
            //set up empty array to store only results in in search result
            $sorted_results = array();
            // Get search results and cross reference with sorted results
            foreach ($search_keys as $key) {
                //check to see if key exists in all results
                if (isset($flipped_results[$key])) {
                    if ($search_order) {
                        //add by search priority if sorting by search results
                        array_push($sorted_results, $key);
                    } else {
                        //add by sort priority if not sorting by search results
                        $sorted_results[$flipped_results[$key]] = $key;
                    }
                }
            }
            if (!$search_order) {
                //Sort by key value
                ksort($sorted_results);
            }
            return $sorted_results;
        }
        return false;
    }

    public static function get_catalog($table)
    {
        if (in_array($table, search_catalog::$catalogs) || in_array($table, search_catalog::$protected_catalogs)) {
            $html = "";
            $html .= search_catalog::get_search_bar();
            $html .= "<script type='text/javascript' src='../src/javascripts/tsc.js'></script>";
            $html .= "<link rel='stylesheet' type='text/css' href='../src/stylesheets/tsc.css' />";
            $html .= "<div id='catalog_load_trigger'></div>";
            $html .= ("<script type='text/javascript'>
                var tsc;
                var xhttp;
                function load_catalog(table){
                    console.log('loading catalog: ' + table);
                    xhttp = new XMLHttpRequest;
                    xhttp.onreadystatechange = function () {
                        let search_bar = document.getElementById('search_bar');
                        if (this.readyState == 4 && this.status == 200) {
                            //console.log(this.responseText);
                            tsc = new trie_search_catalog(search_bar);
                            tsc.build(this.responseText);
                            search_bar.placeholder = 'Search " . search_catalog::get_catagory_name($table) . "...';
                            search_bar.disabled = false;
                            console.log('catalog loaded successfully');
                            $('#catalog_load_trigger').click();
                        } else {
                            search_bar.placeholder = 'Unable to search...';
                            search_bar.disabled = true;
                        }
                    }
                    xhttp.open('GET', '../src/retriever.php?table=$table', true);
                    xhttp.send();
                }
                load_catalog('$table'); //load the table
                </script>");
            return $html;
        } else {
            return null;
        }
    }

    public static function get_search_bar()
    {
        echo ("
        <div class='p-2 flex-grow-1'>
            <div class='text_input_container'>
                <input type='text' id='search_bar' name='search' placeholder='Search Sleepovers...' />
                <div class='text_suggestion_container'></div>
            </div>
        </div>
        <div class='p-2'>
            <div id='search_bar_form'>
                <button id='search_submit' onclick='if(tsc.search_bar.value === undefined || tsc.search_bar.value.length == 0){clear_search();search();}else{tsc.search();}'>Search</button>
                <div id='search_results'></div>
            </div>
        </div>
        ");
    }

    public static function get_catagory_name($table)
    {
        switch ($table) {
            case "useraccount":
                return "Users";
            case "product":
                return "Products";
            case "venueaccount":
                return "Venues";
            case "artistaccount":
                return "Artists";
            case "blogpost":
                return "Blogposts";
            case "eventpost":
                return "Events";
            case "orders":
                return "Orders";
            default:
                return "Sleepovers";
        }
    }
}
