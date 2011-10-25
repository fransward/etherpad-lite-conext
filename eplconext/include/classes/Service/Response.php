<?php

class EPLc_Service_Response {
	
	public $_result;		// boolean
	public $_message;		// optional string
	public $_data;			// array with k => v with extra results
	
	
	function getData() { return $this->_data; }
	function setData($data) { $this->_data = $data; }
	
	static function create($result, $message = null) {
		$o = new EPLc_Service_Response();
		$o->_result = $result;
		$o->_message = $message;
		return $o;
	}
	
}