<?php

class url
{
    private static $web_url = "http://cosc499.ok.ubc.ca/27307164/group-1-sleepovers-web-portal-cosc499-team1-sleepovers";

    public static function get_website_url()
    {
        return url::$web_url;
    }
}
