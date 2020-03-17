<?php

// pro- prefix, using related names

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



$page = 20;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$count = 0;

$done = false;

foreach (range('A', 'Z') as $letter) 
//$letter = 'X';
{
	$offset = 0;
	$done = false;

	while (!$done)
	{
		$sql = 'SELECT nz.id, nz.genus, nz.comments, nz_related.related_id, nz2.genus as related_name FROM `nz` 
		INNER JOIN nz_related USING(id)
		INNER JOIN nz as nz2 ON nz_related.related_id = nz2.id
		WHERE nz.comments LIKE "(pro ' . $letter . '%- %" LIMIT ' . $page . ' OFFSET ' . $offset;

		
		$sql = 'SELECT nz.id, nz.genus, nz.comments, nz_related.related_id, nz2.genus as related_name FROM `nz` 
		INNER JOIN nz_related USING(id)
		INNER JOIN nz as nz2 ON nz_related.related_id = nz2.id
		WHERE nz.comments LIKE "(emend. pro ' . $letter . '%- %" LIMIT ' . $page . ' OFFSET ' . $offset;
		
		
		//echo "-- $sql\n";		

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		while (!$result->EOF && ($result->NumRows() > 0)) 
		{			
			$id = $result->fields['id'];
			$genus = $result->fields['genus'];
			$comments = $result->fields['comments'];

			$related_id = $result->fields['related_id'];
			$related_name = $result->fields['related_name'];
		
			$matched = false;
		

			if (!$matched)
			{
				if (preg_match('/\((?<type>(emend\.?)\s+)?pro (?<genus>' . $letter . '\w+-)\s+(?<author>.*)\)/Uu', $comments, $m))
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

					
					$sql = 'UPDATE nz SET related_name="' . $related_name . '" WHERE id=' . $id . ';';
					echo $sql . "\n";					
					$sql = 'UPDATE nz SET related_id=' . $related_id . ' WHERE id=' . $id . ';';
					echo $sql . "\n";
					$sql = 'UPDATE nz SET relationship="' . $relationship_type . '" WHERE id=' . $id . ';';
					echo $sql . "\n";
					
					

				}
			}
			
			if (!$matched)
			{
				echo "Not matched: $id $comments\n";

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
