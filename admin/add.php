<?php # add.php
// This is the add images to an existing photo gallery page
$page_title = 'Add Images to an Existing Photo Gallery';
include ('includes/header.html');

if (!isset($_SESSION['user_id'])) {
	// User is not authenticated - redirect
	redirect_user();
}

// Set session variable so that unconfirmed images are returned from MySQL:
$_SESSION['confirmed']='0';

require (MYSQL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	// Initialise arrays and variables:
	$errors = [];
	$image_data = [];
	$images_loaded = false;
	
	if($_SESSION['load_mode'] == 1){
		// Initial validation and upload.
		
		// Check for attachments(s):
		if ($_FILES['imgs']['name'][0].$_FILES['icon']['name'] == '') {
			$errors[] = 'Please select one or more images to upload';
		}
		
		// Check for exceeding maximum number of images:
		if (count($_FILES['imgs']['name']) > $_SESSION['max_imgs_upload']) {
			$errors[] = 'You have tried to upload '.count($_FILES['imgs']['name']).' images, a maximum of '.$_SESSION['max_imgs_upload'].' images may be uploaded';
		}
		
		if (empty($errors)) { // If everything's OK.
			// get folder for images:
			$_SESSION['folder'] = '../'.$_SESSION['d'];
			
			// Copy all files into folder:	
			if($_FILES['imgs']['name'][0]!=''){
				// Get existing highest sort order for gallery
				$q = "SELECT MAX(sort_order) as max_sort_order FROM " . DB_TAB_IMAGES . " WHERE directory='".$_SESSION['d']."'";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
				$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
				$max_sort_order = $row['max_sort_order'];

				foreach($_FILES['imgs']['tmp_name'] as $key => $img_name){
					if(!move_uploaded_file($img_name, $_SESSION['folder'].'/'.$_FILES['imgs']['name'][$key])){
						$errors[] = 'Error copying image \''.$img_name.'\'.';
					}
					else{
						// Image successfully uploaded - add details to arrays for subsequent MySQL table insertion
						if($max_sort_order!=0){
							$max_sort_order++;
						}
						
						$image_data[] = "('".$_FILES['imgs']['name'][$key]."', '".$_SESSION['d']."', '".date("Y-m-d", filemtime($_SESSION['folder'].'/'.$_FILES['imgs']['name'][$key]))."', ".$max_sort_order.")";
					}
				} // end of FOREACH				
				//Set icon loaded flag:
				$images_loaded = true;
			} // end of IF iamges exist
			
			// Copy icon, if present:
			$icon = $_FILES['icon']['name'];
			if ($icon != '') {
				// Remove any existing icon file:
				if($_SESSION['icon']!=''){
					unlink($_SESSION['folder'].'/'.mysqli_real_escape_string($dbc, $_SESSION['icon']));
				}
				
				if(!move_uploaded_file($_FILES['icon']['tmp_name'], $_SESSION['folder'].'/'.$icon)){
					$errors[] = 'Error copying icon.';
				}
				
				// Add icon name to gallery data:
				$q = "UPDATE " . DB_TAB_GALLERIES . " SET icon='$icon' WHERE directory='".$_SESSION['d']."' LIMIT 1";
				$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

				if (!$r){ // Failure.
					$errors[] = 'Error adding icon name to gallery information.';
				}
			} // end of IF icon present
		} // end of IF errors

		if (empty($errors) && $images_loaded) { // If some images have been successfully uploaded, add details to MySQL:
			// Insert images
			$q = "INSERT INTO " . DB_TAB_IMAGES . " (file_name, directory, date, sort_order) VALUES " . join(", ", $image_data);
			$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
						
			if (!$r){ // Failure.
				$errors[] = 'Error adding image information.';
			}
		}
		
		if (empty($errors)) {// Successfully loaded images and image data - update page mode:
			if($images_loaded){
				// Images (and possibly icon) loaded.
				$_SESSION['load_mode'] = 2;
			}else{
				// Icon only loaded.
				$_SESSION['load_mode'] = 3;
			}
		}
	} // End of load_mode = "1" conditional
	elseif($_SESSION['load_mode'] == 2){
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
				unlink($_SESSION['folder'].'/'.mysqli_real_escape_string($dbc, $_POST['i_name'][$key]));
			} // End of exclude conditional.
		} // End of FOREACH.
		
		// Remove excluded images:
		$q = "DELETE FROM " . DB_TAB_IMAGES . " WHERE confirmed=0 AND directory='".$_SESSION['d']."'";
		$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));
		
		if (!$r){ // Failure.
			$errors[] = 'Error deleting excluded images\' data from MySQL.';
		} // End of MySQL update error. 
		
		if (empty($errors)) {// Successfully updated image data - update page mode:
			$_SESSION['load_mode'] = 3;
		}
		
	} // End of load_mode conditional
} // End of SUBMIT conditional.
else
{
	// Get directory of target gallery:
	$_SESSION['d']=$_GET['d'];
	
	// Set default page mode:
	$_SESSION['load_mode'] = 1;
	
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
	mysqli_free_result($r);
}

// Get gallery data
$q = "SELECT title FROM " . DB_TAB_GALLERIES . " WHERE directory = '".$_SESSION['d']."'";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

if (!$r){ // Failure.
	$errors[] = 'Error getting gallery details.';
}else{
	$row = $r->fetch_assoc();
	$_SESSION['title'] = $row["title"];
}

