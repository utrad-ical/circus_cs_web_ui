<?php

/**
 * Model class for CIRCUS plugin.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class Plugin extends Model
{
	protected static $_table = 'plugin_master';
	protected static $_primaryKey = 'plugin_id';

	/**
	 * Returns the plugin long name, such as 'MRA-CAD_v2' etc.
	 */
	public function fullName()
	{
		return $this->plugin_name . '_v.' . $this->version;
	}
}

?>