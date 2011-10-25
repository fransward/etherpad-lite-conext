<?php
/**
 * GroupRelations implementation for Grouper API
 * 
 * mplementation of interface interface
 * 
 * @author Mark Dobrinic (mdobrinic@cozmanova.com)
 * Implementation for SURFnet (http://www.surfnet.nl)
 */



class GroupRelationsImpl extends IGroupRelations {
	
	private $_testfile;
	
	public function configure($config) {
		$this->_testfile = $config["testfile"];
	}
	
	
	/**
	 * Fetch group relations for provided user
	 * @return array of Group and Person instances
	 */
	public function fetch($args) {
		$userId = $args["userId"];
		// echo "Fetching from Grouper API for {$userId}<br/>\n";
		
		
		$data = file_get_contents($this->_testfile);
		$a = json_decode($data, true);
		$result = array();
		
		foreach ($a["groups"] as $aGroupDef) {
			$result[] = Group::fromJSON($aGroupDef);
		}
		
		return $result;
	}
	
	
	public function process($arguments, $callback, $groups, $persons = array()) {
		
	}
	
	
	
}
?>