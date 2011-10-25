<?php

class EPLc_UserInfo {
	
	public $_userId;			// string
	public $_userCommonName;	// string
	
	public $_userdata;			// k => v array
	
	function __construct() {}
	
	static function create($userId, $userCommonName, $userdata) {
		$o = new EPLc_UserInfo();

		$o->_userId = $userId;
		$o->_userCommonName = $userCommonName;
		$o->_userdata = $userdata;
		
		return $o;
	}
}