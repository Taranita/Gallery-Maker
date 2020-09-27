<?php # amend.php
// This is the amend photo gallery page
$page_title = 'Amend Photo Gallery';
include ('includes/header.html');

if (!isset($_SESSION['user_id'])) {
	// User is not authenticated - redirect
	redirect_user();
}

// Set default modes:
$_SESSION['confirmed']='1';

require (MYSQL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Initialise arrays and variables:
	$errors = [];
	$image_data = [];
	
	// Validate the title:
	if (!empty($_POST['title'])) {
		$new_title = mysqli_real_escape_string ($dbc, $_POST['title']);
	} else {
		$errors[] = 'Please enter a title';
	}

	// Get checkbox values
	get_checkbox_checked();
	
	if (empty($errors)) { // If everything's OK.
		// Get image details.
		$image_data = [];

		// Set counter
		$counter = 0;
		
		foreach($_POST['i_desc'] as $key => $desc){
			// Get file name:	
			$i_name = mysqli_real_escape_string($dbc, $_POST['i_name'][$key]);
			
			// Update data for images that have not been excluded:
			if(!isset($_POST['i_excl'][$key])){
				$i_desc = mysqli_real_escape_string($dbc, $desc);
				$i_date = $_POST['i_date'][$key];
				
				// Update MySQL image data:	
				$counter++;
				$q = "UPDATE " . DB_TAB_IMAGES . " SET date='".$i_date."', description='".$i_desc."', sort_order='$counter' WHERE file_name='".$i_name."' AND directory='".$_SESSION['d']."' LIMIT 1";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
				
				if (!$r){ // Failure.
					$errors[] = 'Error adding image information.';
				} // End of MySQL update error.
			}
			else{
				// Delete unrequired file from directory:
				unlink('../'.$_SESSION['d'].'/'.$i_name);
				
				// Delete unrequired file's data from MySQL table:
				$q = "DELETE FROM " . DB_TAB_IMAGES . " WHERE file_name='".$i_name."' AND directory='".$_SESSION['d']."' LIMIT 1";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
				
				if (!$r){ // Failure.
					$errors[] = 'Error adding image information.';
				} // End of MySQL update error.
			} // End of exclude conditional.
		} // End of FOREACH.		
		
		if (empty($errors)) { // If everything's OK, update other gallery details:
			$q = "UPDATE " . DB_TAB_GALLERIES . " SET title='".$new_title."', visible=".$_SESSION['visible'].", inc_dates=".$_SESSION['inc_dates']." WHERE directory='".$_SESSION['d']."' LIMIT 1";
			$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
			
			if (!$r){ // Failure.
				$errors[] = 'Error amending gallery title.';
			}else{
				$_SESSION['title'] = $new_title;					
			} // End of MySQL update error.
		}
		
		if (empty($errors)) { // If everything's still OK, check to see if icon is being removed:
			if(isset($_POST['i_excl_icon'])){
				// Update MySQL gallery data for icon:
				$q = "UPDATE " . DB_TAB_GALLERIES . " SET icon='' WHERE directory='".$_SESSION['d']."' LIMIT 1";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
				
				if (!$r){ // Failure.
					$errors[] = 'Error amending gallery title.';
				}else{
					// Remove icon file:
					unlink('../'.$_SESSION['d'].'/'.$_SESSION['icon']);
					$_SESSION['icon']='';
				} // End of MySQL update error.
			}				
		}
	} // End of errors conditional.
	
	// Set dialog box mode if no errors		
	if (empty($errors)) { // If everything's OK.
		$_SESSION['dialog_box'] = 1;
	} // End of MySQL update error.
} // End of SUBMIT conditional.
else
{
	// Get directory of target gallery:
	$_SESSION['d']=$_GET['d'];
}

// Get icon details:
$q = "SELECT title, icon, visible, inc_dates FROM " . DB_TAB_GALLERIES . " WHERE directory = '".$_SESSION['d']."'";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

if (!$r){ // Failure.
	$errors[] = 'Error getting gallery data.';
}else{
	$row = $r->fetch_assoc();
	$_SESSION['title'] = $row["title"];
	$_SESSION['icon'] = $row["icon"];
	$_SESSION['visible'] = $row["visible"];
	$_SESSION['inc_dates'] = $row["inc_dates"];
}
	
