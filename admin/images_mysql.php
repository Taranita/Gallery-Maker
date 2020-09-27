<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require ('includes/taranita_config.inc.php'); 
require ('includes/php_functions.inc.php'); 
require (MYSQL);

// Initialize a session:
ini_set('session.use_only_cookies', 1);
session_name('TaranitaAdmin' . TITLE);
session_start();

$directory = $_SESSION['d'];

$query = "SELECT file_name, date, description FROM " . DB_TAB_IMAGES . " WHERE directory = '".$directory."'";

if($_SESSION['confirmed']=='1'){
	$query .= " AND confirmed = 1 ORDER BY sort_order, date ASC, file_name";
}else{
	$query .= " AND confirmed = 0";
}

$result = $dbc->query($query);

$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}
    $outp .= '{"Name":"'  . $rs["file_name"] . '",';
    $outp .= '"Date":"'  . $rs["date"] . '",';
    $outp .= '"Description":"'  . remove_commas_semi_colons($rs["description"]) . '",';
    $outp .= '"Directory":"'  . $directory . '"}';
}
$outp ='{"images":['.$outp.']}';
$dbc->close();

echo($outp);
?>