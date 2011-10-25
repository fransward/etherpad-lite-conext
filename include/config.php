<?php
/** 
 * Application Context:
 * Use session
 */
session_start();


/**
 * Depend on CozmanovaPHPCommons shared features
 */
require_once('../CozmanovaPHPCommons/lib/cpc.php');


/* Load our own libraries */
require_once('GroupRel/OpenSocial/osapiGroupRelProvider.php');
require_once('lib/GroupRel/Controller/OpenSocial/GroupRelationsImpl.php');

// OAuth stuff:
require_once('include/epl_OAuthStorage.php');

/* load application configuration */
$cfgKeys = array( 'SERVER', 'PATH', 'SERVER_URL',
				'DEV_MODE', 
				'SPDEF',
				'OAUTH_CONFIG_requestTokenUrl', 'OAUTH_CONFIG_authorizeUrl', 'OAUTH_CONFIG_accessTokenUrl', 
				'OAUTH_CONFIG_restEndpoint', 'OAUTH_CONFIG_rpcEndpoint',
				'OAUTH_CONFIG_consumerKey', 'OAUTH_CONFIG_consumerSecret',
				'ETHERPADLITE_APIKEY', 'ETHERPADLITE_BASEURL', 'ETHERPADLITE_SESSION_DURATION',
				'ETHERPADLITE_PADBASEURL'
				);

// Load keys from .ini file:
$iniKeys = parse_ini_file( 'include/config.ini' );
foreach ($iniKeys as $key => $val ) {
	define( $key, $val );
}

// Check definitions integrity
foreach ( $cfgKeys as $key ) {
	if ( ! defined( $key ) ) {
		echo 'CONFIG::' . $key . ' is missing in config.ini';
		exit();
	} 
}


/*
 * Add classes directory to the autoloader
 */
function __EPLconext_autoload($sClassname) {
	/* tries to load EPLc_[classname] from include/classes/[classname].php */
	$a = explode('_', $sClassname);
	if (count($a) > 1) {
		array_shift($a);	/* remove first element */
		$c = join('/', array_slice($a, 0, count($a)));
		$f = 'include/classes/' . $c . '.php';
		Logger_Log::trace("including $c", "__EPLconext_autoload");
		if (file_exists($f)) {
			include_once($f);
		}
	}
}

spl_autoload_register('__EPLconext_autoload');


/* instantiate configured DAO-Factory */
//$fc = DAO_FACTORY;
//$DAOFactory = new $fc(DB_SERVER, DB_DATABASE, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
?>