<?php

/**
 * The model class for user group.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Group extends Model
{
	protected static $_table = 'groups';
	protected static $_primaryKey = 'group_id';
	protected static $_hasMany = array(
		'User' => array('key' => 'group_id')
	);

	/**
	 * Checks the privilege for this group.
	 * @param string $priv_name The privilege name.
	 * @param bool $recursive If true, upper level privileges also match.
	 * For example, checking for Auth::SERVER_OPERATION will return true
	 * when this group has Auth::SERVER_SETTINGS privilege.
	 * If false, matches the privilege of exactly the same type.
	 * @return bool True if the group has the specified privilege.
	 */
	public function hasPrivilege($priv_name, $recursive = true)
	{
		if (!$priv_name)
			return false;
		if ($recursive)
		{
			// upper level privilege can match
			$pl = Auth::getPrivilegeTypes();
			if ($pl[$priv_name][2] && $this->hasPrivilege($pl[$priv_name][2]))
				return true;
		}
		$cnt = DBConnector::query(
			'SELECT COUNT(*) FROM group_privileges WHERE group_id=? AND privilege=?',
			array($this->group_id, $priv_name),
			'SCALAR'
		);
		return $cnt > 0;
	}

	/**
	 * Returns the list of privilege that this group has.
	 * @return array The array of privilege names.
	 */
	public function listPrivilege()
	{
		$result = DBConnector::query(
			'SELECT privilege FROM group_privileges WHERE group_id=?',
			array($this->group_id),
			'ALL_COLUMN'
		);
		return $result;
	}

	/**
	 * Updates the privilege list of this group.
	 * @param array $priv_list The list of privilege names.
	 */
	public function updatePrivilege($priv_list)
	{
		$pl = Auth::getPrivilegeTypes();
		foreach ($pl as $priv)
			$privTypes[$priv[0]] = $priv;

		$pdo = DBConnector::getConnection();
		$pdo->beginTransaction();
		DBConnector::query(
			'DELETE FROM group_privileges WHERE group_id = ?',
			array($this->group_id),
			'SCALAR'
		);
		$sth = $pdo->prepare(
			'INSERT INTO group_privileges(group_id, privilege) VALUES(?, ?)');
		foreach ($priv_list as $priv)
		{
			if ($privTypes[$priv])
				$sth->execute(array($this->group_id, $priv));
		}
		$pdo->commit();
	}
}

?>