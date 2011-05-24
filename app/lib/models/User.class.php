<?php

/**
 * The model class for users.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class User extends Model
{
	protected static $_table = 'users';
	protected static $_primaryKey = 'user_id';
	protected static $_belongsTo = array(
		'Group' => array ('key' => 'group_id')
	);
}