// Get icon details:
$q = "SELECT icon FROM galleries WHERE directory = '".$_SESSION['d']."'";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

if (!$r){ // Failure.
	$errors[] = 'Error getting gallery icon.';
}else{
	$row = $r->fetch_assoc();
	$_SESSION['icon'] = $row["icon"];
}
	
mysqli_close($dbc);
?>

<!-- Ajax validation -->
<script>
$(function(){
	// Assign an event handler to the form:
	$('#submit').click(function(){
		if($('#img-upload').val().length + $('#icon-upload').val().length == 0 && <?php echo $_SESSION['load_mode']==1?>){
			$('#images-error-panel').show();
			return false;
		}else{
			$('#images-error-panel').hide();
		}
	}); //  End of form submission.	
}); // End of document ready.
</script>

<form action="add.php" method="post" enctype="multipart/form-data" class="w3-container" style="position:relative; top:90px">
	<!-- Dialog box for successful update -->	
	<?php
	if($_SESSION['dialog_box'] == 1){
		echo '<div id="dialog" title="Amend Photo Gallery" style="display: none;">
		<p>The gallery has been successfully amended.</p>
		</div>';
		$_SESSION['dialog_box'] = 0;
	}
	?>
	
	<h2 class="w3-text-blue">Add Images to the Existing Photo Gallery: <?php echo $_SESSION['title']; ?></h2>
	
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
	<p><div class="ui-widget" id="images-error-panel" hidden="true"><div class="ui-state-error ui-corner-all" style="padding: 0.1em;"><p><span class="ui-icon ui-icon-alert" style="margin-left: .3em; margin-right: .3em;"></span><strong> - Please select one or more images to upload</strong></p></div></div></p>
		
<!-- Initial fields display-->
	<div id="fields2" <?php if($_SESSION['load_mode']!=1){echo 'hidden';} ?> />
	<p>
	<label class="w3-text-blue">Icon (optional<?php if($_SESSION['icon']!=''){echo ', note that adding an icon will replace the existing icon';}?>):</label>
	<input class="w3-input w3-border" type="file" name="icon" id="icon-upload" accept=".ico" />
	</p>
	<p id="upload">
	<label class="w3-text-blue">Images (maximum <?php echo $_SESSION['max_imgs_upload'] ?>):</label>
	<input class="w3-input w3-border" type="file" name="imgs[]" id="img-upload" multiple accept="image/*" />
	</p>
	</div>
	
<!-- Image details fields display-->
	<div><label class="w3-text-blue">Add image details:</label><div>
	<div ng-app="myApp" ng-controller="imagesCtrl" class="flex-container" <?php if($_SESSION['load_mode']!=2){echo 'hidden';} ?> />
		<div ng-repeat="x in image_data" class="flex-item">
			<p>
				<div class="w3-card-2">
					<table width="99%">
						<tr>
							<td width="200px">
								<a href="../{{ x.Directory }}/{{ x.Name }}" data-lightbox="{{ x.Name }}"><img src="../{{ x.Directory }}/{{ x.Name }}" style="border: 1px solid #ddd; border-radius: 4px; padding: 5px; width: 200px; display: block; margin-left: auto; margin-right: auto;"></a>
							</td><td width="300px">
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
	
<!-- Buttons-->
	<p <?php if($_SESSION['load_mode']==3){echo 'hidden';} ?>>
		<input class="w3-btn w3-blue" type="submit" name="submit" id="submit" value="Next"/>
		<input class="w3-btn w3-blue" type="button" value="Cancel" onClick="window.open('admin.php','_self')"  <?php if($_SESSION['load_mode']>1){echo 'hidden';} ?>/>
	</p>
	
<!-- Confirmation display-->
	<div id="fields2" <?php if($_SESSION['load_mode']!=3){echo 'hidden';} ?> />
		<p <?php if(!empty($errors)){echo 'hidden';} ?>>
			<label class="w3-text-blue">New images have been successfully added to your photo gallery '<?php 
						// get relative path
						$relPath = str_replace("/admin/add.php","", $_SERVER['REQUEST_URI']);
						
						echo $_SESSION['title'].'\'. Click on the following link to view the images: 
						<a href="http://'.$_SERVER['SERVER_NAME'].$relPath.'/slideshow.php?d='.$_SESSION['d'].'">
						http://'.$_SERVER['SERVER_NAME'].$relPath.'/slideshow.php?d='.$_SESSION['d'].'</a>';?>
			</label>
		</p>
	</div>	
		
	<!-- Help dialog box -->
	<div id="dialogHelp" title="Help" style="display: none;">
		<p>
			Add any images you would like to include in the gallery, and optionally an icon for the gallery's web page.
			<br><br>
			Once the images have been uploaded, you will have the chance to add a description and a date for each. If you change your mind about one of the images, you can opt to omit it from the gallery.
			<br><br>
			Go to the 'Amend photo gallery' page if you want to re-order the images within the gallery.
		</p>
	</div>
</form>

<script>
	var app = angular.module("myApp", [])
		.controller('imagesCtrl', function($scope, $http) {
		try {
			$http.get("images_mysql.php")
			.then(function (response) {$scope.image_data = response.data.images;})
		} catch(error){
			console.log('Error is handled: ', error.name);
		}
	})
	.factory('$exceptionHandler', function() {
    return function(exception, cause) {
      console.log(exception)
    }
  });
</script>

<?php include ('includes/footer.html'); ?>