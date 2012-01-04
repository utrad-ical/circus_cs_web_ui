<?php

/**
 * Model class for series_join_list.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SeriesJoin extends Model
{
	protected static $_table = 'series_join_list';
	protected static $_primaryKey = 'series_instance_uid';
	protected static $_tableAsSqlView = TRUE;
}
