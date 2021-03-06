<?php

/**
 * Base model class.
 * Some concepts of this class was borrowed from CakePHP.
 *
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
abstract class Model implements Iterator
{
	protected $_data;

	protected static $_table;
	protected static $_belongsTo;
	protected static $_hasMany;
	protected static $_hasAndBelongsToMany;
	protected static $_primaryKey;
	protected static $_sequence;
	protected static $_tableAsSqlView;

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

	public static function select($condition = array(), $options = array())
	{
		$table = static::$_table;
		$sql = "SELECT * FROM $table";
		$conds = array();
		$vals  = array();
		foreach ($condition as $key => $value)
		{
			if (preg_match('/^(.+?)\s*(=|<|<=|>|>=|<>|like)$/', $key, $m))
			{
				$key = $m[1];
				$op = $m[2];
			}
			else
				$op = '=';
			$conds[] = "$key $op ?";
			$vals[] = $value;
		}
		if (count($condition)) {
			$sql .= ' WHERE ' . implode(' AND ', $conds);
		}

		if (isset($options['order']) && is_array($options['order']))
		{
			$sql .= ' ORDER BY ' . implode(', ', $options['order']);
		}
		if (isset($options['limit']) && is_array($options['limit']))
		{
			$sql .= ' LIMIT ' . implode(', ', $options['limit']);
		}
		if (isset($options['limit']) && is_scalar($options['limit']))
		{
			$sql .= ' LIMIT ' . $options['limit'];
		}
		$rows = DBConnector::query($sql, $vals, 'ALL_ASSOC');
		$results = array();
		if (!is_array($rows)) return $results;
		foreach ($rows as $row)
		{
			$item = new static();
			$item->_data = $row;
			$results[] = $item;
		}
		return $results;
	}

	public static function selectOne($condition = array(), $options = array())
	{
		$tmp = static::select($condition, $options);
		if (count($tmp) > 0)
			return $tmp[0];
		else
			return null;
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
		$children = $key::select(array(
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
		if (isset($this->_data[$key]))
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

	public function __isset($name)
	{
		return isset($this->_data[$name]);
	}

	/**
	 * Update the current data, or save new data.
	 * @param array $data The data to be saved.
	 * @param bool $upsert Whether to update existing record if already exists.
	 * @throws BadMethodCallException
	 */
	public function save(array $data, $upsert = false)
	{
		$class = get_class($this);
		$table = static::$_table;
		$pkey = static::$_primaryKey;
		$obj = $data[$class];
		$tableAsSqlView = $class::$_tableAsSqlView;

		if($tableAsSqlView)
			throw new BadMethodCallException('You can not save to SQL view');

		if ($upsert)
		{
			$dummy = static::selectOne(array($pkey => $obj[$pkey]));
			if ($dummy)
			{
				$dummy->save($data);
				return;
			}
		}

		if ($this->_data[$pkey])
		{
			// update
			$sql = "UPDATE $table SET " .
				implode(', ', array_map(function($k) {
					return "$k=?";
				}, array_keys($obj))) .
				"WHERE $pkey=?";
			$binds = array_values($obj);
			$binds[] = $this->_data[$pkey];
			DBConnector::query($sql, $binds);
		}
		else
		{
			// save new
			$sql = "INSERT INTO $table (" .
				implode(', ', array_keys($obj)) .
				") VALUES (" .
				implode(', ', array_fill(0, count($obj), '?')) .
				")";
			DBConnector::query($sql, array_values($obj));
			if (!$obj[$pkey] && $class::$_sequence)
			{
				$new_id = DBConnector::query(
					"SELECT currval('{$class::$_sequence}')", array(), 'SCALAR');
				$this->_data[$pkey] = $new_id;
			}
		}
		foreach ($obj as $k => $v)
		{
			$this->_data[$k] = $v;
		}
	}

	public function getData()
	{
		return $this->_data;
	}

	public static function delete($id)
	{
		$tbl = static::$_table;
		$pkey = static::$_primaryKey;
		$sql = "DELETE FROM $tbl WHERE $pkey = ?";
		DBConnector::query($sql, array($id), 'SCALAR');
	}

	/**
	 * Locks table (uses exclusive lock).
	 */
	public static function lock()
	{
		DBConnector::query('LOCK TABLE ' . static::$_table, null, 'SCALAR');
	}

	// Iterators
	public function rewind() { reset($this->_data); }
	public function current() { return current($this->_data); }
	public function key() { return key($this->_data); }
	public function next() { return next($this->_data); }
	public function valid() {
		$key = key($this->_data);
		return ($key !== null && $key !== false);
	}
}
