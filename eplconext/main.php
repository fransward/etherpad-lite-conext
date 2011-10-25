<?php
require_once("lib/all.php");
require_once("include/config.php");

/* APP_ENV defines whether the application is deployed locally or in a staging
 * environment; the latter allows access to live OpenSocial services. 
 */
// define('APP_ENV', 'local');
define('APP_ENV', 'staging');

// require_once('lib/GroupRel/Controller/OpenSocial/GroupRelationsImpl.php');


/* are we embedded from OpenSocial-gadget, or running stand-alone? */
/* perform OAuth authentication */
/* rely on SimpleSAMLphp OAuth implementation */
require_once( "include/epl_OAuthStorage.php" );

$userdata = array();
$store = new epl_OAuthStorage();
$server = new sspmod_oauth_OAuthServer($store);
$server->add_signature_method($hmac_method = new OAuthSignatureMethod_HMAC_SHA1());
$server->add_signature_method($plaintext_method = new OAuthSignatureMethod_PLAINTEXT());
$server->add_signature_method($rsa_method = new sspmod_oauth_OAuthSignatureMethodRSASHA1());

$proxied_content = false;

try {
	$oAuthRequest = OAuthRequest::from_request();
	
	// Logger_Log::debug(print_r($oAuthRequest,true));
	
	list( $consumer, $token ) = $server->verify_request($oAuthRequest);
	/* token should be empty, as this is 2-legged-oauth */
	/* Also: when we reached this point, the request was verified (signature) */

//	if (! $store->isAuthorized($token->key)) {
//		// Call not authorized
//		Logger_Log::debug("Unauthorized call.");
//		throw new Exception("Unauthorized call.");
//	}
	
	// only allow when proxied-content is set:
	$proxied_content = $oAuthRequest->get_parameter('opensocial_proxied_content');
	if ($proxied_content != 1) {
		Logger_Log::debug('Invalid call - not from embedded OpenSocial gadget!');
		throw new Exeption('Invalid call - not from embedded OpenSocial gadget!');
	}
	
	/*			[parameters:OAuthRequest:private] => Array ( 
				[lang] => all 
				[country] => ALL 
				[oauth_body_hash] => yuf7psjQdxZ5ohBA96OUgdk8dLs= 
				[opensocial_owner_id] => urn:collab:person:test.surfguest.nl:mdobrinic 
				[opensocial_viewer_id] => urn:collab:person:test.surfguest.nl:mdobrinic 
				[opensocial_app_id] => https://etherpad.conext.surfnetlabs.nl/eplconext/gadget/eplconext.xml 
				[opensocial_app_url] => https://etherpad.conext.surfnetlabs.nl/eplconext/gadget/eplconext.xml 
				[opensocial_instance_id] => nl:surfnet:diensten:etherpadlite-team-a 
				[opensocial_proxied_content] => 1 
				[oauth_version] => 1.0 
				[oauth_timestamp] => 1317132059 
				[oauth_nonce] => 580819809020216534 
				[oauth_consumer_key] => consumer_key_etherpad 
				[oauth_signature_method] => HMAC-SHA1 
				[oauth_signature] => mZ14v3XdMfFQ2JhcL9y8VLicQrQ= 
	 */
	
	$userattributes = array(
			'conext' => $oAuthRequest->get_parameter('opensocial_owner_id'),
			'urn:mace:dir:attribute-def:cn' => '',
			'opensocial_instance_id' => $oAuthRequest->get_parameter('opensocial_instance_id'),
		);
		
	
} catch (OAuthException $e) {
	/* OK; no OAuth call; perform SimpleSAML authentication
	 */
	// Logger_Log::debug(print_r($e, true), "main.php");
	
	$as = new SimpleSAML_Auth_Simple(SPDEF);
	$as->requireAuth();

	/* establish local user context */
	$userattributes = $as->getAttributes();
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

/* ================================================================ */
/* is user member of requested group? */
if ($proxied_content !== FALSE) {
	// we were called from embedded OpenSocial gadget
	// simulate osapi response for 1 group:
	$o = new osapiGroup();
	$o->id = array('groupId' => $userattributes['opensocial_instance_id']);
	$o->title = 'undefined';
	$o->description = 'undefined';
	
	$a = array($o);
	$result['getGroups'] = new osapiCollection($a);
	
} else if (APP_ENV != 'local') {
	$os_config = array('providerName' => 'conext',
	                   'requestTokenUrl' => OAUTH_CONFIG_requestTokenUrl,
	                   'authorizeUrl' => OAUTH_CONFIG_authorizeUrl,
	                   'accessTokenUrl' => OAUTH_CONFIG_accessTokenUrl, 
	                   'restEndpoint' => OAUTH_CONFIG_restEndpoint,
	                   'rpcEndpoint' => OAUTH_CONFIG_rpcEndpoint,
	);
	$storage = new osapiFileStorage('/tmp/osapi');
	/* NULL as HttpProvider implies: 'new osapiCurlProvider()' */
	$provider = new osapiGroupRelProvider(NULL, $os_config);
	$auth = osapiOAuth3Legged_10a::performOAuthLogin(OAUTH_CONFIG_consumerKey, OAUTH_CONFIG_consumerSecret, 
				$storage, $provider, $userId);
	
	/* now retrieve groups for this member */
	$user_params = array('userId' => $userId);
	
	$osapi = new osapi($provider, $auth);
	if ($strictMode) { $osapi->setStrictMode($strictMode); }
	
	$service = $osapi->groups;
	$batch = $osapi->newBatch();
	$batch->add($service->get($user_params), 'getGroups');
	$result = $batch->execute();
	
} else {
	$result = null;
}

/* start with static group until group fetch actually works.. */
$gname="static group";
$aGroups = array(); // array($gname);
$o = new Group($gname); $o->_aAttributes['description'] = 'Static Group Desc';
$aGroupInstances = array(); // array($o);

if ($result != null) {
	foreach ($result['getGroups']->list as $osapiGroup) {
		$o = Group::fromOsapi($osapiGroup);
		
		$aGroupInstances[] = $o;
		$aGroups[] = $o->getIdentifier();
	}
}


/* fetch pads for groups */
$oEPLclient = new MyEtherpadLiteClient();
try {
	$ep_author = $oEPLclient->createAuthorIfNotExistsFor($userId, $userCommonName);
} catch (UnexpectedValueException $e) {
	Logger_Log::error("Exception: '" . $e->getMessage() . "'; is the EtherpadLite service started?");
	exit();
}

foreach ($aGroupInstances as $group) {
	$ep_group = $oEPLclient->createGroupIfNotExistsFor($group->getIdentifier());
	$ep_group_pads = $oEPLclient->listPads($ep_group->groupID);
	
	$a = array();
	foreach($ep_group_pads->padIDs as $p => $v) {
		$a[] = $p;
	}
	$aPads[$group->getIdentifier()] = $a; 
}

/* login the user@etherpad */
// --> this is irrelevant, as we need to login for a GROUP everytime we switch groups 
//if (! isset($_COOKIE['sessionID'])) {
//
//	$endtimestamp = time() + ETHERPADLITE_SESSION_DURATION;
//
//	$ep_session = $oEPLclient->createSession(
//		$ep_group->groupID,
//		$ep_author->authorID,
//		$endtimestamp);
//
//	$sID = $ep_session->sessionID;
//	// bool setcookie ( string $name [, string $value [, int $expire = 0 [, string $path [, string $domain [, bool $secure = false [, bool $httponly = false ]]]]]] )
//	setcookie("sessionID",$sID, $endtimestamp, '/'); // Set a cookie in root of server
//	echo "New session created<br/>\n";
//} else {
//	$sID = $_COOKIE['sessionID'];
//}

/* deal with group pads */
// $aPads = array("Pad1", "AnotherPad", "ScratchPad", "Pad2", "Another Pad2");

// print_r($aPads);

/* initialize page context for AJAX-requests */
$urlPadAdd = Web_CGIUtil::get_self_url('/eplconext/padmanager.php/add/');
$urlPadRemove = Web_CGIUtil::get_self_url('/eplconext/padmanager.php/remove/');
$urlGroupSession = Web_CGIUtil::get_self_url('/eplconext/padmanager.php/groupsession/');


?>
<html>
<head>
  <title>SURFconext: EtherpadLite - main frame</title>
  <link type="text/css" href="css/eplconext.css" rel="stylesheet" />
  <link type="text/css" href="css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" />

  <script language="JavaScript" src="js/jquery-1.6.2.min.js"></script>
  <script language="JavaScript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
  <script language="JavaScript" src="js/etherpad.js"></script>
  <script language="JavaScript">
  function makeHttpObject() {
	  try {return new XMLHttpRequest();}
	  catch (error) {}
	  try {return new ActiveXObject("Msxml2.XMLHTTP");}
	  catch (error) {}
	  try {return new ActiveXObject("Microsoft.XMLHTTP");}
	  catch (error) {}

	  throw new Error("Could not create HTTP request object.");
	}

	// append key=urlencodedURIComponent(val) to url, and return result
  function addToUrl(url, key, value) {
    var s='?'; if (url.indexOf("?") > -1) {
      s = '&';
    }
    return [ url, s, key, '=', encodeURIComponent(value) ].join("");
  }

  function callAddPad(groupname, newpadname, onsuccessfunction, xtra_argument) {
	var u = '<?php echo $urlPadAdd; ?>' + encodeURIComponent(groupname) + '/' + encodeURIComponent(newpadname);
    var request = makeHttpObject();
    request.open("GET",u,true);
    request.send(null);
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
        	var resp = JSON.parse(request.responseText);
        	
			if (resp.result == 'OK') {
				onsuccessfunction(xtra_argument, resp.data.padId);
			} else {
				if (resp.message != null) {
					alert(resp.message);
				}
			}
        }
    };
  }

  function callRmPad(groupname, rmpadname, onsuccessfunction, xtra_argument) {
	var u = '<?php echo $urlPadRemove; ?>' + encodeURIComponent(groupname) + '/' + encodeURIComponent(rmpadname);
    var request = makeHttpObject();
    request.open("GET",u,true);
    request.send(null);
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
        	var resp = JSON.parse(request.responseText);
        	
			if (resp.result == 'OK') {
				onsuccessfunction(xtra_argument, resp.data.padId);
			} else {
				if (resp.message != null) {
					alert(resp.message);
				}
			}
        }
    };
  }

  function callGroupSession(group, author) {
	  author = '<?php echo $userId; ?>';
	  var u = '<?php echo $urlGroupSession; ?>' + encodeURIComponent(group) + '/' + encodeURIComponent(author);
	  var request = makeHttpObject();
	  request.open("GET",u,true);
	  request.send(null);
	  //not interested in result
	    request.onreadystatechange = function() {
	        if (request.readyState == 4) {
	        	var resp = JSON.parse(request.responseText);
	        	
				if (resp.result == 'OK') {
					// session changed; it's cool
				} else {
					if (resp.message != null) {
						alert(resp.message);
					}
				}
	        }
	    };
  }


