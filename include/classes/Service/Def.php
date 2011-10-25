<?php

class EPLc_Service_Def {

	public $_name;
	public $_authenticated;		// true, false
	public $_authMethod;		// { 'OAuth', 'SimpleSAMLphp' }
	
	public $_groupService;		// true, false
	public $_groupNameConext;	// true, false; when true, the groupname is established from request argument
	public $_trustedGroup;		// true, false; when true, the group only consists of the established groupname
								// otherwise, group info is fetched and constructed based on full attribute selection
								// Note: should only be allowed when request is authenticated (OAuth)

	public static $GROUPNAME_FROM_CONEXTREQUEST = true;
	public static $GROUPNAME_FROM_URL = false;
	
	public static $TRUSTED_GROUP = true;
	public static $UNTRUSTED_GROUP = false;
	
	function __construct($name, $authenticated, $authMethod, $groupService, $groupNameConext = false, $trustedGroup = false) {
		$this->_name = $name;
		$this->_authenticated = $authenticated;
		$this->_authMethod = $authMethod;
		$this->_groupService = $groupService;
		
		$this->_groupNameConext = $groupNameConext;
		$this->_trustedGroup = $trustedGroup;
	}
	
	/**
	 * Factory method for Service_Def
	 * @param String $name Service name, also used in request URI
	 * @param Boolean $authenticated true if authentication is required
	 * @param String $authMethod Method to establish user data {'OAuth', 'SimpleSAMLphp'}
	 * @param boolean $groupService true when it is a group service and the user must be 
	 * 	a member of the group
	 */
	static function create($name, $authenticated, $authMethod, $groupService, $groupNameConext = false, $trustedGroup = false) {
		return new self($name, $authenticated, $authMethod, $groupService, $groupNameConext, $trustedGroup);
	}
	
	
	function __toString() {
		return "{'{$this->_name}: authenticated={$this->_authenticated}/{$this->_authMethod}; groupService={$this->_groupService};" .
					"groupNameConext:{$this->_groupNameConext}; trustedGroup:{$this->_trustedGroup}" .
					"}";
	}
	
	
}

?>