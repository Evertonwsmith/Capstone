<?php
$page_title = 'Register Account';
include 'header.php';
require_once '../src/is_valid.php';
?>

<div class="row justify-content-center" style="text-align-center">
    <div>
        <div class="row justify-content-center">
            <h2>Register</h2>
        </div>
        <hr>
        <br>

        <?php
        if (isset($_POST['email'], $_POST['password'], $_POST['password_confirm'], $_POST['first_name'], $_POST['last_name'])) {
            $email = $_POST['email'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $pass = $_POST['password'];
            $pass_2 = $_POST['password_confirm'];

            //Get newletter notification setting
            $newsletter_notif = isset($_POST['newsletter_notif']) ? 1 : 0;

            //Get blog notification setting
            $blog_notif = isset($_POST['blog_notif']) ? 1 : 0;

            //Get account type
            $account_type = isset($_POST['account_type']) ? $_POST['account_type'] : "user";

            //Get artist/venue public name
            $public_name = isset($_POST['public_name']) ? $_POST['public_name'] : null;

            $account_invalid = false;

            //Check valid email
            $email_errors = is_valid::email($email);
            if (isset($email_errors) && $email_errors !== true) {
                $account_invalid = true;
            }

            //Check valid password
            $pass_errors = is_valid::password($pass);
            if (isset($pass_errors) && $pass_errors !== true) {
                $account_invalid = true;
            }

            //Check passwords match
            if (!($pass == $pass_2)) {
                $account_invalid = true;
                $pass_2_errors = "<div class='error'>Passwords do not match.</div>";
            }

            //Check valid public name for account type
            switch ($account_type) {
                case "artist":
                    if (!isset($public_name)) {
                        $account_invalid = true;
                        $account_errors = "<div class='error'>Please enter an Artist/Musician Name. Public users will be able to find you with this name.</div>";
                    } else {
                        $exists = my_sql::select("artistName", "artistaccount", "artistName=:0", array("$public_name"));
                        if ($exists != false) {
                            $account_errors = "<div class='error'>An account with this Artist/Musician Name already exists. Please Enter a different name.</div>";
                            $account_invalid = true;
                        }
                    }
                    break;
                case "venue":
                    if (!isset($public_name)) {
                        $account_invalid = true;
                        $account_errors = "<div class='error'>Please enter a name for your venue. Public users will be able to find you with this name.</div>";
                    } else {
                        $exists = my_sql::select("venueName", "venueaccount", "venueName=:0", array("$public_name"));
                        if ($exists != false) {
                            $account_errors = "<div class='error'>An account with this Venue Name already exists. Please Enter a different name.</div>";
                            $account_invalid = true;
                        }
                    }
                    break;
            }

            if (isset($account_invalid) && !$account_invalid) {
                //Insert account into useraccounts
                if (my_sql::insert("useraccount", array("userEmail", "firstName", "lastName", "isOnMailList", "blogOptIn"), array("$email", "$first_name", "$last_name", "$newsletter_notif", "$blog_notif"))) {
                    //Insert into additional account tables if not useraccount
                    switch ($account_type) {
                        case "user":
                            break;
                        case "artist":
                            if (!my_sql::insert("artistaccount", array("artistName", "userEmail"), array("$public_name", "$email"))) {
                                //Something went wrong so abort
                                $account_invalid = true;
                            }
                            break;
                        case "venue":
                            if (!my_sql::insert("venueaccount", array("venueName", "userEmail"), array("$public_name", "$email"))) {
                                //Something went wrong so abort
                                $account_invalid = true;
                            }
                            break;
                    }

                    //Generate password hash
                    $hash = password_hash($pass, PASSWORD_ARGON2I);
                    if (!$account_invalid && my_sql::insert("password", array("userEmail", "hash"), array("$email", "$hash"))) {
                        //Display success message and log user in
                        $_SESSION['user_email'] = $email;
                        echo "<script>window.location.assign('register_success.php');</script>";
                    } else {
                        //Delete user and display message
                        my_sql::delete("useraccount", "userEmail=:0", array("$email"));
                        echo "<div class='error'>Something went wrong... Account Registration was unsuccessfull.</div>";
                    }
                }
            }
        }
        ?>

        <form action="register.php" method="post">
            <div class="d-flex flex-row"><label class="align-self-center">First Name</label><input type="text" name="first_name" class="ml-auto p-2" <?php echo isset($first_name) ? "value='$first_name'" : ""; ?> required /></div><br />
            <div class="d-flex flex-row"><label class="align-self-center">Last Name</label><input type="text" name="last_name" class="ml-auto p-2" <?php echo isset($last_name) ? "value='$last_name'" : ""; ?> required /></div><br />
            <div class="d-flex flex-row"><label class="align-self-center">User Email</label><input type="email" name="email" class="ml-auto p-2" <?php echo isset($email) ? "value='$email'" : ""; ?> required /></div>
            <?php
            if (isset($account_invalid)) {
                if (isset($email_errors) && $email_errors !== true) {
                    echo "<div class='error'>" . $email_errors . "</div>";
                }
            }
            ?>
            <br />
            <div class="d-flex flex-row"><label class="align-self-center">Password </label><input type="password" name="password" class="ml-auto p-2" required /></div>
            <?php
            if (isset($account_invalid)) {
                if (isset($pass_errors) && $pass_errors !== true) {
                    echo "<div class='error'>" . $pass_errors . "</div>";
                }
            }
            ?>
            <br />
            <div class="d-flex flex-row"><label class="align-self-center">Confirm Password </label><input type="password" name="password_confirm" class="ml-auto p-2" required /></div>
            <?php
            if (isset($account_invalid)) {
                if (isset($pass_2_errors)) {
                    echo $pass_2_errors;
                }
            }
            ?>
            <br /><br />
            <div class="row" style="width: 100%; position: relative;">
                <div class="checkbox-text">I would like to sign up for the Sleepovers Newsletter</div>
                <div class="inline-checkbox">
                    <label class="check">
                        <input type="checkbox" name="newsletter_notif" <?php echo isset($newsletter_notif) && $newsletter_notif ? "checked" : ""; ?> />
                        <span class="checkmark"></span>
                    </label>
                </div>
            </div>
            <br /><br />
            <div class="row" style="width: 100%; position: relative;">
                <div class="checkbox-text">I would like to receive email notifications when a Sleepovers Blog is posted</div>
                <div class="inline-checkbox">
                    <label class="check">
                        <input type="checkbox" name="blog_notif" <?php echo isset($blog_notif) && $blog_notif ? "checked" : ""; ?>>
                        <span class="checkmark"></span>
                    </label>
                </div>
            </div><br /><br />
            <div class="d-flex flex-row"><label class="align-self-center">Account Type</label>
                <select name="account_type" class="ml-auto p-2" onchange="
                        let name=document.getElementById('public_name');
                        let label=name.getElementsByTagName('label')[0];
                        let input=name.getElementsByTagName('input')[0];
                        if(this.value=='artist'){name.style.display='inline';input.required=true;label.innerHTML='Artist Name';}
                        else if(this.value=='venue'){name.style.display='inline';input.required=true;label.innerHTML='Venue Name';}
                        else{name.style.display='none';input.required=false;}
                    ">
                    <option class="select-option" value="user" <?php echo isset($account_type) ? ($account_type === "user" ? "selected='true'" : "") : "selected='true'"; ?>>User</option>
                    <option class="select-option" value="artist" <?php echo isset($account_type) && $account_type === "artist" ? "selected='true'" : ""; ?>>Artist/Musician</option>
                    <option class="select-option" value="venue" <?php echo isset($account_type) && $account_type === "value" ? "selected='true'" : ""; ?>>Venue</option>
                </select>
            </div><br />
            <div id="public_name" style="display:<?php echo isset($account_type) ? ($account_type === "user" ? "none" : "inline") : "none"; ?>;">
                <div class="d-flex flex-row"><label class="align-self-center">Public Name</label><input type="text" name="public_name" class="ml-auto p-2" <?php echo isset($public_name) ? "value='$public_name'" : ""; ?> /></div>
            </div><br />
            <?php
            if (isset($account_invalid)) {
                if (isset($account_errors)) {
                    echo $account_errors;
                }
            }
            ?>
            <hr>
            <input type="submit" value="Register" />
            <a href="home.php"><input type="button" value="Cancel" /></a><br />
        </form>
    </div>
</div>

<?php
include 'footer.php';
?>