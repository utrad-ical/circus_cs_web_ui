<?php
$params = array('toTopDir' => "../");
include_once("../common.php");
Auth::checkSession();

//------------------------------------------------------------------------------------------------------------------
// Import $_POST variables
//------------------------------------------------------------------------------------------------------------------
$seriesUIDArr = array();
$seriesUIDArr = explode('^', $_POST['seriesUIDStr']);
$seriesNum = count($seriesUIDArr);
//------------------------------------------------------------------------------------------------------------------

try
{
	$pdo = DBConnector::getConnection();

	$seriesList = array();

	for($j=0; $j<$seriesNum; $j++)
	{
		// Get joined series data
		$s = new SeriesJoin();
		$sdata = $s->find(array("series_instance_uid" => $seriesUIDArr[$j]));
		$seriesData = $sdata[0]->getData();
		
		$seriesList[] = array('study_id' => $seriesData['study_id'],
							  'series_number' => $seriesData['series_number'],
							  'series_date' => $seriesData['series_date'],
							  'series_time' => $seriesData['series_time'],
							  'modality' => $seriesData['modality'],
							  'image_number' => $seriesData['image_number'],
							  'series_description' => $seriesData['series_description']);
	}

	echo json_encode($seriesList);
}
catch (PDOException $e)
{
	var_dump($e->getMessage());
}
$pdo = null;

?>
