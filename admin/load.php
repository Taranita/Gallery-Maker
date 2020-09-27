<?php # load.php
// This is the create new photo gallery page
$page_title = 'Create New Photo Gallery';
include ('includes/header.html'); 

if (!isset($_SESSION['user_id'])) {
	// User is not authenticated - redirect
	redirect_user();
}

// Set session variable so that unconfirmed images are returned from MySQL:
$_SESSION['confirmed']='0';

require (MYSQL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {	
	// Initialise an arrays and variables:
	$errors = [];
	$image_data = [];
	
	if($_SESSION['load_mode'] == 1){
		// Initial validation and upload.
		
		// Validate the title:
		if (!empty($_POST['title'])) {
			$title = mysqli_real_escape_string ($dbc, $_POST['title']);
			$_SESSION['title'] = $title;
		} else {
			$errors[] = 'Please enter a title';
		}
		
		// Validate the abbreviated title:
		if (!empty($_POST['abbr'])) {
			$abbr = mysqli_real_escape_string ($dbc, $_POST['abbr']);
		} else {
			$errors[] = 'Please enter an abbreviated title';
		}
		
		// Check for attachments(s):
		if ($_FILES['imgs']['name'][0] == '') {
			$errors[] = 'Please select one or more images to upload';
		}
		
		// Check for exceeding maximum number of images:
		if (count($_FILES['imgs']['name']) > $_SESSION['max_imgs_upload']) {
			$errors[] = 'You have tried to upload '.count($_FILES['imgs']['name']).' images, a maximum of '.$_SESSION['max_imgs_upload'].' images may be uploaded';
		}
		
		if (empty($errors)) { // If everything's OK.
			// Create new folder:
			$_SESSION['new_folder'] = '../'.$abbr;
			
			// Set directory:
			$_SESSION['d'] = $abbr;
			if(is_dir($_SESSION['new_folder'])){
				$errors[] = 'Unable to create directory for images as proposed directory name already exists.';
			}
			elseif(!mkdir($_SESSION['new_folder'])){
				$errors[] = 'Error creating new folder for images.';
			}
			else{
				// Copy all files into folder:			
				foreach($_FILES['imgs']['tmp_name'] as $key => $img_name){
					echo "<script>console.log('".$_FILES['imgs']['name'][$key]."');</script>";
					sleep(1);
					if(!move_uploaded_file($img_name, $_SESSION['new_folder'].'/'.$_FILES['imgs']['name'][$key])){
						$errors[] = 'Error copying image \''.$img_name.'\'.';
					}
					else{
						// Image successfully uploaded - add details to arrays for subsequent MySQL table insertion
						$image_data[] = "('".$_FILES['imgs']['name'][$key]."', '".$abbr."', '".date("Y-m-d", filemtime($_SESSION['new_folder'].'/'.$_FILES['imgs']['name'][$key]))."')";
					}
				}
				
				// Copy icon, if present:
				$icon = $_FILES['icon']['name'];
				if ($icon != '') {
					if(!move_uploaded_file($_FILES['icon']['tmp_name'], $_SESSION['new_folder'].'/'.$icon)){
						$errors[] = 'Error copying icon.';
					}
				} // end of IF icon present
			} // end of IF folder creation successful
		} // end of IF erors
		
		if (empty($errors)) { // If some images have been successfully uploaded, add details to MySQL:
			$q = "INSERT INTO " . DB_TAB_IMAGES . " (file_name, directory, date) VALUES " . join(", ", $image_data);
			$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
			
			if (!$r){ // Failure.
				$errors[] = 'Error adding image information.';
			}
			else{
				// Add gallery data:
				
				// Get checkbox values
				get_checkbox_checked();

				// Add gallery data:
				$q = "INSERT INTO " . DB_TAB_GALLERIES . " (directory, title, icon, visible, inc_dates) VALUES ('$abbr', '$title', '$icon', '".$_SESSION['visible']."', '".$_SESSION['inc_dates']."')";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

				if (!$r){ // Failure.
					$errors[] = 'Error adding gallery information.';
				}
			}
		}
		
		if (empty($errors)) {// Successfully loaded images and image data - update page mode:
			$_SESSION['load_mode'] = 2;
		}
	}
	elseif($_SESSION['load_mode'] == 2){
		// Load of image details

		// Get image details.
		$image_data = [];
		
		foreach($_POST['i_desc'] as $key => $d){
			// Collect data for images that have not been excluded:
			if(!isset($_POST['i_excl'][$key])){
				
				$i_name = mysqli_real_escape_string($dbc, $_POST['i_name'][$key]);
				$i_desc = mysqli_real_escape_string($dbc, $d);
				$i_date = $_POST['i_date'][$key];
				
				// Update MySQL image data:				
				$q = "UPDATE " . DB_TAB_IMAGES . " SET confirmed=1, date='".$i_date."', description='".$i_desc."' WHERE file_name='".$i_name."' AND directory='".$_SESSION['d']."' LIMIT 1";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
				
				if (!$r){ // Failure.
					$errors[] = 'Error adding image information.';
				} // End of MySQL update error.
			}
			else{
				// Delete unrequired file from directory:
				unlink($_SESSION['new_folder'].'/'.mysqli_real_escape_string($dbc, $_POST['i_name'][$key]));
			} // End of exclude conditional.
		} // End of FOREACH.
		
		// Remove excluded images:
		$q = "DELETE FROM " . DB_TAB_IMAGES . " WHERE confirmed=0 AND directory='".$_SESSION['d']."'";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (!$r){ // Failure.
			$errors[] = 'Error deleting excluded images\' data from MySQL.';
		} // End of MySQL update error. 
				
		// Update MySQL gallery data:				
		$q = "UPDATE " . DB_TAB_GALLERIES . " SET confirmed=1 WHERE directory='".$_SESSION['d']."' LIMIT 1";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (!$r){ // Failure.
			$errors[] = 'Error updating gallery information in MySQL.';
		} // End of MySQL update error. 
		
		if (empty($errors)) {// Successfully updated image data - update page mode:
			$_SESSION['load_mode'] = 3;
		}
		
	} // End of load_mode conditional
} // End of SUBMIT conditional.
else{
	// Set default page mode:
	$_SESSION['load_mode'] = 1;
	
	// Initialise variables:
	$_SESSION['title']='';
	$_SESSION['d']='';
	
	// Get maximum number of images that can be uploaded:
	$q = "SELECT max_imgs_upload FROM " . DB_TAB_SETTINGS . " LIMIT 1";
	$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
	if (@mysqli_num_rows($r) == 1) { // A match was made.
		$row = $r->fetch_assoc();
		$_SESSION['max_imgs_upload'] = intval($row["max_imgs_upload"]);
	}else{
		// Assign default in case MySQL lookup failed:
		$_SESSION['max_imgs_upload'] = 10;
	}
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
		}else{
			$('#title-error-panel').hide();
		}
		if($('#abbr').val().length==0){
			$('#abbr-error-panel').show();
			bReturn = true;
		}else{
			$('#abbr-error-panel').hide();
		}
		if($('#img-upload').val().length==0 && <?php echo $_SESSION['load_mode']==1 ?>){
			$('#images-error-panel').show();
			bReturn = true;
		}else{
			$('#images-error-panel').hide();
		}
		if(bReturn){return false};
	}); //  End of form submission.	
}); // End of document ready.
</script>

