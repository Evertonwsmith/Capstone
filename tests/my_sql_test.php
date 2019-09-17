<?php

use PHPunit\Framework\TestCase;

final class my_sql_test extends TestCase
{
    /**
     * test the my_sql select function by checking:
     * - expected table case
     * - empty case ('where' result does not exist)
     * - multiple result case
     * - order by functionality
     * - group by functionality
     * - limit functionality
     * - incorrect syntax
     */

    function test_select_expected_table()
    {
        $expected = [
            ["userEmail" => "user1@email.com", "profileImageID" => 1, "firstName" => "User", "lastName" => "One", "shippingAddressID" => 1, "billingAddressID" => 1],
            ["userEmail" => "user2@email.com", "profileImageID" => 2, "firstName" => "User", "lastName" => "Two", "shippingAddressID" => 2, "billingAddressID" => 2],
            ["userEmail" => "user3@email.com", "profileImageID" => 3, "firstName" => "User", "lastName" => "Three", "shippingAddressID" => 3, "billingAddressID" => 3],
            ["userEmail" => "user4@email.com", "profileImageID" => 4, "firstName" => "User", "lastName" => "Four", "shippingAddressID" => 4, "billingAddressID" => 4],
            ["userEmail" => "user5@email.com", "profileImageID" => 5, "firstName" => "User", "lastName" => "Five", "shippingAddressID" => 5, "billingAddressID" => 5],
            ["userEmail" => "user6@email.com", "profileImageID" => 6, "firstName" => "User", "lastName" => "Six", "shippingAddressID" => 6, "billingAddressID" => 6],
            ["userEmail" => "user7@email.com", "profileImageID" => NULL, "firstName" => "User", "lastName" => "Seven", "shippingAddressID" => 7, "billingAddressID" => 7],
            ["userEmail" => "user8@email.com", "profileImageID" => NULL, "firstName" => "User", "lastName" => "Eight", "shippingAddressID" => 8, "billingAddressID" => 8]
        ];
        $result = my_sql::select("*", "useraccount");
        $this->assertEquals($expected, $result);
    }

    function test_select_empty_result()
    {
        $expected = 0;
        $result = count(my_sql::select("*", "useraccount", "userEmail = :0", array("thisEmailDoesNotExist@email.com")));
        $this->assertEquals($expected, $result);
    }

    function test_select_multiple_result()
    {
        $expected = 2;
        $result = count(my_sql::select("*", "useraccount", "userEmail = :0 OR userEmail = :1", array("user1@email.com", "user2@email.com")));
        $this->assertEquals($expected, $result);
    }

    function test_select_order_by_asc()
    {
        $expected_asc = [
            ["userEmail" => "user1@email.com", "profileImageID" => 1, "firstName" => "User", "lastName" => "One", "shippingAddressID" => 1, "billingAddressID" => 1],
            ["userEmail" => "user2@email.com", "profileImageID" => 2, "firstName" => "User", "lastName" => "Two", "shippingAddressID" => 2, "billingAddressID" => 2],
            ["userEmail" => "user3@email.com", "profileImageID" => 3, "firstName" => "User", "lastName" => "Three", "shippingAddressID" => 3, "billingAddressID" => 3],
            ["userEmail" => "user4@email.com", "profileImageID" => 4, "firstName" => "User", "lastName" => "Four", "shippingAddressID" => 4, "billingAddressID" => 4],
            ["userEmail" => "user5@email.com", "profileImageID" => 5, "firstName" => "User", "lastName" => "Five", "shippingAddressID" => 5, "billingAddressID" => 5],
            ["userEmail" => "user6@email.com", "profileImageID" => 6, "firstName" => "User", "lastName" => "Six", "shippingAddressID" => 6, "billingAddressID" => 6],
            ["userEmail" => "user7@email.com", "profileImageID" => NULL, "firstName" => "User", "lastName" => "Seven", "shippingAddressID" => 7, "billingAddressID" => 7],
            ["userEmail" => "user8@email.com", "profileImageID" => NULL, "firstName" => "User", "lastName" => "Eight", "shippingAddressID" => 8, "billingAddressID" => 8]
        ];
        $result = my_sql::select("*", "useraccount", null, null, "userEmail ASC");
        $this->assertEquals($expected_asc, $result);
    }

