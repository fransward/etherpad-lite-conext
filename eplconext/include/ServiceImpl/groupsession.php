<?php

class Service_groupsession extends EPLc_Service_IAbstractService {
	
	function perform($userinfo, $groupinfo, $serviceargs) {
		$m = 'Performing groupsession for ' . print_r($serviceargs, true) . ' for user ' . $userinfo->_userId;
		if ($groupinfo != null) { $m .= ' and group ' . $groupinfo->_groupId; }
		Logger_Log::debug($m, 'Service_groupsession');
		
		/* add means
		 * argument 0 is the new padname
		 * create it for the group with groupId from $groupinfo->_groupId
		 * ... and be done!
		 */
		$oEPLclient = new EtherpadLiteClient(ETHERPADLITE_APIKEY, ETHERPADLITE_BASEURL);
		
		$padname = $serviceargs[0];
		$ep_group = $oEPLclient->createGroupIfNotExistsFor($groupinfo->_groupId);
		$ep_author = $oEPLclient->createAuthorIfNotExistsFor($userinfo->_userId, $userinfo->_userCommonName);
		
		$endtimestamp = time() + ETHERPADLITE_SESSION_DURATION;
		
		$ep_session = $oEPLclient->createSession(
			$ep_group->groupID, 
			$ep_author->authorID, 
			$endtimestamp);
		
		$sID = $ep_session->sessionID;
		setcookie("sessionID",$sID, $endtimestamp, '/'); // Set a cookie
		Logger_Log::debug("Created new session with id '{$sID}'", 'Service_groupsession');

		$result = EPLc_Service_Response::create(true, "Session created succesfully for userId:'{$userId}' and userCN:'{$userinfo->_userCommonName}'");
		$result->setData(array(
				'sessionId' => $sID,
			));
		
		return $result;
	}
	
	
}