mysqli_close($dbc);
?>


<!-- Ajax validation -->
<script>
$(function(){
	// Assign an event handler to the form:
	$('#submit').click(function(){
		$('.foreach-error').hide();
		var bReturn = false
		// If appropriate, perform the Ajax request:
		if($('#title').val().length==0){
			$('#title-error-panel').show();
			bReturn = true;
			window.scrollTo(0, 0); 
		}else{
			$('#title-error-panel').hide();
		}
		if(bReturn){return false};
	}); //  End of form submission.	
}); // End of document ready.

/* Events fired on entering the drop target */
document.addEventListener("dragenter", function(event) {
  if ( event.target.className == "droptarget" ) {
    event.target.style.backgroundColor = "lightblue";
  }
});

/* Events fired on leaving the drop target */
document.addEventListener("dragleave", function(event) {
  if ( event.target.className == "droptarget" ) {
    event.target.style.backgroundColor = "";
  }
});
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

<form action="amend.php" method="post" enctype="multipart/form-data" class="w3-container" style="position:relative; top:90px">
	<!-- Dialog box for successful update -->	
	<?php
	if($_SESSION['dialog_box'] == 1){
		echo '<div id="dialog" title="Amend Photo Gallery" style="display: none;">
		<p>The gallery has been successfully amended.</p>
		</div>';
		$_SESSION['dialog_box'] = 0;
	}
	?>
	
	<h2 class="w3-text-blue"><?php echo $page_title; ?></h2>
	
	<div id="fields1">
	<p>
	<?php
	if(!empty($errors)){
		foreach($errors as $msg){
			echo "<p><div class=\"ui-widget foreach-error\"><div class=\"ui-state-error ui-corner-all\" style=\"padding: 0.1em;\"><p><span class=\"ui-icon ui-icon-alert\" style=\"margin-left: .3em; margin-right: .3em;\"></span><strong> - $msg</strong></p></div></div></p>";
		}
	}
	?>
	</p>
	</div>
	
	<!-- Error panels for Ajax validation -->
	<p><div class="ui-widget" id="title-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter a title</strong></p></div></div></p>
		
<!-- Initial fields display-->
	<div id="fields2" />
		<p>
			<label class="w3-text-blue" ng-click="changeTitle()">Title:</label>
			<input class="w3-input w3-border" type="text" name="title" id="title" maxlength="80" value="<?php echo $_SESSION['title']; ?>" />
		</p>
	
		<p>
			<label class="w3-text-blue">Gallery Visible to Users?</label>
			<input class="w3-check" type="checkbox" name="visible" <?php if($_SESSION['visible']==1){echo "checked";} ?> />
		</p>
	
		<p>
			<label class="w3-text-blue">Include Dates in Image Descriptions?</label>
			<input class="w3-check" type="checkbox" name="inc_dates"  <?php if($_SESSION['inc_dates']==1){echo "checked";} ?> />
		</p>
	</div>
	
