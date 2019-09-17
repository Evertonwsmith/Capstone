<?php

require_once 'tsc_node.php';

class tsc
{

    /** TRIE SEARCH CATALOG FORMAT:
     * **************************
     * illegal chars: [< > = ' , :]
     * 
     * EXAMPLE DOCUMENT:
     * **************************
     * <
     *  <ap
     *      <p='1,5:i'
     *          <lication='3,4:'>
     *      >
     *      <ple='2:app'>
     *  >
     *  <i='3,5:apple'>
     * >
     * **************************
     * CONTAINS:
     * app='1,5:i' application='3,4:' apple='2:app' i='3,5:apple'
     */

    private $root;
    private static $replace_chars = ["\r", "\n", "\r\n", "\t", "<", ">", "=", "'", ",", ":", ";", "`", "~", "!", "#", "$", "%", "^", "*", "(", ")", "_", "+", "?", ".", "|", "{", "}", "[", "]", "/", "\\"];

    public function __construct($table, $allow_email = false)
    {
        $this->root = new tsc_node(null); // create root node
        switch ($table) {
            case "useraccount":
                // Join useraccount and address
                $rows = my_sql::select("*", "useraccount LEFT JOIN address ON (useraccount.shippingAddressID=address.AddressID OR useraccount.billingAddressID=address.AddressID)", "useraccount.isActive='1'");
                $allow_email = true;
                break;
            case "orders":
                //join orders, useraccount, and address
                $rows = my_sql::select(
                    "*",
                    "(orders 
                    LEFT JOIN useraccount ON (orders.userEmail=useraccount.userEmail)) 
                    LEFT JOIN address ON (orders.shippingAddress=address.AddressID OR orders.billingAddress=address.AddressID)"
                );
                $allow_email = true;
                break;
            default:
                $rows = my_sql::select("*", $table);
                break;
        }
        $pks = my_sql::$primary_keys[$table];

        if ($rows != false) {
            foreach ($rows as $row) {
                //Get master key for result
                $key = "";
                foreach ($pks as $k) {
                    $key .= $row[$k] . "_";
                }
                $key = rtrim($key, "_");
                foreach ($row as $attr => $value) {
                    if ($allow_email || $attr !== "userEmail") {
                        //strip html tags from string
                        $value = strip_tags($value);
                        //make string lowercase
                        $value = strtolower($value);
                        //remove all bad characters from string
                        foreach (tsc::$replace_chars as $bad_char) {
                            $value = str_replace($bad_char, "", $value);
                        }
                        $words = explode(" ", $value);
                        for ($i = 0; $i < count($words); $i++) {
                            $word = $words[$i];
                            //Get next letter of following word to help with tsc client-side suggestions
                            $next_word = "";
                            $next_index = $i;
                            while ($next_index < count($words) - 1) {
                                //Look for next actual word:
                                $next_word = $words[$next_index + 1];
                                if (strlen($next_word) > 0) {
                                    break;
                                }
                                $next_index++;
                            }
                            if (strlen($word) > 64) $word = substr($word, 0, 64); //limit max word length to prevent maxing out recursion
                            if (strlen($word) > 0) ($this->root)->pass($word, $key, $next_word);
                        }
                    }
                }
            }
        }
    }

    public function to_string($tabs = false)
    {
        ($this->root)->compress();
        return ($this->root)->to_string(($tabs ? 0 : null));
    }
}
