<?php
use PHPunit\Framework\TestCase;

class address_test extends TestCase
{
    /**
     * The following functions test the address::address_to_string function in the following cases:
     * - a non existent address id is provided
     * - a correct address id is provided
     * - an address with no "line two" is provided
     */

    function test_wrong_address_id(){
        $expected = false;
        $result = address::address_to_string(11);
        $this->assertEquals($expected, $result);
    }

    function test_correct_address_id(){
        $expected = "001 Street Name, APT#, City 001 BC A1A1A1";
        $result = address::address_to_string(1);
        $this->assertEquals($expected, $result);
    }

    function test_no_line_two_address(){
        $expected = "456 1st Street, Gotham ON A5B7T5";
        $result = address::address_to_string(9);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test add address (at least 3 cases)
     */

     function test_add_address_wrong_email() {

     }

     function test_add_address_correct_email() {

     }

     function test_add_address_with_insert() {

     }

     function test_add_address_no_insert() {

     }
     
    /**
     * Test get shipping address
     */

     function test_get_shipping_address_wrong_email() {

     }

     function test_get_shipping_address_right_email() {

     }

     function test_get_shipping_address_correct_address() {

     }

     function test_get_shipping_address_wrong_address() {

     }

    /**
     * Test get billing address
     */

     function test_get_billing_address_wrong_email() {

     }

     function test_get_billing_address_right_email() {

     }

     function test_get_billing_address_correct_address() {

     }

     function test_get_billing_address_wrong_address() {

     }

    /**
     * Test get shipping address id
     */

     function test_get_shipping_address_id_correct_id() {

     }

     function test_get_shipping_address_id_wrong_id() {
         
     }

}

?>