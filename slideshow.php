<?php # slideshow.php
// This is the page that displays a slideshow of images.
// Which slideshow is determined by the d parameter passed with the URL, 
// which is the directory of the gallery of images, e.g. 'slideshow.php?d=far-east-2017
require ('admin\includes\php_functions.inc.php'); 
require ('admin\includes\taranita_config.inc.php'); 
require (MYSQL_SLIDESHOW);


// Initialize a session:
ini_set('session.use_only_cookies', 1);
session_name('TaranitaAdmin' . TITLE);
session_start();

// Set session variables:
$_SESSION['confirmed']='1';
$_SESSION['d']=$_GET['d'];

// Get gallery data:
$d = $_GET['d'];
$q = "SELECT title, icon, inc_dates FROM " . DB_TAB_GALLERIES . " WHERE directory = '".$d."' AND confirmed = 1 LIMIT 1";
$r = mysqli_query ($dbc, $q) or trigger_error("Query: $q\n<br />MySQL Error: " . mysqli_error($dbc));

// Report no match:
if(mysqli_num_rows($r) == 0){
	echo 'Invalid gallery selection.';
	exit();
}

// Collect gallery data:
$row = mysqli_fetch_array($r, MYSQLI_ASSOC);
$title = $row['title'];

//Compute icon file path - if no icon specified, use default:
if($row['icon'] == ''){
	$icon_file_name = 'taranita.ico';
}else{
	$icon_file_name = $d."/".$row['icon'];
}

//Set variable depending upon whether dates should be shown or not.
if($row['inc_dates']==1){
	$inc_dates = true;
}else{
	$inc_dates = false;
}

// Get image data:
$q = "SELECT file_name, date, description FROM " . DB_TAB_IMAGES . " WHERE directory = '".$d."' AND confirmed = 1 ORDER BY sort_order, date ASC, file_name";
$r = $dbc->query($q);

$outp = "";
while($rs = $r->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}
    $outp .= '{"Name":"'  . $rs["file_name"] . '",';
    $outp .= '"Description":"'  . put_back_commas_semi_colons($rs["description"]) . '",';
	
    if($inc_dates){
		if($rs["description"] != ""){
			$outp .= '"Date":" - '  . date("j F Y", strtotime($rs["date"])) . '",';
		}else{
			$outp .= '"Date":"'  . date("j F Y", strtotime($rs["date"])) . '",';
		}
	}else{
		$outp .= '"Date":"",';
	}
	
	$outp .= '"Directory":"'  . $d . '"}';
}
$outp ='{"images":['.$outp.']}';

//Get count of rows:
$numResults = $r->num_rows;

$dbc->close();
mysqli_free_result($r);
?>

<!DOCTYPE html>
<html>
<head>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-119899377-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-119899377-1');
</script>

<title><?php echo $title ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="admin\js\js_functions.js"></script>
<link rel="stylesheet" href="<?php echo w3_stylesheet_path ?>">
<link rel="stylesheet" href="taranita.css">
<link rel="shortcut icon" href="<?php echo $icon_file_name ?>">

</head>

<body class="blackBody" onresize="resizer(slideIndex);" id="docBody">
<div id="Header" class="bordered">
	<div class="mainhdg"><a href="index.php" class="whiteText">taranita.com</a><i>&nbsp;&nbsp;&nbsp;<?php echo $title ?></></div>
</div>

<div>
	<div>
		<div id="imgDiv"></div>	
		<p id="imgDesc" class="w3-container outer-text"></p>
	</div>
	
	<button class="t-btn" onclick="plusDivs(-1)" id="btnPrev"><nobr>&lsaquo; Prev</nobr></button>
	<button class="t-btn" onclick="plusDivs(1)" id="btnNext"><nobr>Next &rsaquo;</nobr></button>

	<!-- load 'loading.gif' -->
	<img src='admin\images\loading.gif' id="loadingImg">
	
	<!-- load number buttons -->
	<?php  
	echo '<div id="btn-bar" class="btn-bar">';
	for ($x = 1; $x <= $numResults; $x++){
		echo '
		<span style="align:center;">
		<button class="btn" id="btn'.($x - 1).'" onclick="showImg('.($x - 1).');" onMouseOver="showBtnDesc('.($x - 1).')" onMouseOut="hideBtnDesc('.($x - 1).');">'.$x.'</button>
		</span>';
	}
	echo '</div>';	
	?>

	<!-- create div for button descriptions -->
	<div id="txtBxDiv" class="txtBx"></div>
</div>

<script>
// get default values
var mobile = IsMobilePlatform();
var img_data;
var current_image;
var swd;
var sht;
var slideIndex = 0;
loadImageData();
positionButtons();
sizeButtons();
document.getElementById('btn0').click();

function sizeButtons(){
	// get all buttons
	btns = document.getElementsByClassName("btn");
	var btnMaxWidth = 0;

	// find kargest width
	for (const k of btns){
		if (k.offsetWidth > btnMaxWidth) {
			btnMaxWidth = k.offsetWidth;
		}
	}

	// set all buttons to largest width
	for (const k of btns){
		k.style.width = btnMaxWidth + "px";
	}
}

