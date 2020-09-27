<?php // index_m.php
require ('admin/includes/taranita_config.inc.php'); 
require ('admin/includes/php_functions.inc.php'); 

$directory = 'backgroundImages';
$bgImages = set_up_index_background_images($directory);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>

<!-- Global Site Tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=40471551-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '40471551-1');
</script>

<script src="<?php echo angular_path ?>"></script>
<script src="<?php echo jquery_path ?>"></script>
<script src="<?php echo js_functions_path ?>"></script>

<title>T&#257;r&#257;n&#299;ta's website, member of the Triratna Buddhist Order</title>
<meta name="description" content="The website of Taranita, member of the Triratna Buddhist Order">
<meta name="keywords" content="western buddhist order, wbo, fwbo, meditation, buddhist, metta bhavana, mindfulness, retreat, bristol, spirituality, spiritual, vessantara, family, chipping sodbury, iron acton, metal, rammstein, radiohead, led zeppelin, nine inch nails, year zero, photos, photographs"><meta name="robots" content="index, follow">
<meta name="verify-v1" content="b+Y8GQ/QV6wtwxh4PCP9rF7zCUd/LZHyrLuLYkC7N5E=" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="taranita.ico">
<link rel="stylesheet" href="admin\css\index.css">

<script>

var mobile = IsMobilePlatform();

// Check if on mobile device
function isMobile()
{
if (!mobile) {
   document.location = "index.php"
   }
}

</script>
<p>
<style>
a {font-size: 24pt}
</style>

</head>
<body onresize="indexSizePic();" onLoad="indexSetUp(); isMobile()" scroll="no" style="overflow: hidden">

<!-- Display background images -->
<div class=bgImages>
  <?php 
    foreach ($bgImages as &$value) {
		echo '<img src="'.$directory.'/'.$value.'" width=1420 height=1065 style="opacity:0" class="bgPics">
		';
    }
  ?>
</div>

<div class="bordered" style="position:absolute; left:20px; top:20px; width:430px; height:50px">
<span class="mainhdg" style="position:relative; left:6px; top:1px">T&#257;r&#257;n&#299;ta's Website</span>
</div>

<div class="bordered" id="pBlock" style="position:absolute; left:20px; top:110px; width:205px; height:50px"
 onmouseover="OutAll('p'); Over('p')">
<span class="hdg" id="pText" style="position:relative; left:6px; top:1px">Photos</span>
</div>

<div class="bordered" id="lBlock" style="position:absolute; left:20px; top:200px; width:205px; height:50px"
 onmouseover="OutAll('l'); Over('l')">
<span class="hdg" id="lText" style="position:relative; left:6px; top:1px">Links</span>
</div>

<div class="bordered" id="bBlock" style="position:absolute; left:20px; top:290px; width:205px; height:50px"
 onmouseover="OutAll('b'); Over('b')">
<span class="hdg" id="bText" style="position:relative; left:6px; top:1px">Buddhism</span>
</div>

<!-- PHOTOS TABLE-->
<div ng-app="myApp" ng-controller="galleryCtrl">
<table id="pTable" class="lines" style="position:absolute; left:250px; top:110px; width:400px; display:none;"
onmouseover="Over('p')"
onmouseout="OutAll('p')">

 <tr ng-repeat="x in gallery_data">
	<td class="noBorders">
		<table class="noBorders">
			<tr class="lnk">
				<td><a href="slideshow.php?d={{x.Directory}}">{{x.Title}}</td>
			</tr>
			<tr class="bl" class="noBorders"><td></td></tr>
		</table>
	</td>
</tr>

<tr class="lnk"><td><a href="border2016">Along the Border 2016</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="ior2016">India Order retreat plus Delhi and Patna 2016</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="japan2015">Japan 2015</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="mallorca2014">Mallorca 2014</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="usa2013">NW USA 2013</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="india2013">India 2013 - International Order Convention and Nagaloka</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="india2012">India, 2012</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="sw-usa-2010">South Western USA, 2010</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="cornwall2009">Cornwall 2009</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="india2009">India 2009 - WBO/TBM Convention, Bodhgaya & Kalimpong</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="east-coast-2008">East Coast 2008</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="andalucia2007">Andaluc&iacute;a 2007</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="pnw2006">Pacific Northwest 2006</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="retreat-june-2005">Retreat at Castell, June 2005</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="nz2004">New Zealand 2004</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="ordination-retreat">T&#257;r&#257;n&#299;ta and Dharmapakshin's Ordination Retreat</a></td></tr>
</table>

<!-- LINKS TABLE-->
<table id="lTable" class="lines"
style="position:absolute; left:250px; top:200px; width:400px; display:none;
 onmouseover="Over('l')"
 onmouseout="OutAll('l')">
<tr class="lnk"><td><a href="http://www.facebook.com/taranita.jobbins">My Facebook</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://www.bristol-buddhist-centre.org">Bristol Buddhist Centre</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://thebuddhistcentre.com">Triratna Buddhist Community</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://thebuddhistcentre.com/news">Triratna News</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://www.freebuddhistaudio.com">Free Buddhist Audio</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://www.rammstein.de/en">Rammstein</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://www.nin.com">Nine Inch Nails</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="http://www.qotsa.com">Queens of the Stone Age</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="admin/login.php">Admin</a></td></tr>
</table>

<!-- BUDDHISM TABLE-->
<table id="bTable" class="lines"
style="position:absolute; left:250px; top:290px; width:400px; display:none;"
 onmouseover="Over('b')"
 onmouseout="OutAll('b')">
<tr class="lnk"><td><a href="http://www.freebuddhistaudio.com/browse?s=taranita&t=audio&az=false&textlist=true">
Talks on Free Buddhist audio</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="meditationcd">Guided Meditations</a></td></tr>
<tr class="bl"><td></td></tr>
<tr class="lnk"><td><a href="files/greentara.html" target="_blank">A Vision of Green Tara</a></td></tr>
</table>

<!-- get gallery data -->
<script>
var app = angular.module("myApp", []);
app.controller('galleryCtrl', function($scope, $http) {
   $http.get("admin/galleries_mysql.php")
   .then(function (response) {$scope.gallery_data = response.data.galleries;});
});
</script>

<!-- Last modified 20 December 2018 -->

</body></html>
