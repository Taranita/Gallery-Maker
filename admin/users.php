<?php # users.php
// This is the page for user management.
$page_title = 'Manage Users';
include ('includes/header.html');

if (!isset($_SESSION['user_id'])) {
	// User is not authenticated - redirect
	redirect_user();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require (MYSQL);
	
	// Initialise an arrays and variables:
	$errors = [];
	
	// Get gallery to be deleted:
	$email = $_POST['hidden1'];
	
	if($_POST['hidden2'] === 'delete'){
		// Delete user details from MySQL:
		$q = "DELETE FROM " . DB_TAB_USERS . " WHERE email = '".$email."' LIMIT 1";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (!$r){ // Failure.
			$errors[] = 'Error removing user details.';
		}
		
		// Tidy up MySQL:
		mysqli_close($dbc);

	}elseif($_POST['hidden2'] === 'pwd'){
		$p = mysqli_real_escape_string ($dbc, trim($_POST['hidden3']));

		// Reset user password in MySQL:
		$q = "UPDATE " . DB_TAB_USERS . " SET pass=PASSWORD('$p') WHERE email='".$email."' LIMIT 1";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (!$r){ // Failure.
			$errors[] = 'Error resetting user password.';			
		}else{
			// Set session  variable to display confirmation dialog box:
			$_SESSION['dialog_box'] = 1;
		}
		
		// Tidy up MySQL:
		mysqli_close($dbc);
	}

} // End of submit conditional.

// Functions:
function GetAccessLevel($accessLevel){
    if($accessLevel=="1"){
        return "Administrator";
    }else{
        return "User";
    }
}

function FormatDateTime($dateTime){
	if($dateTime != '0000-00-00 00:00:00'){
		$date = date_create($dateTime);
		return date_format($date, 'd/m/Y g:i:s a');
	}    
}
?>

<script>
/* Confirm deletion of user */
function delete_user(email){
	document.getElementById("dialog-confirm-text").innerHTML = "Are you sure you wish to permanently delete the user " + email + "?";	
	document.getElementById("hidden1").value = email;
	$("#dialog-confirm").dialog("open");
}

/* Set up dialog box for changing user password */
function change_pwd(email){
	document.getElementById("dialog-change-pwd-text").innerHTML = "Enter the new password for user " + email + ":";	
	document.getElementById("hidden1").value = email;
	document.getElementById("pass").value = '';
	document.getElementById("passConf").value = '';
	$("#dialog-change-pwd").dialog("open");
}

/* Dialog box for confirmation deletion of user */
$( function() {
    $( "#dialog-confirm" ).dialog({
		dialogClass: "no-close",
		resizable: false,
		width: 500,
		modal: true,
		autoOpen: false,
		classes: {
			"ui-dialog-titlebar": "myDialogTitleClass", "ui-dialog-content": "myDialogContentClass"
		},
		buttons: {
			"Delete user": function() {	
				document.getElementById("hidden2").value = 'delete';
				$( this ).dialog( "close" );
				document.getElementById("myForm").submit();
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
});

/* Dialog box for changing user password */
$( function() {
    $( "#dialog-change-pwd" ).dialog({
		dialogClass: "no-close",
		resizable: false,
		width: 500,
		modal: true,
		autoOpen: false,
		classes: {
			"ui-dialog-titlebar": "myDialogTitleClass", "ui-dialog-content": "myDialogContentClass"
		},
		buttons: {
			"Reset password": function() {
				if($('#pass').val().trim().length < 6){
					$('#pass-error-panel').show();
					$('#pass-match-error-panel').hide();
					exit;
				}else if($('#pass').val() != $('#passConf').val()){
					$('#pass-error-panel').hide();
					$('#pass-match-error-panel').show();
					exit;
				}
			
				document.getElementById("hidden2").value = 'pwd';
				document.getElementById("hidden3").value = $('#pass').val();
				$( this ).dialog( "close" );
				document.getElementById("myForm").submit();
			},
			Cancel: function() {
				$('#pass-error-panel').hide();
				$('#pass-match-error-panel').hide();
				$( this ).dialog( "close" );
			}
		}
	});
});

/* Dialog box for confirmation of password reset */
$( function() {
    $( "#dialog-confirm-change-pwd" ).dialog({
		dialogClass: "no-close",
		resizable: false,	
		width: 500,	
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

<form action="users.php" id="myForm" method="post" class="w3-container" style="position:relative; top:90px">
	
	<!-- Dialog box for confirmation of password reset dialog box -->	
	<?php
	if($_SESSION['dialog_box'] == 1){
		echo '<div id="dialog-confirm-change-pwd" title="Reset User Password">
		<p>The password was successfully reset.</p>
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

    //get current settings from MySQL
	require (MYSQL);
	$q = "SELECT * FROM " . DB_TAB_USERS . " ORDER BY user_id";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
    if ($r) { // Check we have results.
        // Set up user table:
        echo '<table class="w3-table-all w3-hoverable">';
        echo '<tr class="w3-blue"><td>Email Address</td><td>Access Level</td><td>Last Log In</td><td></td><td></td></tr>';
        // Loop through the results, creating a row for each user:
        while($row=mysqli_fetch_array($r, MYSQLI_ASSOC)){
            echo "<td>".$row['email']."</td>";
            echo "<td>".GetAccessLevel($row['level'])."</td>";
            echo "<td>".FormatDateTime($row['Last_log_In'])."</td>";
			echo "<td><input class=\"w3-btn w3-blue w3-tiny\" type=\"button\" value=\"Reset Password\" onClick=\"change_pwd('".$row['email']."')\" /></td>";
        	echo "<td><input class=\"w3-btn w3-blue w3-tiny\" type=\"button\" value=\"Delete\" onClick=\"delete_user('".$row['email']."')\" /></td></tr>";
        }
	    echo '</table>';
    }
	mysqli_free_result($r);
	mysqli_close($dbc);
    ?>

	<p>
	<input class="w3-btn w3-blue" type="button" value="New User" onClick="window.open('new_user.php','_self')" />
	</p>
	
	<!-- hidden fields which will hold value of selected user and action to take -->
	<input type="hidden" name="hidden1" id="hidden1"  />
	<input type="hidden" name="hidden2" id="hidden2"  />
	<input type="hidden" name="hidden3" id="hidden3"  />
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>
			Use this page to manage system users.
		</p>
	</div>
</form>

<?php
// Create the (initially hidden) user deletion confirmation dialog box:
echo '<div id="dialog-confirm" title="Delete User" style="display: none;">
<p id="dialog-confirm-text"></p>
</div>';

// Create the (initially hidden) change password dialog box:
echo '<div id="dialog-change-pwd" title="Reset User Password" style="display: none;">
<p id="dialog-change-pwd-text"></p><p>
<label class="w3-text-blue">Password (minimum 6 characters, no spaces):</label>
<input class="w3-input w3-border" type="password" name="pass" id="pass" maxlength="20" />
</p><p>
<label class="w3-text-blue">Confirm Password:</label>
<input class="w3-input w3-border" type="password" name="passConf" id="passConf" maxlength="20" />
</p>
<p><div class="ui-widget" id="pass-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a valid password</strong></p></div></div></p>
<p><div class="ui-widget" id="pass-match-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - The confirmation password does not match the password</strong></p></div></div></p>
</div>';

include ('includes/footer.html');
?>