function resizeEPLFrame() {
	var hg = document.body.scrollHeight;
	hg = window.innerHeight;
	var the_height=
	    document.getElementById('epframeeplframe').contentWindow.
		    document.body.scrollHeight;
    hMain = $('#site-wrapper').height();
	hHeader = document.getElementById('header').scrollHeight * 3;	// approx..
	$('#eplframe').height(hg - hHeader);
}
  
  function resizeEPLFrame2() {
		var framename='epframe'+'eplframe';
		
    	var the_height=
    	    document.getElementById(framename).contentWindow.
    	      document.body.scrollHeight;

    	var correction = 3 * document.getElementById('header').offsetHeight;
    	alert('correct: ' + correction);
  	     the_height = the_height - correction;

    	  //change the height of the iframe
    	  document.getElementById('eplframe').style.height=
    	     the_height;
    }
  

  var docreadyfunc = function() {
		var stop = false;
		$( "#GroupAccordion h3" ).click(function( event ) {
			if ( stop ) {
				event.stopImmediatePropagation();
				event.preventDefault();
				stop = false;
			}
		});
		$( "#GroupAccordion" )
			.accordion({
				header: "> div > h3",
				change: function(event, ui) {
					var o=decodeURIComponent(ui.newHeader.attr('id').substr(3));
					callGroupSession(o, null);
					}
			})
			.sortable({
				axis: "y",
				handle: "h3",
				stop: function(event, ui) {
					var theOrder = $(this).sortable('toArray').toString();
					$.get('update-sort.php', {theOrder:theOrder});
					stop = true;
				}
			});


	    $(".cPadLink").click(function(){
	    	var linkid = $(this).attr('id');
	    	var theid=decodeURI(linkid.substr(3));
		    var theoptions={'host':'https://etherpad.conext.surfnetlabs.nl/',
	    	    'baseUrl':'p/',
    		    'showControls':'true',
    		    'showChat':'true',
    	    	'showLineNumbers':'true',
    	    	'padId': theid };
		    
	        $("#eplframe").pad(theoptions);
	        resizeEPLFrame();

	    });

	    $(".cPadLinkRm").click(function(){
	    	var linkid=$(this).attr('id');
	    	var theid=decodeURIComponent(linkid.substr(3));

	    	var linkul = $(this).parent().parent(); // .parentNode.parentNode;
	    	var gid=decodeURIComponent(linkul.attr('id').substr(3));
	    	
			if (! confirm("Removing pad '" + theid + "' from group '"+gid+"'; are you sure?")) {
				alert('Cancelled.');
			} else {
				// AJAX-call
				callRmPad(gid, theid, function(container_element,padId) {
					var padname;
					p = padId.split('$');
					if (p.length==1) {
						padname=p[0];
					} else {
						padname=p[1];
					}

					var el = document.getElementById('spg'+encodeURIComponent(padId)).parentNode;
					el.parentNode.removeChild(el);

				}, linkul);
			}
	    });

	    $(".cPadLinkAdd").click(function(){
		    // groupname:
			var groupfromlinkid=$(this).attr('id');
			var theid = groupfromlinkid.substr(3);
			theid = decodeURI(theid);

			// container of group pads:
		    var linkul = this.parentNode.parentNode; // .parentNode.parentNode;
			
			var padname = prompt("Request name for new pad in group "+theid);
			if (padname == null) {
				// cancelled
			} else {
				if (padname.length > 0) {
					// alert("Creating new pad '"+padname+"' in group " + theid + " in ul with " + linkul.children.length + " children");

					// AJAX-call
					callAddPad(theid, padname, function(container_element, padId) {
						var padname;
						p = padId.split('$');
						if (p.length==1) { padname=p[0]; } else { padname=p[1]; }
						
						// alert('We create a new pad ' + padId + ' (' + padname + ') in this group "' + theid + '"?');

						// <a id="spgg.DaWJBtODs2bvGPyy%24alibaba" class="cPadLink" alt="Open pad" href="#">
						// &nbsp;|&nbsp;
						// <a id="rpgg.DaWJBtODs2bvGPyy%24alibaba" class="cPadLinkRm" alt="Remove pad" href="#">
						
					    // ul[#urlencoded(group-id)].li.a[#urlencoded(pad-id) //
					    var newpadlink=document.createElement('a');
					    newpadlink.setAttribute('id', 'spg'+encodeURIComponent(padId));
					    newpadlink.setAttribute('class', 'cPadLink padhandled');
					    newpadlink.setAttribute('alt', 'Open pad');
					    newpadlink.setAttribute('href', '#');
						var newpadimage=document.createElement('img');
						newpadimage.setAttribute('src', 'images/arrownext01.png');
						newpadimage.setAttribute('height', '12px');

					    newpadlink.appendChild(newpadimage);
					    newpadlink.appendChild(document.createTextNode(' '+padname));

					    var removenewpadlink=document.createElement('a');
					    removenewpadlink.setAttribute('id', 'rpg'+encodeURIComponent(padId));
					    removenewpadlink.setAttribute('class', 'cPadLinkRm padhandled');
					    removenewpadlink.setAttribute('href', '#');
					    var removepadimage=document.createElement('img');
					    removepadimage.setAttribute('src', 'images/redcross.png');
					    removepadimage.setAttribute('height', '12px');
					    
					    removenewpadlink.appendChild( removepadimage );
					    
					    var newpadli=document.createElement('li');
					    newpadli.appendChild( newpadlink );
					    newpadli.appendChild( document.createTextNode(' | ') );
					    newpadli.appendChild( removenewpadlink );

						var c = container_element.children;
						var i = c.length;
						container_element.insertBefore(newpadli, c[i-1]);

						// unbind click handlers before re-setting for new element
						$(".padhandled").unbind("click");
						
						docreadyfunc();
					}, linkul);	// callAddPad
				} else {
					alert("Invalid padname");
				}
			}
	    });
	};
  
  $(document).ready(function() {
	  // create session for first group:
	  var o = $('a.ahrGroupHeader:first').parent();
	  if (o!=null && o.attr('id')!=null) {
	  	var i = decodeURIComponent(o.attr('id').substr(3));
	  	callGroupSession(i, null);
	  }

	  // set size of the iframe:
	  var ifr = document.getElementById('eplframe');
	  
	  docreadyfunc();
  });

  $(window).resize(function() {
	  $('#eplframe').css("height","auto");  // Eliminate the heights value
	  resizeEPLFrame();  // Set the heights once more
	  });

  </script>
  
