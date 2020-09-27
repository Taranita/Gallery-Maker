<?php # timeout.php
// This is the timed out page for the site.
$page_title = 'Timed Out';
include ('includes/header.html');

// Log out the user.
end_session();
?>

<h2 class="w3-text-blue" style="position:relative; top:90px; left:10px">Your session has timed out. Please log in if you would like to continue.</h2>

<?php
include ('includes/footer.html');
?>