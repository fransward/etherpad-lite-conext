<?php



class EPLc_Service_Registry {
	
	static protected $_store = array();	/* static initializer */
	
	static public $_serviceImplPath = 'hello'; // 
	
	static function lookup($name) {
		/* for now, just perform static comparison */
		if ($name == 'add' 
				|| $name == 'remove' 
        || $name == 'groupsession'
        ) {
//			return EPLc_Service_Def::create($name, true, 'OAuth', true);
//			return EPLc_Service_Def::create($name, true, 'static', true);
			return EPLc_Service_Def::create($name, true, 'SimpleSAMLphp', true);
		} else if ($name == 'grouppadlist'
		    || $name == 'padaccesstoken'
		    || $name == 'remoteadd'
		    ) {
			$o = EPLc_Service_Def::create($name, true, 'OAuth', true,
					EPLc_Service_Def::$GROUPNAME_FROM_URL, 
					EPLc_Service_Def::$TRUSTED_GROUP);
			return $o;
		}
		
		/* not defined */
		return null;
	}
	
	
	static function serviceInstance($service) {
		/* application class loader */
		/* overrule impl path.. dirty, but static initialization is not allowed like this.. */
		self::$_serviceImplPath = dirname(dirname(dirname(__FILE__))) . '/ServiceImpl';
		
		$f = self::$_serviceImplPath . '/' . $service->_name . '.php';
		
		Logger_Log::debug("including service implementation class '{$f}'", 'EPLc_Service_Registry');
		
		require_once($f);
		
		$c = "Service_{$service->_name}";
		return new $c();
	}

}