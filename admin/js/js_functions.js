// variables
var images;
var pwd;
var pht;
var prop;
var cur;
var oldImage;

/* Validate email address
 */
function is_valid_email(email){
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}

/* Replace commas and semi-colons with safe characters.
 */
function put_back_commas_semi_colons(txt){
	txt = txt.replace('&#039;', "'");
	return txt.replace('&quot;', '"');
}

// Return string to the right of specified substring
function right_of(txt, targetTxt){
	pos = txt.indexOf(targetTxt);
	if(pos == -1){
		return;
	};

	return txt.substring(pos+targetTxt.length);
}

function indexSetUp()
{
  images = document.getElementsByClassName("bgPics"); 

  pwd = document.images[0].width;
  pht = document.images[0].height;
  prop = pwd/pht;

  // Set opacity of all images to 1 
  for (var i = 0; i < images.length; ++i){ 
      images[i].style.opacity = 0; 
      images[i].style.zIndex = 0;
  } 

  //size background images correctly
  indexSizePic()

  // cur specifies the index of the current image
  cur = Math.floor(Math.random() * images.length)
  images[cur].style.opacity = 1; 
  images[cur].style.zIndex = 2;

  // oldImage specifies the index of the previous image
  oldImage = cur - 1;
  oldImage = (oldImage < 0 ? images.length - 1 : oldImage)

  // changeImage function changes the image 
  setInterval(changeImage, 10000); 
}

function indexSizePic()
{
  //size to width?
  wwd = document.body.clientWidth;
  wht = document.body.clientHeight;

  for (var i = 0; i < images.length; ++i){ 
    if((wwd/wht) > prop) {
      //set width
      images[i].width = wwd;

      //set height
      var ht = wwd / prop;
      images[i].height = ht;
      images[i].style.left = 0
      images[i].style.top = (wht - ht) / 2;
      }
    else
      {
      //set width
      images[i].height = wht;

      //set height
      var ht = wht * prop;
      images[i].width = ht;
      images[i].style.left = (wwd - ht) / 2;
      images[i].style.top = 0;
    }
  }
}

// async function changeImage(){ 
function changeImage(){ 

	// Stores index of next image 
	var nextImage = (1 + cur) % images.length; 
	
	images[cur].style.zIndex = 2; 
	images[nextImage].style.zIndex = 1; 
	images[nextImage].style.opacity = 1; 
	images[oldImage].style.zIndex = 0;
	images[oldImage].style.opacity = 1;
	
	transition(cur);
	
	// Set cur to nextImage 
	oldImage = cur;
	cur = nextImage;
}
	  
// This function chnages the opacity of current image at regular intervals
function transition(curImg) { 
	return new Promise(function (resolve, reject) { 

		// del is the value by which opacity is decreased every interval 
		var del = 0.01; 

		// id stores ID of setInterval, this ID will be used later to clear/stop setInterval after we are done changing opacity 
		var id = setInterval(changeOpacity, 10); 

		// changeOpacity() decreasing opacity of current image by del.
		// When opacity reaches to 0, we stops/clears the setInterval and resolve the function 
		function changeOpacity() { 
			images[curImg].style.opacity -= del; 
			if (images[curImg].style.opacity <= 0) { 
				clearInterval(id); 
				resolve(); 
			} 
		} 
	}) 
} 

function Over(prefix)
{
document.getElementById(prefix + 'Text').style.color='white';
document.getElementById(prefix + 'Block').style.background='#004000';
$("#" + prefix + "Table").fadeIn(500);
}

function Out(prefix)
{
document.getElementById(prefix + 'Text').style.color='black';
document.getElementById(prefix + 'Block').style.background='white';
$("#" + prefix + "Table").fadeOut(500);
}

function OutAll(prefix)
{
if(prefix!='p'){Out('p')};
if(prefix!='l'){Out('l')};
if(prefix!='b'){Out('b')};
}

function IsMobilePlatform(){
	return (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));
}

// launch the help dialogue box
function launchHelp(){
  $( "#dialogHelp" ).dialog( "open" );
}

// compute width of Help dialogue box
function helpDlgWidth(){
  $minWidth = document.body.clientWidth - 200;
  if($minWidth > 800){$minWidth = 800};
  return $minWidth;
}

// Help Dialog box function
$( function() {
  $( "#dialogHelp" ).dialog({
    autoOpen: false,
    dialogClass: "no-close",
    resizable: true,		
    minWidth: helpDlgWidth(),
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