<?php
require_once('osapi/osapi.php');
/* SimpleSAMLphp */
require_once('simplesamlphp-1.8.0/lib/_autoload.php');
// force loading OAuth library:
// new sspmod_oauth_OAuthServer();

/* GroupRel library -- include only for Model classes */
require_once('GroupRel/_include.php');

/* EtherpadLite Client with some support functionality added in subclass */
require_once('epl-client/etherpad-lite-client.php');

class MyEtherpadLiteClient extends EtherpadLiteClient {
	function __construct() {
		parent::__construct(ETHERPADLITE_APIKEY, ETHERPADLITE_BASEURL);
	}
	
	/**
	 * retrieve group-id and pad-id from group-padname
	 * @param $padname padname, formatted like g.[^\$]$[$padname]
	 */
	static function splitGrouppadName($padname) {
		$parts = explode('$', $padname);
		if (sizeof($parts) == 2) {
			return array($parts[0], $parts[1]);
		} else {
			return array(null, $padname);
		}
	} 
}
?>