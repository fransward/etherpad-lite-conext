<?php
/**
 * GroupRelations implementation for OpenSocial API
 * 
 * Implementation of interface
 * 
 * @author Mark Dobrinic (mdobrinic@cozmanova.com)
 * Implementation for SURFnet (http://www.surfnet.nl)
 */

if (!class_exists('osapiException')) {
	require_once( dirname(dirname(dirname(__FILE__))) . "/ExtLib/osapi/osapi.php");
	require_once( 'osapiGroupRelProvider.php' );
}


class GroupRelationsImpl extends IGroupRelations {
	
	private $_consumerkey;
	private $_consumersecret;
	private $_osapiProvider;	// instance of configured osapi-provider
	
	private $_msgSource;		// configurable message source (i.e. from-email-address)
	
	private $_strictMode;
	
	
	/**
	 * (non-PHPdoc)
	 * Configuration has to provide:<br/>
	 * <ul><li>osapi-provider: class of the osapi-provider
	 *     <li>key: Oauth consumer key</li>
	 *     <li>secret: Oauth consumer secret</li>
	 *     <li>provider: Name of the OSAPI Provider class that does the OpenSocial-work</li>
	 * </li>

	 * @see lib/GroupRel/Controller/IGroupRelations#configure()
	 */
	public function configure($config) {
		$this->_consumerkey = $config['consumerkey'];
		$this->_consumersecret = $config['consumersecret'];

		$this->_strictMode = ($config["strictMode"] == TRUE);
		
    $this->_msgSource = (array_key_exists('msgSource',$config)?$config["msgSource"]:null);
				
		$provider_config = $config["provider"];
		$cln = $provider_config["class"];
		$this->_osapiProvider = new $cln(NULL, $provider_config);
	}
	
	
	/**
	 * Helper function to make one call to configured OpenSocial container 
	 * @param $userId UserId to work with
	 * @param $osapi_service.call Service to call
	 * @param $keytoset name of the array key that will contain the results 
	 * @return array containing 'keytoset' => results, or osapiError-instance when error occurred
	 */
	protected function callOpenSocial($user_params, $osapi_service, $keytoset) {
  		$osapi = new osapi(
  					$this->_osapiProvider, 
  					new osapiOAuth2Legged(
  						$this->_consumerkey, 
  						$this->_consumersecret, 
  						$user_params['userId']
  						)
  					);
  					
	  if ($this->_strictMode) {
      $osapi->setStrictMode($this->_strictMode);
    }  
		// Start a batch so that many requests may be made at once.
		$batch = $osapi->newBatch();

		$call = explode('.', $osapi_service);
		if (sizeof($call) != 2) {
			throw new Exception("Invalid OpenSocial service call: {$osapi_service}");
		}
		
		// Instantiate service
		$oService = $osapi->$call[0];
		 
		$batch->add($oService->$call[1]($user_params), $keytoset);

		// Send the batch request.
		$result = $batch->execute();
		
		return $result;
	}
	
	
	/**
	 * Fetch group relations for provided user<br/>
	 * <br/>
	 * Performs 2-legged Oauth call through OpenSocial REST API<br/>
	 * $args is an array with at least "userId" => OpenSocial UserID to perform call for
	 * @return array of Group and Person instances
	 */
	public function fetch($args) {
		$userId = $args["userId"];
		
		$user_params = array(
			'userId' => $userId
		);

		$result = $this->callOpenSocial($user_params, "groups.get", "getGroups");
		
		if ($result instanceof osapiError) {
			// what to do? ignore request? or throw exception
			throw new Exception("Error when retrieving group information OpenSocial (provider: " . $this->_osapiProvider->providerName . ")");
			// return array();
		}
		
		$fetchresult = array();
		
		if (! is_array( $result['getGroups']->list) ) {
			return $fetchresult;
		}
		
		foreach ($result['getGroups']->list as $osapiGroup) {
			$fetchresult[] = Group::fromOsapi($osapiGroup);
		}
		
		return $fetchresult;
	}
	
	
	private function getGroupMembers($userId, $group) {
		$user_params = array(
			'userId' => $userId,
			'groupId' => $group->getIdentifier(),
		);
		
		$result = $this->callOpenSocial($user_params, "people.get", "getPeople");
		
		if ($result instanceof osapiError) {
			// what to do? ignore request? or throw exception
			throw new Exception("Error when retrieving group member information from OpenSocial (provider: " . $this->_osapiProvider->providerName . ")");
			// return array();
		}
		
		$fetchresult = array();
		
		if (! is_array( $result['getPeople']->list) ) {
			return $fetchresult;
		}
		
		foreach ($result['getPeople']->list as $osapiPerson) {
			$fetchresult[] = Person::fromOsapi($osapiPerson);
		}
		
		return $fetchresult;
	}
	
	
	public function process($args, $callback, $groups, $persons = array()) {
		$userId = $args["userId"];	// require this for authorizing OpenSocial-calls
		assert( '$userId != null');
		
		$message = &$args["message"];
		
		// Resolve members of selected groups:
		$aGroupMembers = array();
		
		foreach ($groups as $aGroup) {
			$aGroupMembers[ $aGroup->getIdentifier() ] = $this->getGroupMembers( $userId, $aGroup );
			
		}
		// Add to persons-array
		if (is_array($persons)) {
			$aGroupMembers["person"] = $persons;
		}
		
		foreach ($aGroupMembers as $groupname => $groupmembers) {
			// Send out message as Group-activity?
			
			// $this->sendSocialGroupMessage($groupname, $message);
			;
			
			// Send out message to Group Members
			foreach ($groupmembers as $person) {
				
				if ($callback != NULL) {
					$cb_args = array(&$message, $person);
					
					call_user_func_array($callback, $cb_args);
				}
				
				// mail(message);
			}
		}
		
		
	}
	
	
}
?>