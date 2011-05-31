<?php

/**
 * The model class for users.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class User extends Model
{
	protected static $_table = 'users';
	protected static $_primaryKey = 'user_id';
	protected static $_hasAndBelongsToMany = array(
		'Group' => array(
			'joinTable' => 'user_groups',
			'foreignKey' => 'user_id',
			'associationForeignKey' => 'group_id'
		)
	);

	protected $privileges;

	protected function fetchPrivilege()
	{
		if (!$this->privileges)
		{
			$groups = $this->Group;

			$result = array();
			foreach ($groups as $group)
			{
				foreach ($group->listPrivilege() as $priv)
				{
					if (array_search($priv, $result) === false)
						$result[] = $priv;
				}
			}
			$this->privileges = $result;
		}
		return $this->privileges;
	}

	/**
	 * Returns the list of privilege that this user has.
	 * @return array The array of privilege names.
	 */
	public function listPrivilege()
	{
		return $this->fetchPrivilege();
	}

	/**
	 * Checks the privilege for this user.
	 * @param string $priv_name The privilege name.
	 * @param bool $recursive If true, upper level privileges also match.
	 * For example, checking for Auth::SERVER_OPERATION will return true
	 * when this user has Auth::SERVER_SETTINGS privilege.
	 * If false, matches the privilege of exactly the same type.
	 * @return bool True if the user has the specified privilege.
	 */
	public function hasPrivilege($priv_name)
	{
		if (!$priv_name)
			return false;
		$this->fetchPrivilege();
		if ($recursive)
		{
			// upper level privilege can match
			$pl = Auth::getPrivilegeTypes();
			if ($pl[$priv_name][2] && $this->hasPrivilege($pl[$priv_name][2]))
				return true;
		}
		return array_search($priv_name, $this->privileges) !== false;
	}

	/**
	 * Updates the group list of this user.
	 * This does not check if the group IDs are valid.
	 * Should be used with transactioning.
	 * @param array $group_list The list of group IDs.
	 */
	public function updateGroups($group_list)
	{
		$pdo = DBConnector::getConnection();
		DBConnector::query(
			'DELETE FROM user_groups WHERE user_id = ?',
			array($this->user_id),
			'SCALAR'
		);
		$sth = $pdo->prepare(
			'INSERT INTO user_groups(user_id, group_id) VALUES(?, ?)');
		foreach ($group_list as $group_id)
		{
			$sth->execute(array($this->user_id, $group_id));
		}
		$this->_data['Group'] = null;
	}
}