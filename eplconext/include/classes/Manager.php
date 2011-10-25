<?php


define('EPLC_MANAGER_MODE', 'stage');	// can be {'test', 'stage'}



class EPLc_Manager {
	
	protected $_config;
	
	
	/**
	 * Execute service outside without creating context from Request context 
	 * @param $serviceName name of the service
	 * @param $userdata userdata of (already) authenticated user
	 * @param $group name of the group
	 * @param $arguments extra arguments
	 */
	function performParsed($serviceName, $userdata, $group, $arguments) {
		$service = EPLc_Service_Registry::lookup($serviceName);
		if ($service == null) {
			throw new Exception('Unknown service.');
		}

		list($userId, $userCommonName) = $this->parseUserId($userdata, null);
		$userinfo = EPLc_UserInfo::create($userId, $userCommonName, $userdata);

		if ($service->_groupService) {
			if (! $group instanceof Group) {
				$groupInstance = $this->getGroupInstanceFromGroup($userId, $group);
			} else {
				$groupInstance = $group;
			}
			
			$groupinfo = EPLc_GroupInfo::create($groupInstance->getIdentifier(), 
					$groupInstance->_aAttributes["description"], $groupInstance->_aAttributes);
			
		} else {
			$groupinfo = null;
		}
		
		$o = EPLc_Service_Registry::serviceInstance($service);
		return $o->perform($userinfo, $groupinfo, $serviceArgs);
	}
	
	
	/**
	 * Process the request, called like:
	 * mainscript/{servicename}[/{groupname}]/{arguments}
	 * Enter description here ...
	 * @param $request
	 */
	function perform($request) {
		$serviceName = $this->getServiceFromRequest($request);
		if ($serviceName == null) {
			throw new Exception('No service.');
		}
		
		Logger_Log::debug("Established servicename '{$serviceName}'", 'EPLc_Manager');
		
		$service = EPLc_Service_Registry::lookup($serviceName);
		if ($service == null) {
			throw new Exception('Unknown service.');
		}

		Logger_Log::debug("Established service def '{$service}'", 'EPLc_Manager');
		
		$userdata = array();
		$userId = null;
		$userCommonName = null;
		
		if ($service->_authenticated) {
			if ($service->_authMethod == 'OAuth') {
				
				$store = new epl_OAuthStorage();
				$server = new sspmod_oauth_OAuthServer($store);
				$server->add_signature_method($hmac_method = new OAuthSignatureMethod_HMAC_SHA1());
				$server->add_signature_method($plaintext_method = new OAuthSignatureMethod_PLAINTEXT());
				$server->add_signature_method($rsa_method = new sspmod_oauth_OAuthSignatureMethodRSASHA1());

				$oAuthRequest = OAuthRequest::from_request();
				list( $consumer, $token ) = $server->verify_request($oAuthRequest);

				if (! $store->isAuthorized($token->key)) {
					throw new Exception("Unauthorized OAuth call.");
				}

				$userdata = $store->getAuthorizedData($token->key);
				
			} else if ($service->_authMethod == 'SimpleSAMLphp') {
				$as = new SimpleSAML_Auth_Simple(SPDEF);
				$as->requireAuth();

				/* establish local user context */
				$userdata = $as->getAttributes();
				
			} else if ($service->_authMethod = 'static') {
				/* initialize with test data */
				$userdata['NameID'] = 'urn:collab:person:test.surfguest.nl:mdobrinic';
				$userdata['cn'] = 'Marque D\'evelopper';
				
			} else {
				throw new Exception("Unknown authentication method '{$service->_authMethod}'");
			}

			list($userId, $userCommonName) = $this->parseUserId($userdata, $request->getRequestBody());
			
			// Logger_Log::debug("UserID established: userId:'{$userId}', userCN:'{$userCommonName}' (". print_r($userdata, true) .")");
			
			
		}
		
		$userinfo = EPLc_UserInfo::create($userId, $userCommonName, $userdata);

		
		if ($service->_groupService) {
			$group = $this->getGroupFromRequest($service, $request);
			
			if ($group == null) {
				throw new Exception("No group in request.");
			}
			
			// Establish whether we should go out and doublecheck, or whether we trust the
			// established group-identifier and don't need more group-attributes (name, desc, etc.)
			if ($service->_trustedGroup) {
				$groupInstance = new Group($group);
			} else {
				$groupInstance = $this->getGroupInstanceFromGroup($userId, $group);
			}
			
		} else {
			$group = null;
		}
		
		if ($group!=null) {
			$groupinfo = EPLc_GroupInfo::create($groupInstance->getIdentifier(), 
					$groupInstance->_aAttributes["description"], $groupInstance->_aAttributes);
		} else {
			$groupinfo = null;
		}
		
		/* establish service arguments */
		$serviceArgs = $this->getArgumentsFromRequest($request, $service->_groupService);

		Logger_Log::debug('Service arguments: ' . print_r($serviceArgs, true), get_class($this));
		
		$o = EPLc_Service_Registry::serviceInstance($service);
		return $o->perform($userinfo, $groupinfo, $serviceArgs);
	}
	

