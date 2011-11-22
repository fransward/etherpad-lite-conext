<?php

require_once('add.php');  // explicitly include our parent class as classloader will not find it

class Service_remoteadd extends Service_add {
  /* class to use other name for same implementation */
	
//	function perform($userinfo, $groupinfo, $serviceargs) {
//		print "We are being called: remoteadd\n";
//	  $o = parent::perform($userinfo, $groupinfo, $args);
//	  return $o;
//	}
}
