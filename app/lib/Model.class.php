<?php

/**
 * Base model class.
 * Some concepts of this class was borrowed from CakePHP.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class Model
{
	protected $_data;
	protected $_modified = false;
	protected static $_table;
	protected static $_belongsTo;
	protected static $_hasMany;
	protected static $_hasAndBelongsToMany;
	protected static $_primaryKey;

	public function __construct($id = null)
	{
		if ($id)
		{
			$this->load($id);
		}
	}

	protected function load($id)
	{
		$class = get_class($this);
		$sql =
			"SELECT * FROM {$class::$_table} WHERE {$class::$_primaryKey} = ?";
		$row = DBConnector::query($sql, array($id), 'ARRAY_ASSOC');
		$this->_data = $row;
	}

	public function find($condition)
	{
		$class = get_class($this);
		$sql =
			"SELECT * FROM {$class::$_table} WHERE ";
		foreach ($condition as $key => $value)
		{
			$conds[] = "$key = ?";
			$vals[] = $value;
		}
		$sql .= implode(', ', $conds);
		$rows = DBConnector::query($sql, $vals, 'ALL_ASSOC');
		$results = array();
		foreach ($rows as $row)
		{
			$item = new $class();
			$item->_data = $row;
			$results[] = $item;
		}
		return $results;
	}

	protected function loadBelonging($key)
	{
		$class = get_class($this);
		$assoc = $class::$_belongsTo[$key];
		$owner = new $key($this->_data[$assoc['key']]);
		$this->_data[$key] = $owner;
		return $owner;
	}

	protected function loadChildren($key)
	{
		$class = get_class($this);
		$assoc = $class::$_hasMany[$key];
		$finder = new $key();
		$children = $finder->find(array(
			$assoc['key'] => $this->_data[$class::$_primaryKey]));
		$this->_data[$key] = $children;
		return $children;
	}

	protected function loadHasAndBelongsToMany($key)
	{
		$class = get_class($this);
		$assoc = $class::$_hasAndBelongsToMany[$key];
		$foreign = new $key();
		$foreignPrimaryKey = isset($assoc['foreignPrimaryKey']) ?
			$assoc['foreignPrimaryKey'] : $foreign::$_primaryKey;
		$sql =
			"SELECT frn.* " .
			"FROM {$class::$_table} AS me, {$assoc['joinTable']} AS jn, " .
			"{$foreign::$_table} AS frn " .
			"WHERE jn.{$assoc['associationForeignKey']} = frn.{$foreignPrimaryKey} " .
			"AND jn.{$assoc['foreignKey']} = me.{$class::$_primaryKey} " .
			"AND jn.{$assoc['foreignKey']} = ?";
		$rows = DBConnector::query(
			$sql, $this->_data[$class::$_primaryKey], 'ALL_ASSOC');
		$associates = array();
		foreach ($rows as $row)
		{
			$item = new $key();
			$item->_data = $row;
			$associates[] = $item;
		}
		$this->_data[$key] = $associates;
		return $associates;
	}

	public function __get($key)
	{
		$class = get_class($this);
		if ($this->_data[$key])
			return $this->_data[$key];
		if (isset($class::$_belongsTo[$key]))
		{
			return $this->loadBelonging($key);
		}
		if (isset($class::$_hasMany[$key]))
		{
			return $this->loadChildren($key);
		}
		if (isset($class::$_hasAndBelongsToMany[$key]))
		{
			return $this->loadHasAndBelongsToMany($key);
		}
	}

	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
		$this->_modified = true;
	}

	public function save()
	{
		$this->_modified = false;
	}
}

?>