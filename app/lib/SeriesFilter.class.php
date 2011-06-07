<?php

/**
 * Series filter.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SeriesFilter {
	/**
	 * Checks if the $data matches the list of filter set specified by $ruleSets.
	 * The $data is checked
	 * @param array $data The associative array to be filtered.
	 * @param mixed $ruleSets The list of filter rule set.
	 * @return mixed If $data matches one of the rule set specified by $ruleSets,
	 * returns the matched rule.
	 * If $data matched none of the rule sets, returns false.
	 */
	public function processRuleSets(array $data, array $ruleSets)
	{
		foreach ($ruleSets as $ruleSet)
		{
			$rule = $this->processOneRuleSet($data, $ruleSet);
			if ($rule !== false)
				return $rule;
		}
		return false;
	}

	/**
	 * Checks if the $data matches the specified $ruleSet.
	 * @param array $data The associative array to be filtered.
	 * @param array $ruleSet The filter rule set, like
	 * array('filter' => FILTER_NODE, 'rule' => RULE)
	 * @return mixed If $data matched the $ruleSet, returns the rule.
	 * Returns false otherwise.
	 */
	public function processOneRuleSet(array $data, array $ruleSet)
	{
		if ($this->processFilterNode($data, $ruleSet['filter']) === true)
		{
			return $ruleSet['rule'];
		}
		return false;
	}

	/**
	 * Checks if the $data matches the filter node specified by $node
	 * and return true or false.
	 * @param array $data The associative array to be checked.
	 * @param array $node The filter node (group node or comparison node).
	 * @return bool True if matched.
	 * @throws LogicException
	 */
	public function processFilterNode(array $data, array $node)
	{
		if (is_array($node))
		{
			if ($node['members'])
			{
				return $this->processGroupNode($data, $node);
			}
			else if ($node['key'])
			{
				return $this->processComparisonNode($data, $node);
			}
			throw new LogicException('Bad filter node.');
		}
		throw new LogicException('Filter node type error.');
	}

	protected function processGroupNode($data, $node)
	{
		$type = $node['group'] ?: 'and';
		$members = $node['members'];
		if (!is_array($members))
			throw new Exception('Bad group node members.');
		switch ($type)
		{
			case 'and':
				foreach ($node['members'] as $member)
					if (!$this->processFilterNode($data, $member))
						return false;
				return true;
				break;
			case 'or':
				foreach ($node['members'] as $member)
					if ($this->processFilterNode($data, $member))
						return true;
				return false;
				break;
			default:
				throw new LogicException('Bad gruop operator.');
				break;
		}
	}

	protected function processComparisonNode(array $data, array $node)
	{
		$operator = $node['condition'] ?: '=';
		if (!isset($node['key']) || !isset($node['value']))
			throw new Exception('Bad comparison filter node.');
		$cmp_target = $data[$node['key']];
		$value = $node['value'];
		switch ($operator)
		{
			case '=':
				return $value == $cmp_target;
			case '>':
				return $value > $cmp_target;
			case '<':
				return $value < $cmp_target;
			case '>=':
				return $value >= $cmp_target;
			case '<=':
				return $value <= $cmp_target;
			case '<>':
			case '!=':
				return $value != cmp_target;
			case '*=': // contains
				return strpos($cmp_target, $value) !== false;
			case '^=': // begins with
				return strpos($cmp_target, $value) == 0;
			case '$=': // ends with
				return strrpos($cmp_target, $value) == strlen($cmp_target) - strlen($value);
			default:
				throw new LogicException('Bad comparison operator.');
		}
	}
}


?>