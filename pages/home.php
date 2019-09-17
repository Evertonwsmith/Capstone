<?php
$page_title = "Sleepovers";
include "header.php";

$blog_button = my_sql::select("blogID", "blogpost", null, null, "timestamp DESC");
?>

<!-- override body background for home -->
<style>
    body {
        background-image: none;
        background-color: #000;
    }

    .home-logo {
        width: 100%;
        height: 100%;
        padding: 2rem;
        object-fit: contain;
        z-index: 99;
    }

    .img-shadow-container {
        position: fixed;
        left: 50%;
        right: 50%;
        top: 50%;
        bottom: 50%;
    }

    .img-shadow {
        position: fixed;
        left: 20%;
        right: 20%;
        top: 20%;
        bottom: 20%;
        box-shadow: 0 0 7rem 0.4rem #FF0080;
        z-index: 98;
    }

    .img-shadow:before {
        content: "";
        position: fixed;
        left: 22%;
        right: 18%;
        top: 22%;
        bottom: 18%;
        box-shadow: 1rem 1rem 1rem 0.2rem #000;
        z-index: 97;
    }

    .img-shadow:after {
        content: "";
        position: fixed;
        left: 18%;
        right: 22%;
        top: 18%;
        bottom: 22%;
        box-shadow: -1rem -1rem 1rem 0.2rem #000;
        z-index: 97;
    }
</style>

<div class='img-shadow'>
    <img class='home-logo' src='../img/sleepovers_logo.svg'>
</div>

<?php
include "footer.php";
?>