<?php
class Service_padaccesstoken extends EPLc_Service_IAbstractService {
	
	/**
	 * Generate a token that gives access to a pad as a user
	 * The token should allow access to the pad just once (bearer token)
	 *  
	 * @see include/classes/Service/EPLc_Service_IAbstractService::perform()
	 */
	function perform($userinfo, $groupinfo, $serviceargs) {
    $m = 'Performing padaccesstoken for ' . print_r($serviceargs, true) . ' for user ' . $userinfo->_userId;
    if ($groupinfo != null) { $m .= ' and group ' . $groupinfo->_groupId; }
    // Logger_Log::debug($m, 'Service_padaccesstoken');
    
    $padname = $serviceargs[0];
    
    $storage = new EPLc_Storage('padaccesstoken');
    
    /* generate this token */
    $userdata_token = String_Util::randomString(24);
    
    /* attach authorization to it */
    $userdata = array(
      'userinfo' => $userinfo,
      'groupinfo' => $groupinfo,
      'padname' => $padname,
    );
    
    $storage->set('userdata', $userdata_token, null, $userdata, 30*60); /* short lived: 1*60 seconds valid */
    
    /* return it */
    $result = EPLc_Service_Response::create(true, "PadAccessToken created succesfully.");
    $result->setData(array(
        'padaccesstoken' => $userdata_token,
      ));
    
    return $result;
	}
	
}