<?php # login.php
// This is the login page for the site.
$page_title = 'Login';
include ('includes/header.html');

if (isset($_SESSION['user_id'])) {
	// User is already authenticated - redirect
	header("Location: admin.php");
	exit(); // Quit the script.
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require (MYSQL);
	
	// Initialise an error array:
	$errors = [];
	
	// Validate the email address:
	if (!empty($_POST['email'])) {
		$e = mysqli_real_escape_string ($dbc, $_POST['email']);
	} else {
		$e = FALSE;
		$errors[] = 'Please enter your email address';
	}
	
	// Validate the password:
	if (!empty($_POST['pass'])) {
		$p = mysqli_real_escape_string ($dbc, $_POST['pass']);
	} else {
		$p = FALSE;
		$errors[] = 'Please enter your password';
	}
	
	if ($e && $p) { // If everything's OK.

		// Query the database:
		$q = "SELECT user_id, email, level FROM " . DB_TAB_USERS . " WHERE (email='$e' AND pass=PASSWORD('$p'))";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (@mysqli_num_rows($r) == 1) { // A match was made.

			// Register the values:
			$_SESSION = mysqli_fetch_array ($r, MYSQLI_ASSOC); 
			mysqli_free_result($r);

			// Update user's last log in date/time:
			$q = "UPDATE " . DB_TAB_USERS . " SET Last_log_In=NOW() WHERE email='$e'";
			mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
			mysqli_close($dbc);
			
			// Set expiration values:
			$_SESSION['last_activity'] = time(); //this was the moment of last activity.
			$_SESSION['expire_time'] = 30 * 60; //expiry time in seconds
							
			// Redirect the user:
			$url = 'admin.php'; // Define the URL.
			ob_end_clean(); // Delete the buffer.
			header("Location: $url");
			exit(); // Quit the script.
				
		} else { // No match was made.
			$errors[] = 'The email address and password entered do not match those on file.';
		}
	}
	
	mysqli_close($dbc);

} // End of SUBMIT conditional.
?>

<!-- Ajax validation -->
<script>
$(function(){
	// Assign an event handler to the form:
	$('#submit').click(function(){
		$('.foreach-error').hide();
		var bReturn = false
		// If appropriate, perform the Ajax request:
		if($('#email').val().length==0){
			$('#email-error-panel').show();
			$('#invalid-email-error-panel').hide();
			bReturn = true;
		}else{
			$('#email-error-panel').hide();
			if(is_valid_email($('#email').val())){
				$('#invalid-email-error-panel').hide();
			}else{			
				$('#invalid-email-error-panel').show();
				bReturn = true;
			}
		}
		if($('#pass').val().length==0){
			$('#pass-error-panel').show();
			bReturn = true;
		}else{
			$('#pass-error-panel').hide();
		}
		if(bReturn){return false};
	}); //  End of form submission.	
}); // End of document ready.
</script>

<form action="login.php" method="post" class="w3-container" style="position:relative; top:90px">
	<h2 class="w3-text-blue"><?php echo $page_title; ?></h2>
	<p>
	<?php
	if(!empty($errors)){
		foreach($errors as $msg){
			echo "<p><div class=\"ui-widget foreach-error\"><div class=\"ui-state-error ui-corner-all\" style=\"padding: 0.1em;\"><p><span class=\"ui-icon ui-icon-alert\" style=\"margin-left: .3em; margin-right: .3em;\"></span><strong> - $msg</strong></p></div></div></p>";
		}
	}
	?>

	<!-- Error panels for Ajax validation -->
	<p><div class="ui-widget" id="invalid-email-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a valid email address</strong></p></div></div></p>
	<p><div class="ui-widget" id="email-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter your email address</strong></p></div></div></p>
	<p><div class="ui-widget" id="pass-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter your password</strong></p></div></div></p>
	
	<label class="w3-text-blue">Email Address:</label>
	<input class="w3-input w3-border" type="text" name="email" id="email" maxlength="40" value="<?php if(isset($e)){echo $e;} ?>"/>
	</p><p>
	<label class="w3-text-blue">Password:</label>
	<input class="w3-input w3-border" type="password" name="pass" id="pass" maxlength="20" />
	</p><p>
	<input class="w3-btn w3-blue" type="submit" name="submit" id="submit" value="Login" />
	</p>
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>Enter the email address and password provided.</p>
	</div>
</form>

<?php include ('includes/footer.html'); ?>