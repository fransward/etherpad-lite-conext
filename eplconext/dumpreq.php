<?php
require_once("lib/all.php");
require_once("include/config.php");

if (! DEV_MODE) {
	exit();
}

/* ================================================ */
/* some debugging: */
//$body='';
//$fh   = @fopen('php://input', 'r');
//if ($fh) { while (!feof($fh)) {
//    $s = fread($fh, 1024);
//    if (is_string($s)) {
//      $body .= $s;
//    }
//  }
//  fclose($fh);
//}
$body2 = @file_get_contents('php://input');


$file = './dumpreq';
$contents = "get:\n" . print_r($_GET, true) . "\n\n";
$contents .= "headers:\n" . print_r(getallheaders(), true) . "\n\n";
$contents .= "post:\n" . print_r($_POST, true) . "\n\n";
$contents .= "cookie:\n" . print_r($_COOKIE, true) . "\n\n";
$contents .= "reqbody:\n" . $body . "\n\n";
$contents .= "reqbody2:\n" . $body2 . "\n\n";

if ($j = json_decode($body2)) {
	$contents .= "- as JSON:\n" . print_r($j, true) . "\n\n";
}



file_put_contents($file, $contents);
/* ================================================ */

/* dump into Storage? */
$storage = new EPLc_Storage('dumpreq');

$storage->set('dumps', 'key'.microtime(), null, $contents);

$alltables = array('userdata', 'dumps');

foreach ($alltables as $table) {
	$l = $storage->getList($table);
	print("<table>");
	print("<tr><th colspan=4>{$table}</th></tr>");
	if (is_array($l) && count($l)>0) {
		foreach ($l as $row) {
			print "<tr><td>{$row["key1"]}</td>";
			print "<td>{$row["key2"]}</td>";
			print "<td>{$row["type"]}</td>";
			print "<td>" . nl2br( print_r($row["value"], true) ) . "</td>";
			print "</tr>";
		}
	} else {
		print ("<tr><td colspan=4><i>No data in table</i></td></tr>");
	}
	
	print("</table>");
}


?>
dumpreq &copy; Cozmanova.