	/**
	 * Helper function to retrieve data from OpenSocial JSON-encoded postdata content
	 * 
	 * Example JSON-encoded postdata content:
	 * Array  ( [0] => stdClass Object 
	 *    (
     *      [id] => viewer
     *      [result] => stdClass Object (
     *      	( [element] => [value], ... )
     *      )
     *    )
     * )
	 * @param JSON-object $j OpenSocial JSON-encoded postdata content
	 * @param string $id ID of the section to find
	 * @return JSON-object of the result, or null of id was not found
	 */
	function getOSAPIResultSection($j, $id) {
		foreach ($j as $i) {
			if ($i->{"id"} == $id) {
				return $i->{"result"};
			}
		}
		return null;
	}
	
	
	/**
	 * helper function to get userId and userCommonName out of the userdata
	 * Special case: if attribute 'conext' is set in $userdata, this is used as userId instead of 'NameID'
	 * @param array $userdata containing attributes that can be used for userId and userCommonName
	 * @return array(userId, userCommonName) 
	 */
	function parseUserId($userdata, $postbody = null) {
		/* conext-attribute is introduced for development purposes */
		if (isset($userdata['conext'])) {
			$userId = $userdata['conext'];
		} else {
			$userId = $userdata['NameID'];
		}
		if (is_array($userId)) { $userId = join(',', $userId); }
		
		// can we establish userdata from postbody / "viewer"-section?
		$userCommonName = null;
		
		if ($postbody) {
			$j = json_decode($postbody);
			if ($j != NULL) {
				$section = $this->getOSAPIResultSection($j, "viewer");
				if ($section != null) {
					$userCommonName = $section->name->formatted;
				}
			}
		}
		if ($userCommonName == null) {
			$userCommonName = $userdata['urn:mace:dir:attribute-def:cn'];
			if (is_array($userCommonName)) { $userCommonName = join(',', $userCommonName); }
		}

		return array($userId, $userCommonName);
	}
	
	
	/**
	 * Resolve the servie from how we were called
	 * Parses $SCRIPT_NAME/service[/....]
	 * @param EPLc_Model_Request $request The request to investigate
	 */
	function getServiceFromRequest($request) {
		$request_uri = $request->getServerParameter('REQUEST_URI');
		$script_name = $request->getServerParameter('SCRIPT_NAME');
		
		$context = substr($request_uri, strlen($script_name));
		
		$e = explode('/', $context);
		if (sizeof($e) > 0) {
			return urldecode($e[1]);
		}
		
		return null;
	}
	
	
	/**
	 * Resolve the group from how we were called
	 * Parses $SCRIPT_NAME/service/group[/....] or inspects Request-context for group-identifier
	 * @param EPLc_Service $service Service definition, defining where group
	 *   can be retrieved from (URI, or request-parameter)
	 * @param EPLc_Model_Request $request The request to investigate
	 */
	function getGroupFromRequest($service, $request) {
		
		if ($service->_groupNameConext) {
			return $request->getQueryParameter('opensocial_instance_id');
		} else {
			$request_uri = $request->getServerParameter('REQUEST_URI');
			$script_name = $request->getServerParameter('SCRIPT_NAME');
			
			$context = substr($request_uri, strlen($script_name));
			
			$e = explode('/', $context);
			if (sizeof($e) > 1) {
				$d = urldecode($e[2]);
				// remove optional ?-arguments when group-name is last in URL:
				$r = explode('?', $d);
				return $r[0]; 
			}
		}
		
		return null;
	}
	
