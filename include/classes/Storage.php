<?php

class EPLc_Storage {
	public $_db; 
	
	function __construct($name) {
		/* open file */
		$dbfile = 'storage/$name.sqlite';
		
		if ($this->_db = new SQLiteDatabase($dbfile)) {
			$q = @$this->_db->query('SELECT key1 FROM data LIMIT 1');
			if ($q === false) {
				$this->_db->queryExec('
		CREATE TABLE data (
			key1 text, 
			key2 text,
			type text,
			value text,
			created timestamp,
			updated timestamp,
			expire timestamp,
			PRIMARY KEY (key1,key2,type)
		);
		');
			} 
		} else {
		    throw new Exception('Error creating SQL lite database [' . $dbfile . '].');
		}
	}
	
	
	public function set($type, $key1, $key2, $value, $duration = NULL) {
		if ($this->exists($type, $key1, $key2)) {
			$this->update($type, $key1, $key2, $value, $duration);
		} else {
			$this->insert($type, $key1, $key2, $value, $duration);
		}
	}
	
	
	private function insert($type, $key1, $key2, $value, $duration = NULL) {
		
		$setDuration = '';
		if (is_null($duration)) {
			$setDuration = 'NULL';
		} else {
			$setDuration = "'" . sqlite_escape_string(time() + $duration) . "'";
		}
		
		$query = "INSERT INTO data (key1,key2,type,created,updated,expire,value) VALUES (" . 
			"'" . sqlite_escape_string($key1) . "'," . 
			"'" . sqlite_escape_string($key2) . "'," . 
			"'" . sqlite_escape_string($type) . "'," . 
			"'" . sqlite_escape_string(time()) . "'," . 
			"'" . sqlite_escape_string(time()) . "'," . 
			$setDuration . "," .
			"'" . sqlite_escape_string(serialize($value)) . "')";
		$results = $this->_db->queryExec($query);
		return $results;
	}
	
	
	private function update($type, $key1, $key2, $value, $duration = NULL) {
		
		$setDuration = '';
		if (is_null($duration)) {
			$setDuration = ", expire = NULL ";
		} else {
			$setDuration = ", expire = '" . sqlite_escape_string(time() + $duration) . "' ";
		}
		
		$query = "UPDATE data SET " . 
			"updated = '" . sqlite_escape_string(time()) . "'," . 
			"value = '" . sqlite_escape_string(serialize($value)) . "'" .
			$setDuration .
			"WHERE " . 
			"key1 = '" . sqlite_escape_string($key1) . "' AND " . 
			"key2 = '" . sqlite_escape_string($key2) . "' AND " . 
			"type = '" . sqlite_escape_string($type) . "'";
		$results = $this->_db->queryExec($query);
		# echo $query;
		# echo $this->_db>changes;
		return $results;
	}

	
	public function get($type = NULL, $key1 = NULL, $key2 = NULL) {
		$condition = self::getCondition($type, $key1, $key2);
		$query = "SELECT * FROM data WHERE " . $condition;
		$results = $this->_db->arrayQuery($query, SQLITE_ASSOC);
		
#		echo '<pre>type: ' . $type . ' key1:' . $key1 . '   ' . $query; print_r($results); exit;
		
		if (count($results) !== 1) return NULL;
		
		$res = $results[0];
		$res['value'] = unserialize($res['value']);
		return $res;
	}
	
	
	/*
	 * Return the value directly (not in a container)
	 */
	public function getValue($type = NULL, $key1 = NULL, $key2 = NULL) {
		$res = $this->get($type, $key1, $key2);
		if ($res === NULL) return NULL;
		return $res['value'];
	}
	
	public function exists($type, $key1, $key2) {
		$query = "SELECT * FROM data WHERE " . 
			"key1 = '" . sqlite_escape_string($key1) . "' AND " . 
			"key2 = '" . sqlite_escape_string($key2) . "' AND " . 
			"type = '" . sqlite_escape_string($type) . "' LIMIT 1";
		$results = $this->_db->arrayQuery($query, SQLITE_ASSOC);
		return (count($results) == 1);
	}
		
	public function getList($type = NULL, $key1 = NULL, $key2 = NULL) {
		
		$condition = self::getCondition($type, $key1, $key2);
		$query = "SELECT * FROM data WHERE " . $condition;
		$results = $this->_db->arrayQuery($query, SQLITE_ASSOC);
		if (count($results) == 0) return NULL;
		
		foreach($results AS $key => $value) {
			$results[$key]['value'] = unserialize($results[$key]['value']);
		}
		return $results;
	}
	
	public function getKeys($type = NULL, $key1 = NULL, $key2 = NULL, $whichKey = 'type') {

		if (!in_array($whichKey, array('key1', 'key2', 'type')))
			throw new Exception('Invalid key type');
			
		$condition = self::getCondition($type, $key1, $key2);
		
		$query = "SELECT DISTINCT " . $whichKey . " FROM data WHERE " . $condition;
		$results = $this->_db->arrayQuery($query, SQLITE_ASSOC);

		if (count($results) == 0) return NULL;
		
		$resarray = array();
		foreach($results AS $key => $value) {
			$resarray[] = $value[$whichKey];
		}
		
		return $resarray;
	}
	
	
	public function remove($type, $key1, $key2) {
		$query = "DELETE FROM data WHERE " . 
			"key1 = '" . sqlite_escape_string($key1) . "' AND " . 
			"key2 = '" . sqlite_escape_string($key2) . "' AND " . 
			"type = '" . sqlite_escape_string($type) . "'";
		$results = $this->_db->arrayQuery($query, SQLITE_ASSOC);
		return (count($results) == 1);
	}
	
	public function removeExpired() {
		$query = "DELETE FROM data WHERE expire NOT NULL AND expire < " . time();
		$this->_db->arrayQuery($query, SQLITE_ASSOC);
		$changes = $this->_db->changes();
		return $changes;
	}

	
	
	/**
	 * Create a SQL condition statement based on parameters
	 */
	private static function getCondition($type = NULL, $key1 = NULL, $key2 = NULL) {
		$conditions = array();
		
		if (!is_null($type)) $conditions[] = "type = '" . sqlite_escape_string($type) . "'";
		if (!is_null($key1)) $conditions[] = "key1 = '" . sqlite_escape_string($key1) . "'";
		if (!is_null($key2)) $conditions[] = "key2 = '" . sqlite_escape_string($key2) . "'";
		
		if (count($conditions) === 0) return '1';
		
		$condition = join(' AND ', $conditions);
		
		return $condition;
	}
	
	
	
	
	
	/**
	 * Store data with token
	 * @param $token token to store data by
	 * @param $data data to store (blob)
	 */
	function store($token, $data) {
		
	}

	/**
	 * Retrieve data from token
	 * @param $token token to perform lookup for
	 * @return data that was retrieved, or null when no data was found
	 */
	function lookup($token) {
		
		return $data;
	}
	
}