    function test_select_order_by_desc()
    {
        $expected_desc = [
            ["userEmail" => "user8@email.com", "profileImageID" => NULL, "firstName" => "User", "lastName" => "Eight", "shippingAddressID" => 8, "billingAddressID" => 8],
            ["userEmail" => "user7@email.com", "profileImageID" => NULL, "firstName" => "User", "lastName" => "Seven", "shippingAddressID" => 7, "billingAddressID" => 7],
            ["userEmail" => "user6@email.com", "profileImageID" => 6, "firstName" => "User", "lastName" => "Six", "shippingAddressID" => 6, "billingAddressID" => 6],
            ["userEmail" => "user5@email.com", "profileImageID" => 5, "firstName" => "User", "lastName" => "Five", "shippingAddressID" => 5, "billingAddressID" => 5],
            ["userEmail" => "user4@email.com", "profileImageID" => 4, "firstName" => "User", "lastName" => "Four", "shippingAddressID" => 4, "billingAddressID" => 4],
            ["userEmail" => "user3@email.com", "profileImageID" => 3, "firstName" => "User", "lastName" => "Three", "shippingAddressID" => 3, "billingAddressID" => 3],
            ["userEmail" => "user2@email.com", "profileImageID" => 2, "firstName" => "User", "lastName" => "Two", "shippingAddressID" => 2, "billingAddressID" => 2],
            ["userEmail" => "user1@email.com", "profileImageID" => 1, "firstName" => "User", "lastName" => "One", "shippingAddressID" => 1, "billingAddressID" => 1]
        ];
        $result = my_sql::select("*", "useraccount", null, null, "userEmail DESC");
        $this->assertEquals($expected_desc, $result);
    }

    function test_select_group_by()
    {
        $expected = [
            ["COUNT(AddressID)" => 4, "province" => "AB"],
            ["COUNT(AddressID)" => 4, "province" => "BC"],
            ["COUNT(AddressID)" => 1, "province" => "ON"]
        ];
        $result = my_sql::select("COUNT(AddressID), province", "address", null, null, "province", "province");
        $this->assertEquals($expected, $result);
    }

    function test_select_limit()
    {
        $expected = 8;
        $result = count(my_sql::select("*", "useraccount"));
        $this->assertEquals($expected, $result);
    }

    function test_select_incorrect_syntax()
    {
        $expected = false;
        $result = my_sql::select("*", "jibberish(*^%$#&");
        $this->assertEquals($expected, $result);
    }

    /**
     * test the my_sql insert function with the following cases:
     * - normal insert
     * - duplicate insert
     * - value array size mismatch
     * - incorrect syntax
     */

    function test_insert_normal()
    {
        //assert insert did not return false
        $result = my_sql::insert("useraccount", array("userEmail", "lastName", "firstName"), array("bob@email.com", "Shlong", "Bob"));
        $this->assertNotEquals(false, $result);

        //assert validity of insert data
        $expected = [
            ["userEmail" => "bob@email.com", "profileImageID" => null, "firstName" => "Bob", "lastName" => "Shlong", "shippingAddressID" => null, "billingAddressID" => null]
        ];
        $values = my_sql::select("*", "useraccount", "userEmail=:0", array("bob@email.com"));
        $this->assertEquals($expected, $values);
    }

    function test_insert_duplicate()
    {
        //assert insert did not return false
        $result = my_sql::insert("useraccount", array("userEmail", "lastName", "firstName"), array("greg@email.com", "Evans", "Greg"));
        $this->assertNotEquals(false, $result);

        //assert duplicate insert does not run correctly
        $result = my_sql::insert("useraccount", array("userEmail", "lastName", "firstName"), array("greg@email.com", "Evans", "Greg"));
        $this->assertEquals(false, $result);

        //assert validity of original insert data
        $expected = [
            ["userEmail" => "greg@email.com", "profileImageID" => null, "firstName" => "Greg", "lastName" => "Evans", "shippingAddressID" => null, "billingAddressID" => null]
        ];
        $values = my_sql::select("*", "useraccount", "userEmail=:0", array("greg@email.com"));
        $this->assertEquals($expected, $values);
    }