<!-- Link to lightbox code -->
<!-- <script type="text/javascript" src="js/lightbox.js"></script>
<link href="css/lightbox.css" rel="stylesheet"> -->

<form action="load.php" method="post" enctype="multipart/form-data" class="w3-container" style="position:relative; top:90px">
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
	<p><div class="ui-widget" id="abbr-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please enter an abbreviated title</strong></p></div></div></p>
	<p><div class="ui-widget" id="images-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please select one or more images to upload</strong></p></div></div></p>
		
<!-- Initial fields display-->
	<div id="fields2" <?php if($_SESSION['load_mode']!=1){echo 'hidden';} ?> />
	<p>
		<label class="w3-text-blue" ng-click="changeTitle()">Title:</label>
		<input class="w3-input w3-border" oninput="changeAbbrTitle()" type="text" name="title" id="title" maxlength="80" value="<?php if(isset($title)){echo $title;} ?>" />
	</p>
	<p>
		<label class="w3-text-blue">Abbreviated title:</label>
		<input class="w3-input w3-border" type="text" name="abbr" id="abbr" maxlength="20"  value="<?php if(isset($abbr)){echo $abbr;} ?>"/>
	</p>	
	<p>
		<label class="w3-text-blue">Gallery Visible to Users?</label>
		<input class="w3-check" type="checkbox" name="visible" checked />
	</p>

	<p>
		<label class="w3-text-blue">Include Dates in Image Descriptions?</label>
		<input class="w3-check" type="checkbox" name="inc_dates" checked />
	</p>
	<p>
		<label class="w3-text-blue">Icon (optional):</label>
		<input class="w3-input w3-border" type="file" name="icon" accept=".ico" />
	</p>
	<p id="upload">
		<label class="w3-text-blue">Images (maximum <?php echo $_SESSION['max_imgs_upload'] ?>):</label>
		<input class="w3-input w3-border" type="file" name="imgs[]" id="img-upload" multiple accept="image/*" />
	</p>
	</div>
	
