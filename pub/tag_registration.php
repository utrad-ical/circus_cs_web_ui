<?php

include("common.php");
Auth::checkSession(false);

//------------------------------------------------------------------------------
// Import $_POST variables and validation
//------------------------------------------------------------------------------
$mode = ($_POST['mode'] === "add" || $_POST['mode'] === "delete") ? $_POST['mode'] : "";
$user = Auth::currentUser();
$userID = $user->user_id;

$validator = new FormValidator();
$validator->addRules(array(
	'category' => array(
		'type' => 'int',
		'min' => 1,
		'max' => 7,
		'required' => true
	),
	'referenceID' => array(
		'type' => 'int',
		'min' => 1,
		'required' => true
	)
));

if($mode === 'add' || $mode === 'delete')
	$validator->addRules(array(
		'tag' => array(
			'type' => 'string',
			'required' => true
		)
	));

try
{
	if(!$validator->validate($_POST))
		throw new Exception(implode("\n", $validator->errors));
	$req = $validator->output;
	$dummy = new Tag();
	$req['tag'] = trim($req['tag']);

	if (($mode == 'add' || $mode == 'delete') && $req['tag'])
	{
		$targets = Tag::select(array(
			'category' => $req['category'],
			'reference_id' => $req['referenceID'],
			'tag' => $req['tag']
		));
		if ($mode == 'add')
		{
			if (count($targets) == 0)
				$dummy->save(array('Tag' => array(
					'category' => $req['category'],
					'reference_id' => $req['referenceID'],
					'tag' => $req['tag'],
					'entered_by' => $userID
				)));
		}
		if ($mode == 'delete')
		{
			foreach ($targets as $target)
				Tag::delete($target->sid);
		}
	}

	$objs = Tag::select(array(
		'category' => $req['category'],
		'reference_id' => $req['referenceID']
	));
	$tags = array();
	foreach ($objs as $item)
		$tags[] = array(
			'tag' => $item->tag,
			'entered_by' => $item->entered_by
		);

	echo json_encode(array(
		'status' => 'OK',
		'result' => array(
			'tags' => $tags
		)
	));
}
catch (Exception $e)
{
	echo json_encode(array(
		'status' => 'SysError',
		'error' => array(
			'message' => $e->getMessage()
		)
	));
}

?>
