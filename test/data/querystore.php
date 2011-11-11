<?php
// DISCLAIMER: this is a simplistic implementation for testing purposes only.
// This implementation is NOT scalable (and not performant even at 200 items).

header('Content-type: application/json');
$data = @file_get_contents('querystoreData.json');
if (!$data) {
	// no file; create/reset
	$data = array();
	$names = array('sasquatch', 'foo', 'bar', 'zaphod', 'beeblebrox');
	for ($i = 1; $i <= 200; $i++) {
		$data[] = array(
			'id' => $i,
			'name' => $names[$i % 5]
		);
	}
	file_put_contents('querystoreData.json', json_encode($data));
} else {
	$data = json_decode($data, true);
}

$datalen = count($data);

function sortfunc($a, $b) {
	global $sort, $desc;
	if ($a[$sort] == $b[$sort]) return 0;
	if ($a[$sort] > $b[$sort]) return ($desc ? -1 : 1);
	return ($desc ? 1 : -1);
}

function getIndex($id) {
	global $data, $datalen;
	// find existing item if any
	for ($i = 0; $i < $datalen; $i++) {
		if ($data[$i]['id'] == $id) return $i;
	}
	return false; // no match
}

if (isset($_POST['object'])) { // add/put
	$object = json_decode($_POST['object'], true);
	$index = getIndex($object['id']);
	
	if ($index === false) $data[] = $object;
	else $data[$index] = $object;
	
	file_put_contents('querystoreData.json', json_encode($data));
	echo($object['id']);
} elseif (isset($_POST['id'])) { // remove
	$index = getIndex((float)$_POST['id']);
	
	if ($index !== false) {
		array_splice($data, $index, 1);
		file_put_contents('querystoreData.json', json_encode($data));
		echo($id);
	}
} elseif (isset($_GET['id'])) { // get
	$index = getIndex((float)$_GET['id']);
	if ($index !== false) echo(json_encode($data[$index]));
} else { // query
	$start = isset($_GET['start']) ? $_GET['start'] : 0;
	$count = isset($_GET['count']) ? $_GET['count'] : $datalen;
	$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
	$desc = (substr($sort, 0, 1) == '-');
	if ($desc) $sort = substr($sort, 1);
	
	usort($data, 'sortfunc');
	$slice = array_slice($data, $start, $count);
	echo(json_encode(array(
		'total' => $datalen,
		'items' => $slice
	)));
}
?>
