<?php

use PHPunit\Framework\TestCase;

class order_test extends TestCase
{
    /**
     * The following function tests the basic getters of the order class when a bad order is given
     */

    function test_wrong_order_id()
    {
        $order = new order(null);

        //Assert that no order_id is assigned for a bad order
        $result = $order->get_order_id();
        $this->assertEquals(false, isset($result));

        //Assert that no user email is assigned for a bad order
        $result = $order->get_user_email();
        $this->assertEquals(false, isset($result));

        //Assert that no shipping address is assigned for a bad order
        $result = $order->get_shipping_address();
        $this->assertEquals(false, $result);

        //Assert that no billing address is assigned for a bad order
        $result = $order->get_billing_address();
        $this->assertEquals(false, $result);

        //Assert that get_order_status_form returns false for a bad order
        $result = $order->get_order_status_form();
        $this->assertEquals(false, $result);

        //Assert that no order_date is assigned for a bad order
        $result = $order->get_order_date();
        $this->assertEquals(false, isset($result));

        //Assert that no completion_date is assigned for a bad order
        $result = $order->get_completion_date();
        $this->assertEquals(false, isset($result));

        //Assert that no ship date is assigned for a bad order
        $result = $order->get_ship_date();
        $this->assertEquals(false, isset($result));

        //Assert that get_order_detail_link returns false for a bad order
        $result = $order->get_order_detail_link();
        $this->assertEquals(false, $result);
    }

    /**
     * The following test ensures the proper results are returned for the basic getters when a correct order is input
     */

    function test_correct_order_id()
    {
        $order = new order(1);

        //Assert correct order_id is assigned for a real order
        $expected = 1;
        $result = $order->get_order_id();
        $this->assertEquals($expected, $result);

        //Assert correct user_email is assigned for a real order
        $expected = "user1@email.com";
        $result = $order->get_user_email();
        $this->assertEquals($expected, $result);

        //Assert correct order_date is assigned for a real order
        $expected = "2019-07-17 11:58:25";
        $result = $order->get_order_date();
        $this->assertEquals($expected, $result);

        //Assert correct completion_date is assigned for a real order
        $expected = null;
        $result = $order->get_completion_date();
        $this->assertEquals($expected, $result);

        //Assert correct ship_date is assigned for a real order
        $expected = null;
        $result = $order->get_ship_date();
        $this->assertEquals($expected, $result);
    }


    /**
     * The following tests ensure that the correct order_status is returned for all cases
     */

    function test_order_status_uncon()
    {
        $order = new order(1);
        $includes = "Unconfirmed";
        $result = $order->get_order_status();
        $this->assertStringContainsString($includes, $result);
    }

    function test_order_status_con()
    {
        $order = new order(2);
        $includes = "Confirmed";
        $result = $order->get_order_status();
        $this->assertStringContainsString($includes, $result);
    }

    function test_order_status_comp()
    {
        $order = new order(3);
        $includes = "Completed";
        $result = $order->get_order_status();
        $this->assertStringContainsString($includes, $result);
    }

    function test_order_status_ship()
    {
        $order = new order(4);
        $includes_1 = "Shipped";
        $includes_2 = "Picked Up";
        $result = $order->get_order_status();
        $this->assertStringContainsString($includes_1, $result);
        $this->assertStringContainsString($includes_2, $result);
    }

    function test_order_status_null()
    {
        $order = new order(null);
        $includes = "Unknown";
        $result = $order->get_order_status();
        $this->assertStringContainsString($includes, $result);
    }


    /**
     * The following test ensures the proper form structure is output for get_order_status_form
     */

    function test_get_order_status_form_structure()
    {
        $order = new order(1);
        $expected = new DOMDocument();
        $expected->loadXML(
            "<form method='' onchange=''><select name=''>"
                . "<option style='' value='' selected='true'><b style=''></b></option>"
                . "<option style='' value=''><b style=''></b></option>"
                . "<option style='' value=''><b style=''></b></option>"
                . "<option style='' value=''><b style=''></b></option>"
                . "</select></form>"
        );
        $form = $order->get_order_status_form();
        $result = new DOMDocument();
        $result->loadXML($form);
        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $result->firstChild
        );
    }

    /**
     * The following test ensures the proper link is returned for the get_order_detail_link function
     */

    function test_get_order_detail_link()
    {
        $order = new order(3);
        $includes_1 = "order_detail.php";
        $includes_2 = "order_id=3";
        $result = $order->get_order_detail_link();
        $this->assertStringContainsString($includes_1, $result);
        $this->assertStringContainsString($includes_2, $result);
    }

    /**
     * The following tests ensure the proper structure and content is contained in the get_table_entry function
     */

    function test_get_table_entry_normal()
    {
        $order = new order(1);
        $columns = array(
            "order_id",
            "user_email",
            "shipping_address",
            "billing_address",
            "order_status",
            "order_date",
            "completion_date",
            "ship_date",
            "order_detail",
            null
        );
        $expected = new DOMDocument();
        $expected->loadXML(
            "<tr>"
                . "<td></td>"    // order_id
                . "<td></td>"    // user_email
                . "<td></td>"    // shipping_address
                . "<td></td>"    // billing_address
                . "<td><b style=''></b></td>"    // order_status
                . "<td></td>"    // order_date
                . "<td></td>"    // completion_date
                . "<td></td>"    // ship_date
                . "<td><a href=''></a></td>"    // order_detail
                // nothing for null
                . "</tr>"
        );
        $table_entry = $order->get_table_entry($columns);
        $result = new DOMDocument();
        $result->loadXML($table_entry);
        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $result->firstChild
        );
    }

    function test_get_table_entry_vertical()
    {
        $order = new order(2);
        $columns = array(
            "order_id",
            "user_email",
            "shipping_address",
            "billing_address",
            "order_status",
            "order_date",
            "completion_date",
            "ship_date",
            "order_detail",
            null
        );
        $includes = array(
            "order id",
            "user email",
            "shipping address",
            "billing address",
            "order status",
            "order date",
            "completion date",
            "ship",
            "order detail",
        );
        $expected = new DOMDocument();
        $expected_format = ("<tbody><tr><th></th><td></td></tr>"       // order_id
            . "<tr><th></th><td></td></tr>"                     // user_email
            . "<tr><th></th><td></td></tr>"                     // shipping_address
            . "<tr><th></th><td></td></tr>"                     // billing_address
            . "<tr><th></th><td><b style=''></b></td></tr>"     // order_status
            . "<tr><th></th><td></td></tr>"                     // order_date
            . "<tr><th></th><td></td></tr>"                     // completion_date
            . "<tr><th></th><td></td></tr>"                     // ship_date
            . "<tr><th></th><td><a href=''></a></td></tr>"      // order_detail
            . "<tr></tr></tbody>");
        $expected->loadXML($expected_format);
        $table_entry = $order->get_table_entry($columns, true, false);
        $result = new DOMDocument();
        $result->loadXML("<tbody>$table_entry</tbody>");
        $this->assertEqualXMLStructure(
            $expected->firstChild,
            $result->firstChild
        );
        foreach ($includes as $string) {
            $this->assertStringContainsStringIgnoringCase($string, $table_entry);
        }
    }

    /**
     * TODO: Tests for setters
     */
}
