<?php 
/**
 * Functionality for working with a Pad
 *
 * @author dopey
 */


class EPLc_Pad {
	
	public $_id;
	protected $_name;
	
	protected $_owner;
	protected $_groupid;
	
	function __construct($id) {
		$this->_id = $id;
	}
	
	function getRemoveLink() {
		return ":removelink:";
	}
	
	
	/**
	 * Map current instance to array(
	 *   'name' : 'pad-name',
	//   'url' : 'pad-url',
	//   'created' : 'pad-created-unix-timestamp',
	//   'owner' : 'pad-owner',
	//   'group_id' : 'pad-group-id' }
	 * )
	 */
	function toJSONArray() {
		$result = array();
		
		$p = explode('$', $this->_id);
		if (sizeof($p) > 1) {
			$padname = $p[1];
			$groupid = $p[0];
		} else {
			$padname = $this->_id;
			$groupid = $this->_groupid;
		}
		
		$result['name'] = $padname;
		$result['url'] = '-unknown-';
		$result['created'] = 0;
		$result['owner'] = $this->_owner;
		$result['group_id'] = $groupid;

		return $result;
	}
	
}

?>