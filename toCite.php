<?php


// export to RDF



require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set nz 'utf8'"); 


//----------------------------------------------------------------------------------------
function get_identifiers(&$hit)
{
	global $db;
	
	$sql = "SELECT * FROM nz_id WHERE id=" . $hit->id;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hit->identifiers->{$result->fields['namespace']} = $result->fields['identifier'];
		$result->MoveNext();		
	}
}

//----------------------------------------------------------------------------------------
function get_bhl(&$hit)
{
	global $db;
	
	$sql = "SELECT * FROM nz_bhl WHERE id=" . $hit->id;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hit->identifiers->bhl = $result->fields['PageID'];
		
		if ($result->fields['biostor'] != 0)
		{		
			$hit->identifiers->biostor = $result->fields['biostor'];
		}		
		
		$result->MoveNext();		
	}
}


$page = 1000;
$offset = 0;

$done = false;

$debug = false;
//$debug = true;

while (!$done)
{
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Pseudomaenas%"';
	
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Neoarct%"';
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Ventidius%"';
	
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Prionostemma"';
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Prionomma"';

	$sql = 'SELECT * FROM nz WHERE genus IN ("Prionostemma", "Prionomma")';

	
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Priono%" ORDER BY genus';

	$sql = 'SELECT * FROM nz WHERE genus LIKE "Neobel%" ORDER BY genus';
	
	// all
	$sql = 'SELECT * FROM nz';

	//$sql = 'SELECT * FROM nz WHERE author LIKE "Distant 1910%"';
	
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;
			
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hit = new stdclass;
		$hit->id = $result->fields['id'];
		$hit->genus = utf8_encode($result->fields['genus']);
		$hit->author = utf8_encode($result->fields['author']);
		$hit->publication = utf8_encode($result->fields['publication']);
		
		if ($result->fields['year'] != '')
		{		
			$hit->year = utf8_encode($result->fields['year']);
		}
		
		if ($result->fields['comments'] != '')
		{		
			$hit->comments = utf8_encode($result->fields['comments']);
		}

		if ($result->fields['rdmp_comments'] != '')
		{		
			$hit->rdmp_comments = utf8_encode($result->fields['rdmp_comments']);
		}

		if ($result->fields['category'] != '')
		{		
			$hit->category = utf8_encode($result->fields['category']);
		}
		
		if ($result->fields['category'] != '')
		{		
			$hit->rdmp_category = utf8_encode($result->fields['rdmp_category']);
		}
		

		if ($result->fields['extinct'] != '')
		{		
			$hit->extinct = utf8_encode($result->fields['extinct']);
		}
		
		if ($result->fields['homonym'] != '')
		{		
			$hit->homonym = utf8_encode($result->fields['homonym']);
		}
		
		$hit->identifiers = new stdclass;
		
		get_identifiers($hit);	
		get_bhl($hit);	
		
		// related name
		if ($result->fields['related_name'])
		{
			$hit->related_name = $result->fields['related_name'];
			$hit->related_id = $result->fields['related_id'];
			$hit->relationship = $result->fields['relationship'];
		}
		
		if ($debug)
		{
			print_r($hit);
		}
		
		$rows = array();
		$rows[] = $hit->genus;
		$rows[] = 'nz:' . $hit->id;
		
		// group (higher classification)
		if (isset($hit->rdmp_category))
		{
			$rows[] = $hit->rdmp_category;
		}
		else
		{
			$rows[] = "";
		}		
		
		// wikidata
		if (isset($hit->identifiers->wikidata))
		{
			$rows[] = $hit->identifiers->wikidata;
		}
		else
		{
			$rows[] = "";
		}
		
		// bhl
		if (isset($hit->identifiers->bhl))
		{
			$rows[] = $hit->identifiers->bhl;
		}
		else
		{
			$rows[] = "";
		}
		
		// fake the fragment
		$rows[] = "";
		
		//print_r($rows);
		
		echo join("\t", $rows) . "\n";

		$result->MoveNext();		
	}
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		// If we want to bale out and check it worked
		//if ($offset > 1000) { $done = true; }
	}
}	
	






?>