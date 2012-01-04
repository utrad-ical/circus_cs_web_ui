<?php

class PluginResultPolicy extends Model
{
	protected static $_table = 'plugin_result_policy';
	protected static $_primaryKey = 'policy_id';

	/**
	 * Utility method that checks if the allow_** parameters contains
	 * the name of groups specified by $groups.
	 * @param string $allow_list The comma-separated list of group IDs.
	 * @param array $groups array of group IDs or Group instances to check.
	 * @return bool If $list_str contains at least one of the groups
	 * specified by $groups, returns true. Otherwise returns false.
	 */
	public function searchGroup($allow_list, array $groups)
	{
		if (strlen($allow_list) == 0 || is_null($allow_list))
			return true;
		$tmp = preg_split('/,/', $allow_list);
		$allow_groups = array();
		foreach ($tmp as $gp)
		{
			$gp = trim($gp);
			if ($gp) $allow_groups[] = $gp;
		}
		foreach ($groups as $group)
		{
			if ($group instanceof Group)
				$group_id = $group->group_id;
			else
				$group_id = $group;
			if (array_search($group_id, $allow_groups) !== FALSE)
				return true;
		}
		return false;
	}
}
