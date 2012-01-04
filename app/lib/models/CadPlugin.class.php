<?php

/**
 * CadPlugin is a model class of CAD plugin.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class CadPlugin extends Model
{
	protected static $_table = 'plugin_cad_master';
	protected static $_primaryKey = 'plugin_id';
	protected static $_belongsTo = array(
		'Plugin' => array('key' => 'plugin_id')
	);
}

?>