// Show descriptions when user hovers over the number buttons:
function showBtnDesc(n){
	// Get current button position:
	var btn = document.getElementById('btn' + n);
	var lft = btn.offsetLeft + 10;
	var fromLeft = true;

	// if (left > swd - 200){
	if (lft > swd / 2 ){
		lft = swd - lft;
		fromLeft = false;
	}

	// Add border to number box
	btn.className = btn.className += " bordered-btn";

	// Create text box	
	var dv = document.getElementById('txtBxDiv');
	while (dv.firstChild) {
  	dv.removeChild(dv.firstChild);
	}
	var para = document.createElement("p");
	var txtBx = document.createTextNode(img_data.images[n].Description);
	para.appendChild(txtBx);
	dv.appendChild(para);

	if(fromLeft){
		dv.style.right = null;
		dv.style.left = lft + 'px';
	}else{
		dv.style.left = null;
		dv.style.right = lft + 'px';
	}
	
	dv.style.top = sht + 45 + 'px';
	para.classList.add("btnDesc");
}

// Hide descriptions when user no longer hovers over the number buttons:
function hideBtnDesc(n){
	var dv = document.getElementById('txtBxDiv');
	while (dv.firstChild) {
  	dv.removeChild(dv.firstChild);
	}

	// Remove border from number box	
	var btn = document.getElementById('btn' + n);
	btn.className = btn.className.replace(" bordered-btn", "");
}

function getWindowSize(){
	bod = document.getElementById('docBody');
	if(mobile){
		swd = document.body.clientWidth;
		sht = window.screen.height - 150;
	}else{
		swd = window.innerWidth;
		sht = window.innerHeight - 150;
	}
}

function loadImageData(){
	img_data = <?php echo $outp ?>;
}

function resizer(n){
	positionButtons();
	sizeImage();
}

function positionButtons(){
	// position buttons:
	getWindowSize();
	if(mobile){
		var btn_tp = sht/2 + 'px';
	}else{	
		var btn_tp = sht/2 - 20 + 'px';
	} 
	
	var btn = document.getElementById('btnPrev');
	btn.style.left = '10px';
	btn.style.top = btn_tp;
	var btn = document.getElementById('btnNext');
	btn.style.left = swd - 85 + 'px';
	btn.style.top = btn_tp;
	
	// number buttons:	
	var bar = document.getElementById("btn-bar");
	if(mobile){
		bar.style.visibility = 'hidden';
	}else{
		bar.style.top = sht + 85 + 'px'; 
	}

	// position loading image:	
	var ldImg = document.getElementById("loadingImg");
	ldImg.style.width = '32px';  
	ldImg.style.position = 'absolute';  
	if(mobile){ 
		ldImg.style.left = swd/2 - 20 + 'px'; 
		ldImg.style.top = btn_tp;
	}else{	 
		ldImg.style.left = swd/2 - 25 + 'px'; 
		ldImg.style.top = btn_tp;
	}
}

function showImg(n){
	// Ensure selected number does not exceed bounds(i.e. when using + and - buttons):
	var dots = document.getElementsByClassName("btn");
	if (n >= dots.length) {
		slideIndex = 0;
	} else if (n < 0) {
		slideIndex = dots.length - 1;
	} else{
		slideIndex = n;
	}
	
	// Update number buttons
	if(!mobile){
		for (i = 0; i < dots.length; i++) {
			dots[i].className = dots[i].className.replace(" w3-red", "");
		}
		dots[slideIndex].className += " w3-red";
	}

	// update text
	var fieldNameElement = document.getElementById("imgDesc");
	while(fieldNameElement.childNodes.length >= 1) {
		fieldNameElement.removeChild(fieldNameElement.firstChild);
	}
	descTxt = img_data.images[slideIndex].Description + img_data.images[slideIndex].Date;
	fieldNameElement.appendChild(fieldNameElement.ownerDocument.createTextNode(descTxt));

	// update image:
	var imgElement = document.getElementById("imgDiv");
	while(imgElement.childNodes.length >= 1) {
		imgElement.removeChild(imgElement.firstChild);
	}

	// Show waiting image:
	var ldImg = document.getElementById("loadingImg");
	ldImg.style.visibility = "visible";

	var DOM_img = document.createElement("img");
	DOM_img.style.visibility = "hidden";
  	DOM_img.src = '<?php echo $d ?>/' + img_data.images[slideIndex].Name;
	imgElement.appendChild(DOM_img);
	DOM_img.onload = function(){
		sizeImage();
		ldImg.style.visibility = "hidden";
		DOM_img.style.visibility = "visible";
	}	
}

function plusDivs(n) {
  showImg(slideIndex+=n);
}

function sizeImage()
{
	// get image:
	var imgElement = document.getElementById("imgDiv");
	var current_image = imgElement.children[0];

	getWindowSize();
	pwd = current_image.naturalWidth;
	pht = current_image.naturalHeight;
	prop = pwd/pht;

	if(pwd > swd && pht > sht)
	{
//both dimensions too small
	 if(pwd / swd > pht / sht)
	 {
//width smallest
	 	current_image.width = swd;
	 	current_image.height = pht * swd / pwd;
	 }
	else		
	 {
//height smallest
	 current_image.height = sht;
	 current_image.width = pwd * sht / pht;
	 }
	}
	else if(pwd > swd)
//width only too small
	{
	 current_image.width = swd;
	 current_image.height = pht * swd / pwd;
	}
	else if(pht > sht)
//height only too small
	{
	 current_image.height = sht;
	 current_image.width = pwd * sht / pht;
	}
	else
//size adequate
	{
	 current_image.height = pht;
	 current_image.width = pwd;
	}

// horizontal position:
current_image.style.position = 'absolute'; 
current_image.style.left = (swd - current_image.width) / 2 + 'px';
current_image.style.top = '85px';
}
</script>

</body>
</html>