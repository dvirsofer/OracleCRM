<?php

include_once 'help_functions.php';

class Database {
	private $_username = 'hr';
	private $_password = 'hr';
	protected $_query;
	protected $_dbh;

	// contructor for creating a connection to the database
	public function __construct() {
		$db_props = parse_ini_file('db.ini');
		$this->_oracle_sid = $db_props['oraclesid'];
		$this->_dbh = oci_connect ( $this->_username, $this->_password, $this->_oracle_sid);
		if ($this->_dbh) {
		}
		else {
			$err = oci_error ();
			debug("Connection failed: " . $err);
			trigger_error ( htmlentities ( $err ['message'], ENT_QUOTES ), E_USER_ERROR);

		}
	}
	
	public function __destruct() {
		oci_close($this->_dbh);
		// close connection
	}

	/**
	 * execute the query and return result
	 * @param String $q - the Query
	 * @return array
	 */
	public function createQuery($q) {
		$stid = oci_parse($this->_dbh, $q);
		oci_execute($stid);
		// get results and limit it
		$result = array ();
		while (($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
			array_push ( $result, $row );
		}
		return $result;
	}

	public function fetchAll($q, $stid = null)
	{
		if (is_null($stid)) {
			$stid = oci_parse($this->_dbh, $q);
		}

		oci_execute($stid);
		// get results
		oci_fetch_all($stid, $result);

		$keys = array_keys($result);
		$objectsArray = array();

		for ($i=0; $i < count($result[$keys[0]]); $i++) {
			foreach($keys as $key) {
				@$objectsArray[$i]->$key = $result[$key][$i];
			}
		}

		return $objectsArray;
	}
	
	public function parseQuery($q) {
		$stid = oci_parse($this->_dbh, $q);
		return $stid;
	}
}
