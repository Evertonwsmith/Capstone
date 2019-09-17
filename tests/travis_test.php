<?php
use PHPunit\Framework\TestCase;

class travis_test extends TestCase
{
    function test_travis_equals(){
        $this->assertEquals(1, 1);
    }

    function test_travis_not_equals(){
        $this->assertNotEquals(0, 1);
    }
}

?>