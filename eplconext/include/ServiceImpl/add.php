<?php

class Service_add extends EPLc_Service_IAbstractService {
	
	function perform($userinfo, $groupinfo, $serviceargs) {
		$m = 'Performing add for ' . print_r($serviceargs, true) . ' for user ' . $userinfo->_userId;
		if ($groupinfo != null) { $m .= ' and group ' . $groupinfo->_groupId; }
		Logger_Log::debug($m, 'Service_add');
		
		/* add means
		 * argument 0 is the new padname
		 * create it for the group with groupId from $groupinfo->_groupId
		 * ... and be done!
		 */
		$oEPLclient = new EtherpadLiteClient(ETHERPADLITE_APIKEY, ETHERPADLITE_BASEURL);
		
		$padname = $serviceargs[0];
		$ep_group = $oEPLclient->createGroupIfNotExistsFor($groupinfo->_groupId);
		
		$ep_new_pad = $oEPLclient->createGroupPad($ep_group->groupID, $padname, "{$padname}\nThis is something that does not need to be typed.");
		Logger_Log::debug("Created new GroupPad with id '{$ep_new_pad->padID}'", 'Service_add');

		$result = EPLc_Service_Response::create(true, "Pad created succesfully.");
		$result->setData(array(
				'padId' => $ep_new_pad->padID,
			));
		
		return $result;
	}
	
	
}
