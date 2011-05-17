<?php

/**
 * Model class for CIRCUS plugin.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Plugin extends Model
{
	protected static $_table = 'plugin_master';
	protected static $_primaryKey = 'plugin_id';

	protected $userPreference = array();

	/**
	 * Returns the plugin long name, such as 'MRA-CAD_v2' etc.
	 */
	public function fullName()
	{
		return $this->plugin_name . '_v.' . $this->version;
	}

	/**
	 * Loads the user preference.
	 * @param string|User $user The user ID. Default value is the current user.
	 * @return array Key/value pairs of the preference of this plugin for the
	 * specified user.
	 */
	public function userPreference($user = null)
	{
		global $DEFAULT_CAD_PREF_USER;
		if (is_a($user, 'User'))
			$userid = $user->user_id;
		else
			$userid = $user ?: $_SESSION[userID];
		if (is_array($this->userPreference[$userid]))
			return $this->userPreference[$userid];
		$sql = 'SELECT * FROM plugin_user_preference '
			. 'WHERE plugin_id = ? AND user_id IN (?, ?)';

		$items = DBConnector::query($sql,
			array($this->plugin_id, $userid, $DEFAULT_CAD_PREF_USER),
			'ALL_ASSOC');

		$prefs = array_filter($items, function($in) use ($userid) {
			return $in['user_id'] == $userid;
		});
		if (count($user_pref) == 0)
		{
			$prefs = array_filter($items, function ($in) {
				global $DEFAULT_CAD_PREF_USER;
				return $in['user_id'] == $DEFAULT_CAD_PREF_USER;
			});
		}

		$result = array();
		foreach ($prefs as $item)
		{
			$result[$item['key']] = $item['value'];
		}
		$this->userPreference[$userid] = $result;
		return $result;
	}

	/**
	 * Saves the user preference.
	 * @param string $user The user ID.
	 * @param array $values Key/value pairs of settings.
	 */
	public function saveUserPreference($user, $values)
	{
		$pdo = DBConnector::getConnection();
		$pdo->beginTransaction();
		$sql = '';
		$pdo->commit();
		// TODO: implement save user preference
	}
}

?>