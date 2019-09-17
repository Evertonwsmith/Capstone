<?php
require_once "../src/user.php";
//Run function if login form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST['login_start'])) {
    $active = my_sql::exists("useraccount", "userEmail=:0 AND isActive='1'", array($_POST['user_email']));
    if (!user::login($_POST['user_email'], $_POST['password']) || $active == 0) {
        echo "<script>window.onload=function(){alert('Invalid Login Information.');};</script>";
    } else {
        header("location:profile_page.php");
    }
}
//If noone logged in, show login button
if (!isset($_SESSION['user_email'])) {
    echo '<a id="login_button" class="nav-link" onclick="document.getElementById(\'login_box\').style.display=\'inline\';document.getElementById(\'login_button\').style.display=\'none\';document.getElementById(\'error\').style.display=\'none\';">Login </a>';
} else {
    //If user logged in, show user_email that redirects to profile page
    $user_email = $_SESSION['user_email'];
    $user = new user($user_email);
    $fname = $user->get_first_name();
    $lname = $user->get_last_name();
    echo ('<li class="nav-item">'
        . '<a class="nav-link header" href="profile_page.php">' . $fname . " " . $lname . '<span class="sr-only">(current)</span></a>'
        . '</li>'
        . '<li class="nav-item header">'
        . '<a class="nav-link" href="cart.php">Cart<span class="sr-only">(current)</span></a> '
        . '</li>'
        . '<li class="nav-item header">'
        . '<a class="nav-link" href="logout.php">Logout<span class="sr-only">(current)</span></a> '
        . '</li>');
}

?>

<div id="login_box" style="display:none;">
    <!--Close login form box-->
    <a class="nav-link" title="Close Modal" onclick="document.getElementById('login_box').style.display='none';document.getElementById('login_button').style.display='block'">Close</a>
    <div style="right:0" class="dropdown">
        <form action="" method="post">
            <div>
                <div class="row"><a class="nav-link close-button" title="Close Modal" onclick="document.getElementById('login_box').style.display='none';document.getElementById('login_button').style.display='block'"></a></div><br><br>
                <label class="label-left" for="user_email"><b>Email</b></label>
                <input type="text" placeholder="Enter Email Address" name="user_email" required>
                <br>
                <label class="label-left" for="password"><b>Password</b></label>
                <input type="password" placeholder="Enter Password" name="password" required>
                <br>
                <label for="login_start"></label>
                <button type="submit" name="login_start">Submit</button>
            </div>
        </form>
        <a class="nav-link" href="forgot_password.php">Forgot password?</a>

        <a class="nav-link" href="register.php">Create account</a>
    </div>
</div>