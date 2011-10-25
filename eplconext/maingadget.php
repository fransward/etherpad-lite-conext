<?php
require_once("lib/all.php");
require_once("include/config.php");

require_once( "include/epl_OAuthStorage.php" );

$userdata = array();
$store = new epl_OAuthStorage();
$server = new sspmod_oauth_OAuthServer($store);
$server->add_signature_method($hmac_method = new OAuthSignatureMethod_HMAC_SHA1());
$server->add_signature_method($plaintext_method = new OAuthSignatureMethod_PLAINTEXT());
$server->add_signature_method($rsa_method = new sspmod_oauth_OAuthSignatureMethodRSASHA1());

$proxied_content = false;


$storage = new EPLc_Storage('maingadget');
// Get userattributes from store
$userattributes = null;
$userdata_token = Web_CGIUtil::get_prf_argument($_GET, null, null, 'udtok', null);
if ($userdata_token != null) {
	$record = $storage->get('userdata', $userdata_token, null);
	$userattributes = $record['value'];
}

if ($userattributes != null) {
	// Logger_Log::debug("Retrieved from store: ". print_r($userattributes, true), 'maingadget.php');
	// only allow one-time-use, so clear from session now:
	$storage->remove('userdata', $userdata_token, null);
	$storage->removeExpired();	// maintenance/cleanup
} else {
	try {
		$oAuthRequest = OAuthRequest::from_request();
		
		// Logger_Log::debug(print_r($oAuthRequest,true));
		
		list( $consumer, $token ) = $server->verify_request($oAuthRequest);
		/* token should be empty, as this is 2-legged-oauth */
		/* Also: when we reached this point, the request was verified (signature) */
	
		// only allow when proxied-content is set:
		$proxied_content = $oAuthRequest->get_parameter('opensocial_proxied_content');
		if ($proxied_content != 1) {
			Logger_Log::debug('Invalid call - not from embedded OpenSocial gadget!');
			throw new Exeption('Invalid call - not from embedded OpenSocial gadget!');
		}
		$userattributes = array(
				'conext' => $oAuthRequest->get_parameter('opensocial_owner_id'),
				'urn:mace:dir:attribute-def:cn' => '',
				'opensocial_instance_id' => $oAuthRequest->get_parameter('opensocial_instance_id'),
			);
		
	} catch (OAuthException $e) {
		/* OK; no OAuth call; do not attempt SimpleSAML authentication
		 */
		Logger_Log::warn("No OAuth authentication established: " + $e->getMessage(), "maingadget.php");
		exit();
	}
	
	// store userattributes by a generated random-token
	$userdata_token = String_Util::randomString(24);
	$storage->set('userdata', $userdata_token, null, $userattributes, 30*60);	/* 30*60 minutes valid */
}
	

/* ================================================================ */
/* is user logged on? */
if (isset($userattributes['conext'])) {
	$userId = $userattributes['conext'];
} else {
	$userId = $userattributes['NameID'];
}
if (is_array($userId)) { $userId = join(',', $userId); }
$userCommonName = $userattributes['urn:mace:dir:attribute-def:cn'];
if (is_array($userCommonName)) { $userCommonName = join(',', $userCommonName); }


// ================================================================
// establish current group:
$osGroup = new osapiGroup();
$osGroup->id = array('groupId' => $userattributes['opensocial_instance_id']);
$osGroup->title = 'undefined';
$osGroup->description = 'undefined';

$groupInstance = Group::fromOsapi($osGroup);


$manager = new EPLc_Manager();
// always explicitly set groupsession
$manager->performParsed("groupsession", $userattributes, $groupInstance, $arguments);


// ================================================================
// establish active pad:
$active_pad = Web_CGIUtil::get_prf_argument($_SESSION, $_POST, $_GET, 'activepad', null);

$oEPLclient = new MyEtherpadLiteClient();

if ($proxied_content && !$active_pad) {
	// establish active pad, show list to select pad from:
	$ep_group = $oEPLclient->createGroupIfNotExistsFor($groupInstance->getIdentifier());
	$ep_group_pads = $oEPLclient->listPads($ep_group->groupID);
	
	$padlist = array();
	foreach($ep_group_pads->padIDs as $p => $v) {
		$padlist[] = $p;
	}
	
	$userdata = $userattributes;
	$padlist = $padlist;
	$appcontext = Web_CGIUtil::get_self_url("/eplconext");
	$mainurl = Web_CGIUtil::get_self_url("/eplconext/maingadget.php");
	$mainurl = Web_CGIUtil::appendArg($mainurl, 'udtok', $userdata_token); 
	
	include('templates/select_pad.php');
	exit();
	
} else {
	$userdata = $userattributes;
	$padurl = ETHERPADLITE_PADBASEURL . '/' . $active_pad;
	$t = MyEtherpadLiteClient::splitGrouppadName($active_pad);
	$padname = $t[1];
	include("templates/show_pad.php");
	exit();
	
	// ETHERPADLITE_PADBASEURL
//	$ep_author = $oEPLclient->createAuthorIfNotExistsFor($userId, $userCommonName);
//	$ep_group = $oEPLclient->createGroupIfNotExistsFor($group->getIdentifier());
	
} 


?>