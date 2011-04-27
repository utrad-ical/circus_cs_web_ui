<?php

/**
 * Model class for CIRCUS plugin.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Plugin
{
	/**
	 * Version string for this plugin, such as '0.5' or '2' etc.
	 * @var string
	 */
	public $version;

	/**
	 * Name of this plugin, such as 'MRA-CAD' etc.
	 * @var string
	 */
	public $name;

	/**
	 * Internal ID for this plugin (integer).
	 * @var int
	 */
	public $id;

	/**
	 * Returns the plugin long name, such as 'MRA-CAD_v2' etc.
	 */
	public function fullName()
	{
		return $this->name . '_v' . $this->version;
	}
}

?>