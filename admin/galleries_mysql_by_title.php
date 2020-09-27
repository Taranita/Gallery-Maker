<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require ('includes/taranita_config.inc.php'); 
require (MYSQL);

// Initialize a session:
ini_set('session.use_only_cookies', 1);
session_name('TaranitaAdmin' . TITLE);
session_start();

$query = "SELECT directory, title FROM " . DB_TAB_GALLERIES . " WHERE confirmed = 1 ORDER BY title";

$result = $dbc->query($query);

$outp = "";
while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
    if ($outp != "") {$outp .= ",";}
    $outp .= '{"Title":"'  . $rs["title"] . '",';
    $outp .= '"Directory":"'  . $rs["directory"] . '"}';
}
$outp ='{"galleries":['.$outp.']}';
$dbc->close();

echo($outp);
?>