<?php
/**
 * REST-service endpoint
 * Reports result as JSON-data structure
 * 
 * Format of request:
 * http(s)://.../padmanager.php/$SERVICENAME[/$GROUPNAME][/$argument]...[/$argument]
 * 
 */

require_once("lib/all.php");
require_once("include/config.php");

$request = EPLc_Model_Request::create();

try {
	$manager = new EPLc_Manager();
	$response = $manager->perform($request);

	Logger_Log::debug('Response:[' . print_r($response, true) . ']', 'padmanager.php');
	
	$o = array('result' => ($response->_result ? 'OK' : 'ERROR'));
	if (isset($response->_message)) {
		$o['message'] = $response->_message;
	}
	if (isset($response->_data)) {
		$o['data'] = $response->_data;
	}
	
} catch (Exception $e) {
	$o = array(
			'result' => 'EXCEPTION',
			'message' => $e->getMessage(),
			);
}

header('Content-Type: application/json');

print json_encode($o);

?>