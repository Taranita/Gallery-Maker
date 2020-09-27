<?php # php_functions.inc.php
// This page defines shared functions

/* This function determines an absolute URL and redirects the user there.
 * The function takes one argument: the page to be redirected to.
 * The argument defaults to index.php.
 */

function redirect_user(){
	
	//Redirect the user:
	header("Location: ../index.php");
	exit(); // Quit the script.
	
} // End of redirect_user() function.


// This function destroys the session and cookie stuff when user logs out or is timed out.
function end_session(){	
	// Log out the user.
	$_SESSION = array(); // Destroy the variables.
	session_destroy(); // Destroy the session itself.
	setcookie (session_name(), '', time()-3600); // Destroy the cookie.
}


// Confirmation dialog box for removal of gallery.
function remove_gallery_dialog(){
	echo '<div id="dialog-confirm" title="Remove Photo Gallery">
	<p>Are you sure you want to remove this photo gallery?</p>
	</div>';
}


// Replace commas and semi-colons with safe characters.
function remove_commas_semi_colons($txt){
	$txt = str_replace("'", '&#039;', $txt);
	return str_replace('"', '&quot;', $txt);
}


// Replace safe characters with commas and semi-colons.
function put_back_commas_semi_colons($txt){
	$txt = str_replace('&#039;', "'", $txt);
	return str_replace('&quot;', '\"', $txt);
}


function get_checkbox_checked(){
	
	//Get values of visible and include dates checkboxes
	if(empty($_POST['inc_dates'])){
		$_SESSION['inc_dates'] = 0;
	}else{
		$_SESSION['inc_dates'] = 1;
	};
	
	if(empty($_POST['visible'])){
		$_SESSION['visible'] = 0;
	}else{
		$_SESSION['visible'] = 1;
	};

} 

function set_up_index_background_images($directory){
	// get background images
	$bgImages = scandir($directory);

	//remove dot and double dot entries
	return array_diff($bgImages, array('..', '.'));
}

function startsWith( $haystack, $needle ) {
	$length = strlen( $needle );
	return substr( $haystack, 0, $length ) === $needle;
}

function endsWith( $haystack, $needle ) {
   $length = strlen( $needle );
   if( !$length ) {
	   return true;
   }
   return substr( $haystack, -$length ) === $needle;
}

function left_of( $haystack, $needle ) {
   $pos = strpos ($haystack , $needle);
   if( !$pos ) {
	   return;
   }
   return substr( $haystack, 0, $pos) ;
}


?>
