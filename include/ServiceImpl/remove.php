<?php

class Service_remove extends EPLc_Service_IAbstractService {
	
	function perform($userinfo, $groupinfo, $serviceargs) {
		$m = 'Performing remove for ' . print_r($serviceargs, true) . ' for user ' . $userinfo->_userId;
		if ($groupinfo != null) { $m .= ' and group ' . $groupinfo->_groupId; }
		Logger_Log::debug($m, 'Service_remove');
		
		/* remove means
		 * argument 0 is the padname to remove (grouppad: '[groupId]$[padname]')
		 * remove it for the group with groupId from $groupinfo->_groupId
		 * ... and be done!
		 */
		$oEPLclient = new EtherpadLiteClient(ETHERPADLITE_APIKEY, ETHERPADLITE_BASEURL);
		
		$padname = $serviceargs[0];
		$ep_group = $oEPLclient->createGroupIfNotExistsFor($groupinfo->_groupId);
		
		// $delete_padname = $ep_group->groupID . '$' . $padname;
		$delete_padname = $padname;
		
		$ep_removed_pad = $oEPLclient->deletePad($delete_padname);
		
		Logger_Log::debug("Deleted GroupPad with id '{$ep_new_pad->padID}'", 'Service_remove');

		$result = EPLc_Service_Response::create(true, "Pad removed succesfully.");
		$result->setData(array(
				'padId' => $delete_padname,
			));
		
		return $result;
	}
	
	
}
