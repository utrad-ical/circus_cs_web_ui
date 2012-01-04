<?php
include("../common.php");
Auth::checkSession();
Auth::purgeUnlessGranted(Auth::SERVER_SETTINGS);

$validator = new FormValidator();
$validator->addRules(array(
	'mode' => array('type' => 'select', 'options' => array('add', 'delete', 'setCurrent')),
	'id' => array('type' => 'int'),
	'type' => array(
		'type' => 'select',
		'options' => array(
			Storage::DICOM_STORAGE,
			Storage::PLUGIN_RESULT,
			Storage::WEB_CACHE
		)
	),
	'ticket' => array('type' => 'str'),
	'path' => array('type' => 'str')
));

$smarty = new SmartyEx();

try
{
	if (!$validator->validate($_REQUEST))
		throw new Exception(implode("\n", $validator->errors));
	$req = $validator->output;
	$mode = $req['mode'];

	if ($mode)
	{
		try
		{
			if ($_SESSION['ticket'] != $req['ticket'])
				throw new Exception('Invalid operation. Try again.');
			$pdo = DBConnector::getConnection();
			$pdo->beginTransaction();
			$t = true;
			switch ($mode)
			{
				case 'add':
					addNewStorage($req['path'], $req['type']);
					$message = 'New storage area was successfully added.';
					break;
				case 'delete':
					deleteStorage($req['id']);
					$message = 'The selected storage area was successfully deleted.';
					break;
				case 'setCurrent':
					setStorageAsCurrent($req['id']);
					$message = 'The current storage was successfully changed.';
					break;
			}
			$pdo->commit();
			// Update storage.json file
			$area = Storage::select();
			foreach ($area as $item)
				$storageList[$item->storage_id] = $item->path;
			file_put_contents("../../config/storage.json", json_encode($storageList));
		}
		catch (PDOException $e)
		{
			if ($t) $pdo->rollBack();
			$message = 'Database error.';
		}
		catch (Exception $e)
		{
			if ($t) $pdo->rollBack();
			$message = $e->getMessage();
		}
	}

	//--------------------------------------------------------------------------
	// Make one-time ticket
	//--------------------------------------------------------------------------
	$ticket = md5(uniqid().mt_rand());
	$_SESSION['ticket'] = $ticket;
	//--------------------------------------------------------------------------

	//--------------------------------------------------------------------------
	// Retrieve storage list
	//--------------------------------------------------------------------------
	$storage = Storage::select(array(), array('order' => array('storage_id')));

	//--------------------------------------------------------------------------
	// Settings for Smarty
	//--------------------------------------------------------------------------
	$smarty->assign('storage', $storage);
	$smarty->assign('ticket', $ticket);
	$smarty->assign('message', $message);
	$smarty->display('administration/data_storage_config.tpl');
	//--------------------------------------------------------------------------
}
catch (Exception $e)
{
	$smarty->assign('message', $e->getMessage());
	$smarty->display('critical_error.tpl');
}

function addNewStorage($path, $type)
{
	if (strlen(trim($path)) == 0)
		throw new Exception('Path to the new storage area is empty.');
	if (!is_dir($path))
		throw new Exception("'$path' is not a valid directory. " .
					"Create an empty directory first.");
	$path = realpath($path);
	if (!is_writable($path))
		throw new Exception("'$path' is not writable.");
	$tmp = Storage::select(array('path' => $path));
	if (count($tmp))
		throw new Exception('Input path is already registered.');

	if ($type == Storage::DICOM_STORAGE)
	{
		if (!mkdir($path . $DIR_SEPARATOR . "tmp"))
			throw new Exception('Failed to create temporary directory. ' .
				'Check if the directory is writable.');
	}

	// Set as 'current' if no storage is already set as current
	$tmp = Storage::select(array('type' => $type));
	$currentUse = count($tmp) == 0 ? 't' : 'f';

	// Get new storage ID
	$newID = DBConnector::query(
		"SELECT nextval('storage_master_storage_id_seq')", null,
		'SCALAR'
	);

	// Save
	$newItem = new Storage();
	$newItem->save(array('Storage' => array(
		'storage_id' => $newID,
		'path' => $path,
		'current_use' => $currentUse,
		'type' => $type
	)));
}

function deleteStorage($id)
{
	$target = new Storage($id);
	if (!isset($target->path))
		throw new Exception('Storage area not found.');
	if ($target->current_use)
		throw new Exception('This storage is set as current use.');

	if ($target->type == Storage::DICOM_STORAGE)
		$sqlStr = "SELECT COUNT(*) FROM series_list WHERE storage_id=?";
	else if ($target->type == Storage::PLUGIN_RESULT)
		$sqlStr = "SELECT COUNT(*) FROM executed_plugin_list WHERE status > 0 AND storage_id=?";
	$result = DBConnector::query($sqlStr, array($id), 'SCALAR');
	if ($result > 0)
		throw new Exception('This storage area is already used.');

	$target->delete($id);
}

function setStorageAsCurrent($id)
{
	$target = new Storage($id);
	if (!isset($target->type))
		throw new Exception('Storage area not found.');
	$type = $target->type;
	DBConnector::query(
		"UPDATE storage_master SET current_use='f' WHERE type=?", $type);
	DBConnector::query(
		"UPDATE storage_master SET current_use='t' WHERE storage_id=?", $id);
}

?>
