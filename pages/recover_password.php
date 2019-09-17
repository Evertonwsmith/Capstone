<?php
$page_title = "Recover Password";
include 'header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

   $user_email = userGen($_POST['email']);

   $result = my_sql::select('*', '`useraccount`', "userEmail = :0", array("$user_email"));
   if (is_array($result) || is_object($result)) {
      foreach ($result as $value1) {
         echo "<br><br>Email sent to $user_email.";
      }
   } else {
      echo "<br><br>Email Not Found";
   }
}
function userGen($data)
{
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>

<br><br><br>
<hr>
<br>
<h1>Password Recovery</h1>
<br>
<hr>
<br>
<br><br><br>
<form action="" method="post">
   <label class='label-left'>Account Email </label><input type="text" name="email" class="box" />
   <input type="submit" value=" Submit " /><br />
</form>
<br><br><br>
<br>
<hr>


<?php
include "footer.php";
?>