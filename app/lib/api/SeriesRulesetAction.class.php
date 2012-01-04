<?php

/**
 * Fetches/updates Series Ruleset. (undocumented API)
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class SeriesRulesetAction extends ApiAction
{
	private $_entries;

	public function requiredPrivileges()
	{
		return array(Auth::SERVER_SETTINGS);
	}

	public function execute($api_request)
	{
		$plugin_id = $api_request['params']['plugin_id'];
		$this->_entries = PluginCadSeries::select(
			array('plugin_id' => $plugin_id),
			array('order' => array('volume_id'))
		);
		if (!count($this->_entries))
			throw new ApiException('Plugin not found', ApiResponse::STATUS_ERR_OPE);

		switch ($api_request['params']['mode'])
		{
			case 'get':
				return $this->getRuleSets();
				break;
			case 'set':
				return $this->setRuleSets($plugin_id, $api_request['params']['pluginRuleSetsData']);
				break;
			default:
				throw new ApiException('Mode not specified', ApiResponse::STATUS_ERR_OPE);
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

		$res = new ApiResponse();
		$res->setResult($action, $items);
		return $res;
	}

	private function setRuleSets($plugin_id, $data)
	{
		$data = json_decode($data, true);
		if (!data)
			throw new ApiException('Data invalid', ApiResponse::STATUS_ERR_OPE);

		$pdo = DBConnector::getConnection();

		try {
			$pdo->beginTransaction();
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
		} catch (PDOException $e) {
			throw new ApiException('DB Error ' . $e->getMessage(), ApiResponse::STATUS_ERR_SYS);
		}

		$res = new ApiResponse();
		$res->setResult($action, null);
		return $res;
	}

}