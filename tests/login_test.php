<?php

use PHPunit\Framework\TestCase;

final class login_test extends TestCase
{
    function test_login()
    {
        //Login user user7@email.com pwd: user7@email.com
        $test = user::login("user7@email.com", "user7@email.com");
        $this->assertEquals($test, true);
    }

    function test_incorrect_password()
    {
        //Login user user7@email.com pwd: 2
        $test = user::login("user7@email.com", "safdshjkl@#sadfj");
        $this->assertEquals($test, false);
    }
    function test_incorrect_pass_case_sensitivity()
    {
        //Login user user7@email.com pwd: user7@eMaIl.COm
        $test = user::login("user7@email.com", "user7@eMaIl.COm");
        $this->assertEquals($test, false);
    }
    function test_incorrect_email_case_sensitivity()
    {
        //Login user user7@email.com pwd: user7@eMaIl.COm
        $test = user::login("user7@eMaIl.COm", "user7@email.com");
        $this->assertEquals($test, true);
    }
    function test_null_pass()
    {
        //Login user user7@email.com null password
        $test = user::login("user7@email.com", null);
        $this->assertEquals($test, false);
    }
    function test_null_email()
    {
        //Login user user7@email.com null username
        $test = user::login(null, "user7@email.com");
        $this->assertEquals($test, false);
    }
    function test_null_all()
    {
        //Login user user7@email.com null username and password
        $test = user::login(null,null);
        $this->assertEquals($test, false);
    }
}