<!-- Image details fields display-->
	<div><label class="w3-text-blue">Amend image data for <?php echo $_SESSION['title'] ?></label><div>
	<div ng-app="myApp" ng-controller="imagesCtrl" class="flex-container">
		<div ng-repeat="x in image_data" class="flex-item">
			<div ondrop="drop(event)" ondragover="allowDrop(event)" id="dropZone_{{$index}}" class="droptarget">&nbsp;
				<div class="w3-card-2" draggable="true" ondragstart="drag(event)" id="dragDiv_{{$index}}">
					<table width="99%">
						<tr>
							<td width="200px">
								<a href="../{{x.Directory}}/{{x.Name}}" data-lightbox="{{x.Name}}"><img src="../{{x.Directory}}/{{x.Name}}" style="border: 1px solid #ddd; border-radius: 4px; padding: 5px; width: 200px; display: block; margin-left: auto; margin-right: auto;" draggable="false"></a>
							</td><td width="300px">
								<p>
									<input type="text" name="i_name[{{$index }}]" value="{{x.Name}}" hidden />
								</p>
								<p>
									<label class="w3-text-blue">Image {{$index + 1}} Description:</label>
									<input class="w3-input w3-border" type="text" name="i_desc[{{$index }}]" maxlength="256" value="{{ x.Description }}">
								</p>
								<p>
									<label class="w3-text-blue">Date:</label>
									<input style="padding:8px;display:block;border:none;border-bottom:1px solid #ccc" class="w3-border" type="date" name="i_date[{{ $index }}]" value="{{x.Date}}" size="12" />
								</p>
								<p>
									<label class="w3-text-blue">Remove:</label>
									<input class="w3-check" type="checkbox" name="i_excl[{{ $index }}]" value="1"/>
								</p>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="icon-box">
		<!--Icon - if present-->
		<?php
			if($_SESSION['icon']!=''){echo
			'<div class="w3-card-2">
				<table width="99%">
					<tr>
						<td width="200px">
							<a href="../'.$_SESSION['d'].'/'.$_SESSION['icon'].'"><img src="../'.$_SESSION['d'].'/'.$_SESSION['icon'].'" style="border: 1px solid #ddd; border-radius: 4px; padding: 5px; display: block; margin-left: auto; margin-right: auto;"></a>
						</td>
						<td>
							<p>
								<label class="w3-text-blue">Image Gallery Icon</label>
							</p>
							<p>
								<label class="w3-text-blue">Remove:</label>
								<input class="w3-check" type="checkbox" name="i_excl_icon" value="1"/>
							</p>
						</td>
					</tr>
				</table>
			</div>';
			}
		?>
	</div>
	
<!-- Buttons-->
	<p>
		<input class="w3-btn w3-blue" type="submit" name="submit" id="submit" value="Save"/>
		<input class="w3-btn w3-blue" type="button" value="Cancel" onClick="window.open('admin.php','_self')" />
	</p>
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>
			You can amend title of your gallery, or other gallery details.
			<br><br>
			You can also change details of each of the images, or remove them from the gallery.
			<br><br>
			Additionally, you can drag and drop the images to arrange them in the order you would like.
		</p>
	</div>
</form>

<script>
	//   Drag and drop handling
	function allowDrop(ev) {
		ev.preventDefault();
	}

	function drag(ev) {
		ev.dataTransfer.setData("text", ev.target.id);
	}
	
	function drop(ev) {
		// Remove background colour of target			
		ev.target.style.backgroundColor = "";

		ev.preventDefault();
		var data = ev.dataTransfer.getData("text");

		dropNum = Number(right_of(ev.target.id, "_"));
		dragNum = Number(right_of(data, "_"));
		console.table(dropNum + ', ' + dragNum);

		if (dragNum == dropNum){		
			// Dragging to currently selected image - do not process drop	
			return;
		}

		// get element top be dropped		
		elementToDrop = document.getElementById(data);

		if (dragNum > dropNum){
			// Move image to new location, dragging from high to low
			ev.target.appendChild(elementToDrop);

			// Images has been dropped into lower position - move images between original and new positions up one place
			for(i=dragNum; i>dropNum; i--){				
				numToMove = i - 1;
				elementToMove = document.getElementById("dragDiv_" + numToMove);
				document.getElementById("dropZone_" + i).appendChild(elementToMove);

				// renumber ID
				elementToMove.id = "dragDiv_" + i;
			}
		}
		else{
			// Move image to new location, dragging from low to high
			elementToReceive = document.getElementById("dropZone_" + dropNum);
			elementToReceive.appendChild(elementToDrop);

			// Images has been dropped into higher position - move images between original and new positions down one place
			for(i=dragNum; i<dropNum; i++){				
				numToMove = i + 1;
				elementToMove = document.getElementById("dragDiv_" + numToMove);
				document.getElementById("dropZone_" + i).appendChild(elementToMove);

				// renumber ID
				elementToMove.id = "dragDiv_" + i;
			}
		}

		// renumber ID of dropped image
		elementToDrop.id = "dragDiv_" + dropNum;
	}
</script>

<?php include ('includes/footer.html'); ?>