<!-- Image details fields display-->
	<div><label class="w3-text-blue">Add image data for <?php echo $_SESSION['title'] ?></label><div>
	<div ng-app="myApp" ng-controller="imagesCtrl" class="flex-container" <?php if($_SESSION['load_mode']!=2){echo 'hidden';} ?> />
		<div ng-repeat="x in image_data" class="flex-item">
			<p>
				<div class="w3-card-2">
					<table width="99%">
						<tr>
							<td width="200px">
								<a href="../{{ x.Directory }}/{{ x.Name }}" data-lightbox="{{ x.Name }}"><img src="../{{ x.Directory }}/{{ x.Name }}" style="border: 1px solid #ddd; border-radius: 4px; padding: 5px; width: 200px; display: block; margin-left: auto; margin-right: auto;"></a>
							</td><td>
								<p>
									<input type="text" name="i_name[{{$index }}]" value="{{x.Name}}" hidden />
								</p>
								<p>
									<label class="w3-text-blue">Image {{$index + 1}} Description:</label>
									<input class="w3-input w3-border" type="text" name="i_desc[{{$index }}]" maxlength="256" />
								</p>
								<p>
									<label class="w3-text-blue">Date:</label>
									<input style="padding:8px;display:block;border:none;border-bottom:1px solid #ccc" class="w3-border" type="date" name="i_date[{{ $index }}]" value="{{x.Date}}" size="12" />
								</p>
								<p>
									<label class="w3-text-blue">Exclude:</label>
									<input class="w3-check" type="checkbox" name="i_excl[{{ $index }}]" value="1"/>
								</p>
							</td>
						</tr>
					</table>
				</div>
			</p>
		</div>
	</div>
	
<!-- Next button-->
	<p <?php if($_SESSION['load_mode']==3){echo 'hidden';} ?>>
		<input class="w3-btn w3-blue" type="submit" name="submit" id="submit" value="Next"/>
		<input class="w3-btn w3-blue" type="button" value="Cancel" onClick="window.open('admin.php','_self')" />
	</p>
	
<!-- Confirmation display-->
	<div id="fields2" <?php if($_SESSION['load_mode']!=3){echo 'hidden';} ?> />
		<p <?php if(!empty($errors)){echo 'hidden';} ?>>
			<label class="w3-text-blue">
				<?php 
					echo 'Your new photo gallery \''.$_SESSION['title'].'\' has been successfully created. '; 
					if(!empty($_SESSION['visible']) && $_SESSION['visible']==1){
						// get relative path
						$relPath = str_replace("/admin/load.php","", $_SERVER['REQUEST_URI']);

						echo 'Click on the following link to view the images: 
							<a href="http://'.$_SERVER['SERVER_NAME'].$relPath.'/slideshow.php?d='.$_SESSION['d'].'">
							http://'.$_SERVER['SERVER_NAME'].$relPath.'/slideshow.php?d='.$_SESSION['d'].
							'</a>';

					}else{
						echo 'As the gallery is not currently visible, use \'Amend photo gallery\' when you are ready for it to be revealed.'; 
					}
				?></a>
			</label>
		</p>
		<p>
			<label class="w3-text-blue">To create a new gallery, click <a href="http://<?php echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ?>">here</a></label>
		</p>
	</div>
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>
			Enter the title of your gallery, and optionally amend any other details. Then add your images and, if you like, an icon for the gallery's web page.
			<br><br>
			Once the images have been uploaded, you will have the chance to add a description and a date for each. If you change your mind about one of the images, you can opt to omit it from the gallery.
			<br><br>
			Having entered image data, you will be presented with a link to the new galleryy. The gallery will also automatically be available as a link in the Photos section on the home page.
		</p>
	</div>
</form>

<script>
function changeAbbrTitle(){
	document.getElementById("abbr").value = document.getElementById("title").value.toLowerCase().replace(/ /g, "-").replace(/[^a-z0-9/-]/g, "").slice(0, 20);
}
</script>

<?php include ('includes/footer.html'); ?>