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
	 * @return True if the group has the specified privilege.
	 */
	public function hasPrivilege($priv_name)
	{
		if (!$priv_name)
			return false;
		$cnt = DBConnector::query(
			'SELECT COUNT(*) FROM group_privileges WHERE group_id=? AND privilege=?',
			array($this->group_id, $priv_name),
			'FETCH_COLUMN'
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
}

?>