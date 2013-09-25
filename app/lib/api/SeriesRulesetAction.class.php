<?php

/**
 * Fetches/updates Series Ruleset. (undocumented API)
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SeriesRulesetAction extends ApiActionBase
{
	private $_entries;

	protected static $required_privileges = array(
		Auth::SERVER_SETTINGS
	);

	protected function execute($params)
	{
		$plugin_id = $params['plugin_id'];
		$this->_entries = PluginCadSeries::select(
			array('plugin_id' => $plugin_id),
			array('order' => array('volume_id'))
		);
		if (!count($this->_entries))
			throw new ApiOperationException('Plugin not found');

		switch ($params['mode'])
		{
			case 'get':
				return $this->getRuleSets();
				break;
			case 'set':
				return $this->setRuleSets($plugin_id, $params['pluginRuleSetsData']);
				break;
			default:
				throw new ApiOperationException('Mode not specified');
		}
	}

	private function getRuleSets()
	{
		$items = array();
		foreach ($this->_entries as $item)
			$items[$item->volume_id] = array(
				'label' => $item->volume_label,
				'ruleset' => json_decode($item->ruleset)
			);
		return $items;
	}

	private function setRuleSets($plugin_id, $data)
	{
		$data = json_decode($data, true);
		if (!is_array($data))
		{
			throw new ApiOperationException('Data invalid');
		}

		$validate_rules = array(
			'start_img_num' => array('type' => 'int', 'min' => 0, 'default' => 0),
			'end_img_num' => array('type' => 'int', 'min' => 0, 'default' => 0),
			'required_private_tags' => array(
				'type' => 'string',
				'regex' => '/^([0-9A-Fa-f]{4},[0-9A-Fa-f]{4};)*([0-9A-Fa-f]{4},[0-9A-Fa-f]{4})?$/'
			),
			'image_delta' => array('type' => 'int', 'default' => 0),
			'environment' => array('type' => 'string', 'regex' => '/^[\w,.-]*$/', 'default' => ''),
			'continuous' => array('type' => 'bool')
		);
		$validator = new FormValidator();
		$validator->addRules($validate_rules);

		foreach ($data as $vol_id => $ruleSetList)
		{
			if (!is_array($ruleSetList))
				throw new ApiOperationException('Data invalid: malformed ruleset list');
			foreach ($ruleSetList as $ruleSet)
			{
				// throw new ApiAuthException(json_encode($ruleSet['rule']));
				if (!is_array($ruleSet['filter']) || !is_array($ruleSet['rule']))
					throw new ApiOperationException('Data invalid: malformed ruleset');
				if (!$validator->validate($ruleSet['rule']))
					throw new ApiOperationException(implode("\n", $validator->errors));
				$ruleSet['rule'] = $validator->output;
			}
		}

		$pdo = DBConnector::getConnection();

		try {
			$pdo->beginTransaction();
			$t = true;
			foreach ($this->_entries as $volume_id => $item)
			{
				$rulesets = $data[$volume_id];
				DBConnector::query(
					'UPDATE plugin_cad_series SET ruleset = ? ' .
					'WHERE plugin_id = ? AND volume_id = ?',
					array(json_encode($rulesets), $plugin_id, $volume_id)
				);
			}
			$pdo->commit();
		} catch (Exception $e) {
			if ($t) $pdo->rollBack();
			throw $e;
		}
		return null;
	}

}