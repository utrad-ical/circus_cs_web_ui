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

	public function execute($params)
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
		if (!data)
			throw new ApiOperationException('Data invalid');

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