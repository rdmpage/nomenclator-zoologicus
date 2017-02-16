<?php

// parse comments

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/utils.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$debug = false;

//----------------------------------------------------------------------------------------
function find_genus($genus, $author)
{
	global $db;
	
	$hit = null;

	$sql = 'SELECT * FROM `nz` WHERE genus="' . $genus . '" AND author="' . addcslashes($author, '"') . '"';
	
	//echo $sql . "\n";

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$hit = new stdclass;
		$hit->id = $result->fields['id'];
		$hit->genus = $result->fields['genus'];
		$hit->author = $result->fields['author'];
		
	}
	else
	{
		// square brackets?
		$author = preg_replace('/([0-9]{4})/', '[$1]', $author);
		$sql = 'SELECT * FROM `nz` WHERE genus="' . $genus . '" AND author="' . addcslashes($author, '"') . '"';
	
		//echo $sql . "\n";

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 1)
		{
			$hit = new stdclass;
			$hit->id = $result->fields['id'];
			$hit->genus = $result->fields['genus'];
			$hit->author = $result->fields['author'];
		
		}
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

$page = 20;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$count = 0;

$done = false;

foreach (range('A', 'Z') as $letter) 
//$letter = 'B';
{
	$offset = 0;
	$done = false;

	while (!$done)
	{
		$sql = 'SELECT * FROM `nz` WHERE comments LIKE "(pro ' . $letter . '%" LIMIT ' . $page . ' OFFSET ' . $offset;
		$sql = 'SELECT * FROM `nz` WHERE comments LIKE "(emend. pro ' . $letter . '%" LIMIT ' . $page . ' OFFSET ' . $offset;
		
	
		echo "-- $sql\n";
		

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		while (!$result->EOF && ($result->NumRows() > 0)) 
		{			
			$id = $result->fields['id'];
			$genus = $result->fields['genus'];
			$author = $result->fields['author'];
			$comments = $result->fields['comments'];
		
			echo "-- $id $genus $author $comments\n";
			$matched = false;
		

			if (!$matched)
			{
				if (preg_match('/\((?<type>(emend\.?)\s+)?pro (?<genus>' . $letter . '\w+)\s+(?<author>.*)\)/Uu', $comments, $m))
				{
					switch ($m['type'])
					{
						case 'emend ':
						case 'emend. ':
							$relationship_type = 'Emendation';
							break;
							
						default:
							$relationship_type = 'Orthographic';
							break;
					}
					
					if ($debug)
					{
						print_r($m);
					}

					$matched = true;

					$hit = find_genus($m['genus'], $m['author']);
					if ($hit)
					{

						if ($debug)
						{
							print_r($hit);
						}

						$sql = 'UPDATE nz SET related_name="' . $hit->genus . '" WHERE id=' . $id . ';';
						echo $sql . "\n";					
						$sql = 'UPDATE nz SET related_id=' . $hit->id . ' WHERE id=' . $id . ';';
						echo $sql . "\n";
						$sql = 'UPDATE nz SET relationship="' . $relationship_type . '" WHERE id=' . $id . ';';
						echo $sql . "\n";

					}
					else
					{
						echo "-- " . $m['genus'] . " " . $m['author'] . " not found ***\n";
					}
				}
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
		
			//if ($offset > 3000) { $done = true; }
		}
	}	
}		

?>
