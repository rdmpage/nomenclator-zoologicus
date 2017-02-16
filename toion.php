<?php

//Map to ION

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/utils.php');
require_once(dirname(__FILE__) . '/micro.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$debug = true;

//----------------------------------------------------------------------------------------
function find_genus($genus, $author)
{
	global $db;
	
	$hit = null;

	$sql = 'SELECT * FROM `nz` WHERE genus="' . $genus . '" AND author="' . $author . '"';
	
	echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$hit = new stdclass;
		$hit->id = $result->fields['id'];
		$hit->genus = $result->fields['genus'];
		$hit->author = $result->fields['author'];
		
	}
	
	return $hit;
}


//----------------------------------------------------------------------------------------
function find_see($author, $comment)
{
	global $db;
	
	$hit = null;

	$sql = 'SELECT * FROM `nz` WHERE author="' . $author . '" AND comments LIKE "%' . $comment . '%"';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$hit = new stdclass;
		$hit->id = $result->fields['id'];
		$hit->genus = $result->fields['genus'];
		$hit->author = $result->fields['author'];
		
	}
	
	return $hit;
}

//----------------------------------------------------------------------------------------
function match_ion($genus, $author)
{
	global $db;
	
	$hit = null;

	$sql = 'SELECT * FROM `names` WHERE nameComplete="' . $genus . '" AND taxonAuthor="' . addcslashes($author, '"') . '"';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$hit = new stdclass;
		$hit->ion = $result->fields['id'];
		$hit->genus = $result->fields['nameComplete'];
		$hit->author = $result->fields['taxonAuthor'];
	}
	else
	{
		// lets try for just author match
		
		$author = preg_replace('/\s+[0-9]{4}/', '', $author);
		$sql = 'SELECT * FROM `names` WHERE nameComplete="' . $genus . '" AND taxonAuthor LIKE "' .  addcslashes($author, '"') . '%"';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 1)
		{
			$hit = new stdclass;
			$hit->ion = $result->fields['id'];
			$hit->genus = $result->fields['nameComplete'];
			$hit->author = $result->fields['taxonAuthor'];
		}
		
		
		
	}
	
	return $hit;
}

	
	
	


$page = 10;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$count = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `nz` WHERE id > 251649 LIMIT ' . $page . ' OFFSET ' . $offset;
	
	//$sql = 'SELECT * FROM nz WHERE genus="Taenionema"';
	//$sql = 'SELECT * FROM nz WHERE author="Distant 1910"  LIMIT ' . $page . ' OFFSET ' . $offset;
	//$sql = 'SELECT * FROM nz WHERE publication LIKE "Proc. ent. Soc. Washington%"  LIMIT ' . $page . ' OFFSET ' . $offset;



	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{			
		$id = $result->fields['id'];
		$genus = $result->fields['genus'];
		$author = $result->fields['author'];
		$comments = $result->fields['comments'];
		
		echo "-- $id $genus $author $comments\n";
		
		$hit = match_ion($genus, $author);
		if ($hit)
		{
			if ($debug)
			{
				// print_r($hit);
			}
			
			//$sql = 'UPDATE nz SET ion=' . $hit->ion . ' WHERE id=' . $id . ';';
			$sql = 'REPLACE INTO nz_id(id, namespace, identifier) VALUES(' . $id . ', "ion",' . $hit->ion . ');';
			echo $sql . "\n";
		}

		
		$count++;

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
	}
	
	
}		

?>
