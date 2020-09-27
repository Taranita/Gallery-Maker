<?php # logout.php
// This is the logout page for the site.
$page_title = 'Logout';
include ('includes/header.html');

// Log out the user.
session_destroy();
?>

<h2 class="w3-text-blue" style="position:relative; top:90px; left:10px">You are now logged out.</h2>

<?php
include ('includes/footer.html');
?>