</head>
<body>
<div id="site-wrapper">
  <!-- insert header here -->
  <div id="header">
    <div id="logo">
      <div style="float:left;"><img src="images/osd-document-icon.png"/></div>
      SURFconext: EtherpadLite<br/>
      Ingelogd als: <?php print "$userCommonName ($userId)"; ?>
    </div><!-- logo -->
  </div><!-- header -->


  <div class="main" id="main-two-columns">

<?php /* ================================================ */ ?>
    <div class="left" id="main-content">
      <h1>Main Content</h1>
      <div id="eplframe">
        Kies een pad uit een groep uit de lijst.
      </div>
    </div><!-- left#main-content -->
    
<?php /* ================================================ */ ?>
    <div class="right sidebar" id="sidebar">
      <div id="GroupAccordion">
<?php
if (count($aGroupInstances) == 0) {
	print "<div><h3>No group memberships</h3></div>";
	
} else {
	foreach ($aGroupInstances as $group) { 
		$groupid = $group->getIdentifier();
		$groupname = $group->_aAttributes['title'];
	
		print("<div id=\"div" . rawurlencode($groupid) . "\">");
	    print("<h3 id=\"h3_" . rawurlencode($groupid) . "\"><a class=\"ahrGroupHeader\" href=\"#\">" . $groupname . "</a></h3>");
	    print("<ul class=\"ulPadList\" id=\"ul_" . rawurlencode($groupid) . "\">");
	
	    foreach ($aPads[$groupid] as $aPad) {
			$pad = new EPLc_Pad($aPad);
			
			list($gid, $pid) = MyEtherpadLiteClient::splitGrouppadName($pad->_id);
			$encodedpid = urlencode($pid);
			$encodedgid = urlencode($gid);
			$encoded_padid = rawurlencode($pad->_id);
	
			print(<<<HERE
<li><a class="cPadLink padhandled" id="spg{$encoded_padid}" href="#" alt="Open pad"><img src="images/arrownext01.png" height="12px"/>&nbsp;{$pid}</a>
    &nbsp;|&nbsp;
    <a class="cPadLinkRm padhandled" id="rpg{$encoded_padid}" href="#" alt="Remove pad"><img src="images/redcross.png" height="12px"/></a></li>
HERE
			);
		} // foreach (pads)

		print(<<<HERE
<li><hr/><a class="cPadLinkAdd padhandled" id="apg{$groupid}" href="#" alt="Add new pad"><img src="images/greenplus.png" height="12px" />&nbsp;New pad</a></li>
</ul>
</div>
HERE
		);
	} // foreach (group) 
}	// if (count ... )
?>
      </div><!-- GroupAccordion -->
    </div><!--right sidebar#sidebar-->
    <div class="clearer">&nbsp;</div><!--clearer-->
  </div><!-- main#main-two-columns -->
</div><!-- site-wrapper -->
</body>
</html>