    function test_insert_array_mismatch()
    {
        //assert insert returns false with array size mismatch
        $result = my_sql::insert("useraccount", array("userEmail", "lastName", "firstName"), array("Rogers", "Steven"));
        $this->assertEquals(false, $result);
    }

    function test_insert_incorrect_syntax()
    {
        //assert insert returns false with incorrect syntax
        $result = my_sql::insert("useraccount withjibberish&*&^# WHERE", array("userEmail", "lastName", "firstName"), array("steven@email.com", "Rogers", "Steven"));
        $this->assertEquals(false, $result);
    }

    /**
     * test the my_sql update function with the following cases:
     * - normal update
     * - update primary key value
     * - value array size mismatch
     * - incorrect syntax
     */

    function test_update_normal()
    {
        //assert update did not return false
        $result = my_sql::update("useraccount", array("lastName", "firstName"), array("Shlongers", "Bobby"), "userEmail=:0", array("bob@email.com"));
        $this->assertNotEquals(false, $result);

        //assert validity of update data
        $expected = [
            ["userEmail" => "bob@email.com", "profileImageID" => null, "firstName" => "Bobby", "lastName" => "Shlongers", "shippingAddressID" => null, "billingAddressID" => null]
        ];
        $values = my_sql::select("*", "useraccount", "userEmail=:0", array("bob@email.com"));
        $this->assertEquals($expected, $values);
    }

    function test_update_duplicate_primary_key_value()
    {
        //assert update on duplicate primary key returns false
        $result = my_sql::update("useraccount", array("userEmail"), array("user1@email.com"), "userEmail=:0", array("bob@email.com"));
        $this->assertEquals(false, $result);
    }

    function test_update_array_mismatch()
    {
        //assert update returns false with array size mismatch
        $result = my_sql::update("useraccount", array("userEmail", "lastName", "firstName"), array("Rogers", "Steven"), "userEmail=:0", array("steven@email.com"));
        $this->assertEquals(false, $result);
    }

    function test_update_incorrect_syntax()
    {
        //assert update returns false with incorrect syntax
        $result = my_sql::update("useraccount withjibberish&*&^# WHERE", array("userEmail", "lastName", "firstName"), array("steven@email.com", "Rogers", "Steven"), "userEmail=:0", array("steven@email.com"));
        $this->assertEquals(false, $result);
    }

    /**
     * test the my_sql delete function with the following cases:
     * - normal delete (from previous insert)
     * - multiple deletions
     * - duplicate deletion
     * - delete with improper syntax
     */

    function test_delete_normal()
    {
        //assert delete does not return false
        $result = my_sql::delete("useraccount", "userEmail = :0", array("bob@email.com"));
        $this->assertNotEquals(false, $result);

        //assert validity of deletion
        $values = my_sql::select("*", "useraccount", "userEmail=:0", array("bob@email.com"));
        $this->assertEquals(count($values), 0);
    }

    function test_delete_multiple()
    {
        //assert delete does not return false
        $result = my_sql::delete("useraccount", "userEmail = :0 OR userEmail = :1", array("user5@email.com", "user6@email.com"));
        $this->assertNotEquals(false, $result);

        //assert validity of deletions
        $values = my_sql::select("*", "useraccount", "userEmail = :0 OR userEmail = :1", array("user5@email.com", "user6@email.com"));
        $this->assertEquals(count($values), 0);
    }

    function test_delete_duplicate()
    {
        //assert initial delete does not return false
        $result = my_sql::delete("useraccount", "userEmail = :0", array("user3@email.com"));
        $this->assertNotEquals(false, $result);

        //assert duplicate delete does not return false since the entity is deleted
        $result = my_sql::delete("useraccount", "userEmail = :0", array("user3@email.com"));
        $this->assertNotEquals(false, $result);

        //assert validity of initial deletion
        $values = my_sql::select("*", "useraccount", "userEmail = :0", array("user3@email.com"));
        $this->assertEquals(count($values), 0);
    }

    function test_delete_improper_syntax()
    {
        //assert delete with improper syntax returns false
        $result = my_sql::delete("useraccount", "userEmail blahs the improper*(&#) :0", array("user3@email.com"));
        $this->assertEquals(false, $result);
    }
}
