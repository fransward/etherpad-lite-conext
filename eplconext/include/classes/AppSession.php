<?php

class EPLc_AppSession {

	/**
	 * User Identifier that was provided by the IdP
	 * @var String
	 */
	protected $_uid;
	
	/**
	 * Collection of user attributes that were provided by IdP
	 * Enter description here ...
	 * @var Array of Key=>Val
	 */
	protected $_attributes;
	
	/**
	 * The unix timestamp when the session was initiated
	 * @var int
	 */
	protected $_started;
	
	/**
	 * The most recent unix timestamp that the session was accessed 
	 * @var int
	 */
	protected $_touched;
	
	function __construct() {
		$this->_uid = null;
	}
	
	
	/**** ---- getters/setters ---- ****/
	function getUID() { return $this->_uid; }
	function setUID($uid) { $this->_uid = $uid; }
	
	function getAttributes() { return $this->_attributes; }
	function setAttributes($attributes) { $this->_attributes = $attributes; }
	
	function getStarted() { return $this->_started; }
	function setStarted($started) { $this->_started = $started; }
	
	function getTouched() { return $this->_touched; }
	function setTouched($touched) { $this->_touched = $touched; }
	
	
}