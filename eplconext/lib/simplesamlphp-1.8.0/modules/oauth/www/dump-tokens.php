<?php
// ==========================================================================================
// IMPORTANT NOTICE:
// This file is for debugging/development purposes; 
// it will show a lot of data from the local OAuth-store
// The data that is shown, contains tokens and consumer-secrets; as such, 
// ----------- it is NOT public data ----------
//die("nothing here"); // remove this line to make things visible.
// ==========================================================================================

require_once(dirname(dirname(__FILE__)) . '/libextinc/OAuth.php');

$store = new sspmod_core_Storage_SQLPermanentStorage('oauth');

$tables = array('authorized', 'consumers', 'nonce', 'request', 'access', 'requesttorequest', );

foreach ($tables as $table) {
	$result = $store->getList($table);
	
	print(<<<HERE
<h1>{$table}</h1>
<table>
  <tr>
    <th>key1</th>
    <th>key2</th>
    <th>type</th>
    <th>value</th>
  </tr>
HERE
);
	foreach ($result as $row) {
		echo "<tr>";
		echo "<td>" . $row["key1"] . "</td>";
		echo "<td>" . $row["key2"] . "</td>";
		echo "<td>" . $row["type"] . "</td>";
		if ($table == 'consumers') {
			echo "<td>" . nl2br( htmlentities( print_r($row["value"], true) ) ) . "</td>";
		} else if ($table == 'authorized') {
			echo "<td>" . nl2br( print_r($row["value"], true) ) . "</td>";
		}
		else {
			echo "<td>" . substr($row["value"], 0, 128) . "</td>";
		}
		echo "</tr>";
	}
	
	echo "</table>";
//	echo htmlentities(print_r($result, true));
}


// Set specific value?
function setConsumer($key, $secret, $name, $description, $owner = 'admin') {
	global $store;
	
	$consumerdefs = array(
		'name' => $name,
    	'description' => 'description',
		'key' => $key,
		'secret' => $secret,
		'owner' => $owner
	);
	
	$store->set('consumers', $key, '', $consumerdefs);
}


function storeRequestToken($token, $consumer, $value) {
	global $store;
	$store->set('request', $token, $consumer, $value);
}

function lookupRequestToken($token) {
	global $store;
	return $store->get('request', $token, null);
}

function storeAccessToken($token, $consumer, $value) {
	global $store;
	$store->set('access', $token, $consumer, $value);
}


//setConsumer("foodle-experimental-key", 
//	"foodle-experimental-" . SimpleSAML_Utilities::stringToHex(SimpleSAML_Utilities::generateRandomBytes(3)),
//	"Foodle Experimental",
//	"Foodle Experimental (Mark Dobrinic)",
//	"admin"
// );
/*
setConsumer("consumer_key_etherpad", 
        "consumer_secret_etherpad",
        "portal.dev.surfconext.nl",
        "Ontwikkelomgeving Shindig OpenSocial",
        "admin"
 );
*/
storeAccessToken("", "consumer_key_etherpad", "");

storeRequestToken("a", "b", "value");
storeRequestToken("a", '', "value-two");
$v = print_r(lookupRequestToken("a") ,true);
echo "\n\n\n<p>v = {$v}<br/>\n</p>";



?>
