<?php # settings.php
// This is the settings page for the site.
$page_title = 'Settings';
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
	if (!empty($_POST['maximgs'])) {
		$m = mysqli_real_escape_string ($dbc, $_POST['maximgs']);
	} else {
		$m = FALSE;
		$errors[] = 'Please enter a valid maximum number of images that can be uploaded at a time';
	}
	
	// Validate the password:
	if (!empty($_POST['timeout'])) {
		$t = mysqli_real_escape_string ($dbc, $_POST['timeout']);
	} else {
		$t = FALSE;
		$errors[] = 'Please enter a valid time out period in minutes';
	}
	
	if ($m && $t) { // If everything's OK.

		// Insert new value into database:
		$q = "UPDATE " . DB_TAB_SETTINGS . " SET max_imgs_upload=".$m.", timeout_mins=".$t.", last_updated=NOW() LIMIT 1";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (!$r){ // Failure.
			$errors[] = 'Error updating settings.';
		}else{
			// Set dialog box mode:
			$_SESSION['dialog_box'] = 1;
		} // End of MySQL update error.
	}
	mysqli_close($dbc);


}else{
	//get current settings from MySQL
	require (MYSQL);
	$q = "SELECT max_imgs_upload, timeout_mins FROM " . DB_TAB_SETTINGS . " LIMIT 1";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
	if (@mysqli_num_rows($r) == 1) { // A match was made.
		$row = $r->fetch_assoc();
		$t = intval($row["timeout_mins"]);
		$m = intval($row["max_imgs_upload"]);
	}
	mysqli_free_result($r);
	mysqli_close($dbc);
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
		if($('#maximgs').val().length==0){
			$('#maximgs-error-panel').show();
			bReturn = true;
		}else{
			$('#maximgs-error-panel').hide();
		}
		if($('#timeout').val().length==0){
			$('#timeout-error-panel').show();
			bReturn = true;
		}else{
			$('#timeout-error-panel').hide();
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
		modal: true,
		classes: {
			"ui-dialog-titlebar": "myDialogTitleClass", "ui-dialog-content": "myDialogContentClass"
		},
		buttons: {
			"OK": function() {
				$( this ).dialog( "close" );
			}
		}
	});
});
</script>

<form action="settings.php" method="post" class="w3-container" style="position:relative; top:90px">
	<!-- Dialog box for successful update -->	
	<?php
	if($_SESSION['dialog_box'] == 1){
		echo '<div id="dialog" title="Settings" style="display: none;">
		<p>The settings have been successfully updated.</p>
		</div>';
		$_SESSION['dialog_box'] = 0;
	}
	?>
	
	<h2 class="w3-text-blue"><?php echo $page_title; ?></h2>
	
	<!-- Errors for back end failures -->
	<?php
	if(!empty($errors)){
		foreach($errors as $msg){
			echo "<p><div class=\"ui-widget foreach-error\"><div class=\"ui-state-error ui-corner-all\" style=\"padding: 0.1em;\"><p><span class=\"ui-icon ui-icon-alert\" style=\"margin-left: .3em; margin-right: .3em;\"></span><strong> - $msg</strong></p></div></div></p>";
		}
	}
	?>

	<!-- Error panels for Ajax validation -->
	<p><div class="ui-widget" id="maximgs-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a valid maximum number of images that can be uploaded at a time</strong></p></div></div></p>
	<p><div class="ui-widget" id="timeout-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a valid time out period in minutes</strong></p></div></div></p>
		
	<label class="w3-text-blue">Maximum number of images that can be uploaded at a time:</label>
	<input class="w3-input w3-border" type="number" name="maximgs" id="maximgs" maxlength="5"  min="1" value="<?php if(isset($m)){echo $m;}else{echo '10';} ?>"/>
	</p><p>
	<label class="w3-text-blue">Time out (in minutes):</label>
	<input class="w3-input w3-border" type="number" name="timeout" id="timeout" maxlength="5" min="1" value="<?php if(isset($t)){echo $t;}else{echo '30';} ?>"/>
	</p><p>
	<input class="w3-btn w3-blue" type="submit" name="submit" id="submit" value="Save" />
	<input class="w3-btn w3-blue" type="button" value="Cancel" onClick="window.open('admin.php','_self')" />
	</p>
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>
			Enter the settings that will prevail across this website.
			<br><br>
			The maximum number of images setting is to prevent time out during image upload.
			<br><br>
			The time out setting is the number of minutes on inactivity until a user is logged out.
		</p>
	</div>
</form>

<?php
include ('includes/footer.html');
?>