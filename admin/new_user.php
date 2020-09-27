<?php # new_user.php
// This is the page for adding a new user
$page_title = 'New User';
include ('includes/header.html');

// Set default dialog box mode:
$_SESSION['dialog_box'] = 0;

if (!isset($_SESSION['user_id'])) {
	// User is not authenticated - redirect
	redirect_user();
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
		$errors[] = 'Please enter the email address';
	}
	
	// Validate the password:
	if (!empty($_POST['pass'])) {
		$p = mysqli_real_escape_string ($dbc, trim($_POST['pass']));
	} else {
		$p = FALSE;
		$errors[] = 'Please enter the password';
	}
	
    if ($e && $p) { // If everything's OK.

		// Query the database to see if email has already been used:
		$q = "SELECT user_id FROM " . DB_TAB_USERS . " WHERE email='$e'";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (@mysqli_num_rows($r) == 0) { // new email address not found so add user:

			// Register the values:
			mysqli_free_result($r);

            // Update user's last log in date/time:
            $al = $_POST['accessLevel'];
			$q = "INSERT INTO " . DB_TAB_USERS . " (email, pass, level) VALUES ('$e', PASSWORD('$p'), '$al')";
			mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
            	
            // Set confirmationdialog box mode:
            $_SESSION['dialog_box'] = 1;
				
		} else { // This email address is already in use.
			$errors[] = 'The email address is already in use.';
        }       
        
	    mysqli_close($dbc);
	}
}// End of SUBMIT conditional.
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
		if($('#pass').val().trim().length < 6){
			$('#pass-error-panel').show();
			$('#pass-match-error-panel').hide();
			bReturn = true;
		}else if($('#pass').val() != $('#passConf').val()){
			$('#pass-error-panel').hide();
			$('#pass-match-error-panel').show();
			bReturn = true;
		}else{
			$('#pass-error-panel').hide();
			$('#pass-match-error-panel').hide();
		}
		if(bReturn){return false};
	}); //  End of form submission.	
}); // End of document ready.
</script>

<!-- Dialog box function -->
<script>
$( function() {
    $( "#dialog" ).dialog({
		dialogClass: "no-close",
		resizable: false,	
		width: 500,	
		modal: true,
		classes: {
			"ui-dialog-titlebar": "myDialogTitleClass", "ui-dialog-content": "myDialogContentClass"
		},
		buttons: {
			"OK": function() {
                window.location = 'users.php';
			}
		}
	});
});
</script>

<form action="new_user.php" method="post" class="w3-container" style="position:relative; top:90px">
	<!-- Dialog box for successful update -->	
	<?php
	
	if($_SESSION['dialog_box'] == 1){
		echo '<div id="dialog" title="New User" style="display: none;">
		<p>The new user has been successfully created.</p>
		</div>';
		$_SESSION['dialog_box'] = 0;
	}
	?>
	
	<h2 class="w3-text-blue"><?php echo $page_title; ?></h2>
	<p>
	<!-- Errors for back end failures -->
	<?php
	if(!empty($errors)){
		foreach($errors as $msg){
			echo "<p><div class=\"ui-widget foreach-error\"><div class=\"ui-state-error ui-corner-all\" style=\"padding: 0.1em;\"><p><span class=\"ui-icon ui-icon-alert\" style=\"margin-left: .3em; margin-right: .3em;\"></span><strong> - $msg</strong></p></div></div></p>";
		}
	}
	?>

	<!-- Error panels for Ajax validation -->
	<p><div class="ui-widget" id="invalid-email-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a valid email address</strong></p></div></div></p>
	<p><div class="ui-widget" id="email-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter the email address</strong></p></div></div></p>
	<p><div class="ui-widget" id="pass-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a valid password</strong></p></div></div></p>
	<p><div class="ui-widget" id="pass-match-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - The confirmation password does not match the password</strong></p></div></div></p>

    <p>
	<label class="w3-text-blue">Email Address:</label>
	<input class="w3-input w3-border" type="text" name="email" id="email" maxlength="40" value="<?php if(isset($e)){echo $e;} ?>"/>
	</p><p>
	<label class="w3-text-blue">Password (minimum 6 characters, no spaces):</label>
	<input class="w3-input w3-border" type="password" name="pass" id="pass" maxlength="20" />
	</p><p>
	<label class="w3-text-blue">Confirm Password:</label>
	<input class="w3-input w3-border" type="password" name="passConf" id="passConf" maxlength="20" />
	</p><p>
	<label class="w3-text-blue">Access Level:</label><br>
	<input class="w3-check w3-border" type="radio" checked name="accessLevel" value="0"><label> User</label><br>
	<input class="w3-check w3-border" type="radio" name="accessLevel" value="1"><label> Administrator</label>
	</p><p>
	<input class="w3-btn w3-blue" type="submit" name="submit" id="submit" value="Save" />
	<input class="w3-btn w3-blue" type="button" value="Cancel" onClick="window.open('users.php','_self')" />
	</p>
</form>

<?php
	echo '<div id="dialog-confirm" title="Remove Photo Gallery" style="display: none;">
	<p id="dialog-confirm-text"></p>
	</div>';

    include ('includes/footer.html');
?>