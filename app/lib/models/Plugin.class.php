<?php

/**
 * Model class for CIRCUS plugin.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Plugin extends Model
{
	/**
	 * Indicates this plugin is a CAD plugin.
	 */
	const CAD_PLUGIN = 1;

	/**
	 * Indicates this plugin is a research plugin.
	 */
	const RESERACH_PLUGIN = 2;

	protected static $_table = 'plugin_master';
	protected static $_primaryKey = 'plugin_id';
	protected static $_hasMany = array(
		'PluginCadSeries' => array('key' => 'plugin_id'),
		'CadPlugin' => array('key' => 'plugin_id')
	);

	protected $userPreference = array();

	protected $presentation = null;

	/**
	 * Returns the plugin long name, such as 'MRA-CAD_v2' etc.
	 */
	public function fullName()
	{
		return $this->plugin_name . '_v.' . $this->version;
	}

	/**
	 * Returns the plugin type in string.
	 */
	public function pluginType()
	{
		switch ($this->type)
		{
			case self::CAD_PLUGIN:
				return 'CAD';
			case self::RESERACH_PLUGIN:
				return 'Research';
			default:
				return 'Unknown';
		}
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
		if ($user instanceof User)
			$userid = $user->user_id;
		else
			$userid = $user ?: (Auth::currentUser()->user_id);
		if (is_array($this->userPreference[$userid]))
			return $this->userPreference[$userid];
		$sql = 'SELECT * FROM plugin_user_preference '
			. 'WHERE plugin_id = ? AND user_id IN (?, ?)';

		$items = DBConnector::query($sql,
			array($this->plugin_id, $userid, $DEFAULT_CAD_PREF_USER),
			'ALL_ASSOC');

		$user_prefs = array();
		$default_prefs = array();
		foreach ($items as $item)
		{
			if ($item['user_id'] == $DEFAULT_CAD_PREF_USER)
				$default_prefs[$item['key']] = $item['value'];
			else
				$user_prefs[$item['key']] = $item['value'];
		}
		$result = array_merge($default_prefs, $user_prefs);
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

	public function configurationPath()
	{
		global $WEB_UI_ROOT;
		$plugin_name = $this->fullName();
		return "$WEB_UI_ROOT/plugin/$plugin_name";
	}

	/**
	 * @return CadPresentation The instanciated presentation instance.
	 */
	public function presentation()
	{
		global $DIR_SEPARATOR;
		if ($this->presentation instanceof CadPresentation)
			return $this->presentation;
		$fileName = $this->configurationPath() . $DIR_SEPARATOR . 'presentation.json';
		$this->presentation = new CadPresentation($fileName, $this);
		return $this->presentation;
	}
}

?>