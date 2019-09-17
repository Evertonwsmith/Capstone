<?php
use PHPunit\Framework\TestCase;

final class is_valid_test extends TestCase
{
    /**
     * The following functions test the is_valid::email function with the following cases:
     * - incorrect email format is input
     * - correct email format but already exists in the database
     * - emtpy email address input
     * - correct email format and does not exist in the database
     */

    function test_incorrect_email_format(){
        $emails = Array("this is not an email", "thishastoo@many@symbols.com", "thishasa space@email.com", "thishasnodomainending@email");
        $not_expected = true;
        foreach($emails as $email){
            $result = is_valid::email($email);
            $this->assertNotEquals($not_expected, $result);
        }
    }

    function test_correct_email_in_database_already(){
        $email = "user8@email.com";
        $not_expected = true;
        $result = is_valid::email($email);
        $this->assertNotEquals($not_expected, $result);
    }

    function test_empty_email(){
        $email = "";
        $not_expected = true;
        $result = is_valid::email($email);
        $this->assertNotEquals($not_expected, $result);
    }

    function test_correct_email(){
        $email = "user9@email.com";
        $expected = true;
        $result = is_valid::email($email);
        $this->assertEquals($expected, $result);
    }

    /**
     * The following functions test the is_valid::password function with the following cases:
     * - password does not contain enough characters
     * - password does not contain enough numerical characters
     * - password does not contain enough symbols
     * - password is empty
     * - password is valid
     */

    function test_password_not_enough_characters(){
        $passwords = Array("short3!", "shortPW", "pw");
        $not_expected = true;
        foreach($passwords as $password){
            $result = is_valid::password($password);
            $this->assertNotEquals($not_expected, $result);
        }
    }

    function test_password_not_enough_numbers(){
        $passwords = Array("thispasswordhasnonumbers", "thispasswordhasasymbol!");
        $not_expected = true;
        foreach($passwords as $password){
            $result = is_valid::password($password);
            $this->assertNotEquals($not_expected, $result);
        }
    }

    function test_password_not_enough_symbols(){
        $passwords = Array("thispasswordhasnosymbols1");
        $not_expected = true;
        foreach($passwords as $password){
            $result = is_valid::password($password);
            $this->assertNotEquals($not_expected, $result);
        }
    }

    function test_password_empty(){
        $password = "";
        $not_expected = true;
        $result = is_valid::password($password);
        $this->assertNotEquals($not_expected, $result);
    }

    function test_password_valid(){
        $passwords = Array("thisIsAValidPassword1!", "thisIsAValidPassword9%");
        $expected = true;
        foreach($passwords as $password){
            $result = is_valid::password($password);
            $this->assertEquals($expected, $result);
        }
    }
}

?>