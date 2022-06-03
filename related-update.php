<?php

// bulk update related

// parse comments

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/utils.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$debug = false;

//----------------------------------------------------------------------------------------
function find_genus($id)
{
	global $db;
	
	$genus = '';

	$sql = 'SELECT * FROM `nz` WHERE id="' . $id . '"';
	
	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$genus = $result->fields['genus'];
	}
	
	return $genus;
}


//----------------------------------------------------------------------------------------

$page = 10;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$count = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `nz_related` LIMIT ' . $page . ' OFFSET ' . $offset;
		

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{			
		$id = $result->fields['id'];
		$related_id = $result->fields['related_id'];
		
		$genus = find_genus($related_id);
		
		$sql = 'UPDATE nz SET related_id=' . $related_id . ', related_name="' . $genus . '" WHERE id=' . $id . ' AND related_id IS NULL;';
		
		echo $sql . "\n"; 


		$result->MoveNext();
	}
	
	//$done = true;
	
	//echo "-------\n";
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		echo "-- $offset\n";
		
		//if ($offset > 3000) { $done = true; }
	}
	
	
}		

?>
