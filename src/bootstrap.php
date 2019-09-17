<?php
function loader($class)
{
    $root_dir = '/home/travis/build/ubco-cosc499-summer2019/group-1-sleepovers-web-portal-cosc499-team1-sleepovers/src/';
    $file = $root_dir . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('loader');
?>