<?php # admin.php
// This is the admin page for the site.
$page_title = 'Admin';
include ('includes/header.html');

if (!isset($_SESSION['user_id'])) {
	// User is not authenticated - redirect
	redirect_user();
}

// Set default dialog box mode:
$_SESSION['dialog_box'] = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	require (MYSQL);
	
	// Initialise an arrays and variables:
	$errors = [];
	
	// Get gallery to be deleted:
	$pg = $_POST['hidden1'];
	
	// Get folder	
	$q = "SELECT directory FROM " . DB_TAB_GALLERIES . " WHERE title = '".$pg."' AND confirmed = 1";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
	
	if (!$r){ // Failure.
		$errors[] = 'Error getting directory for gallery.';
		goto errorFound;
	}
	
	// Add gallery data:
	if (@mysqli_num_rows($r) != 1) { // A match was not made.
		$errors[] = 'Error getting directory for gallery.';
		goto errorFound;
	}
	
	$row = $r->fetch_assoc();
	$d = $row["directory"];
	$pth = '../'.$d;
	
	$files = glob($pth.'/*'); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file)){
			unlink($file); // delete file
		}
	}
		
	// Remove folder
	if(!rmdir($pth)){
		$errors[] = 'Error removing photo gallery folder.';
		goto errorFound;
	}
	
	// Delete gallery details from MySQL:
	$q = "DELETE FROM " . DB_TAB_GALLERIES . " WHERE title = '".$pg."' AND confirmed = 1 LIMIT 1";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
	
	if (!$r){ // Failure.
		$errors[] = 'Error removing photo gallery folder details.';
		goto errorFound;
	}
	
	// Delete gallery image details from MySQL:
	$q = "DELETE FROM " . DB_TAB_IMAGES . " WHERE directory = '".$d."'";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
	
	if (!$r){ // Failure.
		$errors[] = 'Error removing photo gallery image details.';
	}
	
	// Set dialog box mode:
	$_SESSION['dialog_box'] = 1;
	
	// Point to jump to if error found.
	errorFound:
	
	// Tidy up MySQL:
	mysqli_close($dbc);	

} // End of submit conditional.
?>

<script>
// slide up/down sub menus, ensuring other menu is hidden
function clickAmend(itemId){
	$("#" + itemId).slideToggle(500);
	
	if(itemId=='subAmend'){
		$("#subAdd").slideUp(500);
		$("#subRemove").slideUp(500);
	}else if(itemId=='subAdd'){
		$("#subAmend").slideUp(500);
		$("#subRemove").slideUp(500);
	}else{
		$("#subAdd").slideUp(500);
		$("#subAmend").slideUp(500);
	}
}

function remove_gallery_dialog(pg){
	document.getElementById("dialog-confirm-text").innerHTML = "Are you sure you wish to permanently remove the photo gallery " + pg + "?";	
	document.getElementById("hidden1").value = pg;
	$("#dialog-confirm").dialog("open");
}

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
			"Remove photo gallery": function() {
				$( this ).dialog( "close" );
				document.getElementById("myForm").submit();
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		}
	});
});

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
				$( this ).dialog( "close" );
			}
		}
	});
});
</script>

<form action="admin.php" id="myForm" method="post">
	<!-- Dialog box for successful removal of gallery -->	
	<?php
	if($_SESSION['dialog_box'] == 1){
		echo '<div id="dialog" title="Remove Photo Gallery" style="display: none;">
		<p>The gallery '.$pg.' has been successfully removed.</p>
		</div>';
		$_SESSION['dialog_box'] = 0;
	}
	?>

	<!-- Errors for back end failures -->
	<?php
	if(!empty($errors)){
		foreach($errors as $msg){
			echo "<p><div class=\"ui-widget foreach-error\"><div class=\"ui-state-error ui-corner-all\" style=\"padding: 0.1em;\"><p><span class=\"ui-icon ui-icon-alert\" style=\"margin-left: .3em; margin-right: .3em;\"></span><strong> - $msg</strong></p></div></div></p>";
		}
	}
	?>

	<div ng-app="myApp" ng-controller="galleryCtrl">
		<div class="w3-container" style="position:relative; top:90px">
			<h2 class="w3-text-blue"><?php echo $page_title; ?></h2>
			
			<ul class="w3-ul w3-hoverable">
				<li <?php if($_SESSION['level']!=1){echo 'hidden';} ?>><a href="settings.php" title="Settings">Settings</a></li>
				<li <?php if($_SESSION['level']!=1){echo 'hidden';} ?>><a href="users.php" title="Manage Users">Manage Users</a></li>
				<li><a href="load.php" title="Create">Create new photo gallery</a></li>
				
				<li><a href="#" onClick="clickAmend('subAmend');" id="amend">Amend photo gallery</a></li>
								
				<ul class="w3-ul w3-hoverable subList" id="subAmend" hidden="true">
					<div ng-repeat="x in gallery_data">
						<li><a href="amend.php?d={{x.Directory}}">{{x.Title}}</li>
					</div>
				</ul>
				
				<li><a href="#" onClick="clickAmend('subAdd');" id="amend">Add images to existing photo gallery</a></li>
								
				<ul class="w3-ul w3-hoverable subList" id="subAdd" hidden="true">
					<div ng-repeat="z in gallery_data">
						<li><a href="add.php?d={{z.Directory}}">{{z.Title}}</li>
					</div>
				</ul>
				
				<li><a href="#" onClick="clickAmend('subRemove');" id="remove">Remove photo gallery</a></li>
						
				<ul class="w3-ul w3-hoverable subList" id="subRemove" hidden="true">
					<div ng-repeat="y in gallery_data">
						<li><a href="#" name="{{y.Title}}" onClick='remove_gallery_dialog(this.name);'>{{y.Title}}</li>
					</div>
				</ul>		
			</ul>
		</div>
	</div>
	
	<!-- hidden field which will hold value of selected gallery for passing to PHP -->
	<input type="hidden" name="hidden1" id="hidden1"  />
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>
			This application enables users to set up and maintain galleries of photos. So, for example, if you've just returned from a nice holiday, you can quickly and easily add your photos to this website so that they are available for the world to see.
			<br><br>
			For that matter, you could add them using this application while you are on holiday using your smart phone!
			<br><br>
			Use any of the options provided as required, however the first step would be to create new photo gallery.
		</p>
	</div>
</form>

<?php
	echo '<div id="dialog-confirm" title="Remove Photo Gallery" style="display: none;">
	<p id="dialog-confirm-text"></p>
	</div>';
?>

<!-- get gallery data -->
<script>
var app = angular.module("myApp", []);
app.controller('galleryCtrl', function($scope, $http) {
   $http.get("galleries_mysql_by_title.php")
   .then(function (response) {$scope.gallery_data = response.data.galleries;});
});
</script>

<?php include ('includes/footer.html'); ?>