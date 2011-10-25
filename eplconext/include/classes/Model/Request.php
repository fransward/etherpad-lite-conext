<?php

/**
 * Wrapper class around a HTTP request
 *
 * @author dopey
 *
 */
class EPLc_Model_Request {
	
	protected $_context;
	
	private function __construct() {
		// retrieve body from request:
		$request_body= @file_get_contents('php://input');
		
		$this->_context = array(
					'GET' => $_GET,
					'POST' => $_POST,
					'COOKIE' => $_COOKIE,
					'SERVER' => $_SERVER,
					'REQUEST_BODY' => $request_body,
				);
	}
	

	protected function getParameter($context, $arg, $default = null) {
		$v = $this->_context[$context][$arg];
		if ($v == null) {
			return $default;	
		} else {
			return $v;
		}
	}
	
	
	function getQueryParameter($arg, $default = null) {
		return $this->getParameter('GET', $arg, $default);
	}
	
	function getPostParameter($arg, $default = null) {
		return $this->getParameter('POST', $arg, $default);
	}
	
	function getCookieParameter($arg, $default = null) {
		return $this->getParameter('COOKIE', $arg, $default);
	}
	
	function getServerParameter($arg, $default = null) {
		return $this->getParameter('SERVER', $arg, $default);
	}
	
	function getRequestBody() {
		return $this->_context['REQUEST_BODY'];
	}
	
	
	static function create() {
		$o = new EPLc_Model_Request();
		return $o;
	}
	
}