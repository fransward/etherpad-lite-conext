<?php

class EPLc_GroupInfo {
	
	public $_groupId;		// string
	public $_name;			// string
	
	public $_groupdata;		// k => v array
	
	function __construct() {}
	
	static function create($groupId, $name, $groupdata) {
		$o = new EPLc_GroupInfo();

		$o->_groupId = $groupId;
		$o->_name = $name;
		$o->_groupdata = $groupdata;
		
		return $o;
	}
	
}