	/**
	 * Lookup the Group-instance of provided group for user; throws an exception
	 * when the user is not member of the provided group
	 * @param unknown_type $userId UserID to do mapping for
	 * @param unknown_type $group Group to create GroupInstance for
	 * @throws Exception whenever user is not member of group 
	 */
	function getGroupInstanceFromGroup($userId, $group) {
		$userGroups = $this->getGroupsForUser($userId);
		
		$groupInstance = null;
		foreach ($userGroups as $i) {

			if ($i->getIdentifier() == $group) { $groupInstance = $i; break; }
		}
		
		if ($groupInstance == null) {
			throw new Exception("User not member of group '${group}'");
		}
		
		return $groupInstance;
	}
	
	
	/**
	 * Resolve the arguments from how we were called
	 * Parses $SCRIPT_NAME/service[/group]/..arguments..
	 * @param EPLc_Model_Request $request The request to investigate
	 */
	function getArgumentsFromRequest($request, $groupservice) {
		$request_uri = $request->getServerParameter('REQUEST_URI');
		$script_name = $request->getServerParameter('SCRIPT_NAME');
		
		$context = substr($request_uri, strlen($script_name));
		
		/* how many leading elements are not arguments? */
		$skipcount = 2 + ($groupservice?1:0);

		$e = explode('/', $context);
		if (sizeof($e) > $skipcount) {
			$j = 0;
			$s = sizeof($e);
			for ($i=0; $i<$s; $i++) {
				if ($i<$skipcount) {
					array_shift($e);
				} else {
					$e[$j] = urldecode($e[$j]);
					$j++;
				}
			}
		}
		
		return $e;
	}
	
	
	/**
	 * Perform OpenSocial call to establish the groups;
	 * also possible to cache the result in a session context here and return the groups from cache instead ...
	 * @param String $userId to resolve the groups for
	 */
	function getGroupsForUser($userId) {
		
		$session_groups = $_SESSION['cache.groups'];	// by app convention, this is of type 'EPLc_CacheItem' 
		if (isset($session_groups)) {
			if ($session_groups->expires < time()) {
				unset($session_groups);
			} else {
				return $session_groups->_contents; 
			}
		}

		/* could make this configurable; for now, just use the GroupRel implementation */
		if (EPLC_MANAGER_MODE == 'test') {
			$groups = array(); 
			
			$oGroup = new Group('test-group');
			$oGroup->_aAttributes["title"] = 'test-group title';
			$oGroup->_aAttributes["description"] = 'test-group description';
			$groups[] = $oGroup;
			
			$oGroup = new Group('second-group');
			$oGroup->_aAttributes["title"] = 'second-group title';
			$oGroup->_aAttributes["description"] = 'second-group description';
			$groups[] = $oGroup;
			
			$oGroup = new Group('static group');
			$oGroup->_aAttributes["title"] = 'static group title';
			$oGroup->_aAttributes["description"] = 'static group description';
			$groups[] = $oGroup;
			
		} else if (EPLC_MANAGER_MODE == 'stage') {
			// Use 3-legged-OAuth to fetch token ..
			$config = array('consumerkey' => OAUTH_CONFIG_consumerKey,
							'consumersecret' => OAUTH_CONFIG_consumerSecret,
							'strictMode' => TRUE,
							'provider' => 
								array('providerName' => 'conext',
									'class' => 'osapiGroupRelProvider',
				                   'requestTokenUrl' => OAUTH_CONFIG_requestTokenUrl,
				                   'authorizeUrl' => OAUTH_CONFIG_authorizeUrl,
				                   'accessTokenUrl' => OAUTH_CONFIG_accessTokenUrl, 
				                   'restEndpoint' => OAUTH_CONFIG_restEndpoint,
				                   'rpcEndpoint' => OAUTH_CONFIG_rpcEndpoint,
								),
			);
			
			$storage = new osapiFileStorage('/tmp/osapi');
			/* NULL as HttpProvider implies: 'new osapiCurlProvider()' */
			$provider = new osapiGroupRelProvider(NULL, $config['provider']);
			$auth = osapiOAuth3Legged_10a::performOAuthLogin(
						$config['consumerkey'], $config['consumersecret'],  
						$storage, $provider, $userId);
						
			/* now retrieve groups for this member */
			$user_params = array('userId' => $userId);
	
			$osapi = new osapi($provider, $auth);
			if ($strictMode) { $osapi->setStrictMode($strictMode); }
	
			$service = $osapi->groups;
			$batch = $osapi->newBatch();
			$batch->add($service->get($user_params), 'getGroups');
			$batchresult = $batch->execute();
			
			if ($batchresult instanceof osapiError) {
				throw new Exception("Error when retrieving group information OpenSocial (provider: " . $osapi->providerName . ")");
			}
		
			$groups= array();
			foreach ($batchresult['getGroups']->list as $osapiGroup) {
				
				$groups[] = Group::fromOsapi($osapiGroup);
			}
		}
		
		/* store in cache */
		$session_groups = new EPLc_CacheItem();
		$session_groups->_created = time();
		$session_groups->_expires = time() + 0;	// 90 seconds lifetime before re-fetch
		$session_groups->_contents = $groups;
		$_SESSION['cache.groups'] = $session_groups;
		return $groups;
	}
	
}
?>