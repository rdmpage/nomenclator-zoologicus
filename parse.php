<?php

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/utils.php');
require_once(dirname(__FILE__) . '/micro.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 


$page = 2000;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$count = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `nz` LIMIT ' . $page . ' OFFSET ' . $offset;

	$sql = 'SELECT * FROM `nz` WHERE publication LIKE "Bull. Brit. Orn. Club%" LIMIT ' . $page . ' OFFSET ' . $offset;
	$sql = 'SELECT * FROM `nz` WHERE publication LIKE "Systematic Parasitology %" LIMIT ' . $page . ' OFFSET ' . $offset;
	$sql = 'SELECT * FROM `nz` WHERE year=1994 LIMIT ' . $page . ' OFFSET ' . $offset;
	$sql = 'SELECT * FROM `nz` WHERE year=1993 LIMIT ' . $page . ' OFFSET ' . $offset;
	
	//$sql = 'SELECT * FROM `nz` WHERE id=342598 LIMIT ' . $page . ' OFFSET ' . $offset;
	
		
		

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{			
		$id = $result->fields['id'];
		
		$publications = $result->fields['publication'];
		
		$parts = explode(";", $publications);
		
		$publication = $parts[0];
		
		$extra = '';
		if (count($parts) > 1)
		{
			array_shift($parts);
			$extra = trim(join(";", $parts));
		}
		
		echo "-- $publication\n";
		if ($extra != '')
		{
			echo "-- $extra\n";
		}
		
		$m = parse($publication);
		/*
		// parse 
		$m = array();
		$matched = false;

		// Parse citation
	
		$page_pattern 		= "(?<page>(\d+|[xvlci]+))";
		$issue_pattern 		= "\((?<issue>\d+(-\d+)?)\)";
		$volume_pattern 	= "(?<volume>\d+[A-Z]?)";
		$date_pattern 		= "(\d+\s+)?(\w+(-\w+)?)(\s+\d+)";
		$journal_pattern 	= "(?<journal>.*)";
	
		// journal
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/' . $journal_pattern . ',\s+' . $volume_pattern . (\s*\((?<issue>\d+)\))?,\s+' . $page_pattern . '\.?$/Uu', $publication, $m)) 
			{
				//print_r($m);
				//echo __LINE__ . "\n";
				$matched = true;
			}
		}
		
		
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/(?<journal>.*),\s+(No.\s+)?(?<volume>\d+)(\s*\((?<issue>\d+)\))?,\s+' . $page_pattern . '\.?$/Uu', $publication, $m)) 
			{
				//print_r($m);
				//echo __LINE__ . "\n";
				$matched = true;
			}
		}
		
		// Systematic Parasitology 29(1), September: 27.
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/(?<journal>.*)\s+(?<volume>\d+)(\s*\((?<issue>\d+)\))?,\s+(\d+\s+)?(\w+(-\w+)?)(\s+\d+)?:\s+' . $page_pattern . '\.?$/Uu', $publication, $m)) 
			{
				//print_r($m);
				//echo __LINE__ . "\n";
				$matched = true;
			}
		}		
		
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/(?<journal>.*)\s+(?<volume>\d+)(\((?<issue>\d+)\))?:\s+' . $page_pattern . '\.?$/Uu', $publication, $m)) 
			{
				//print_r($m);
				//echo __LINE__ . "\n";
				$matched = true;
			}
		}		
		
		
		
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/(?<journal>.*),\s+(No.\s+)?(?<volume>\d+)(\s+\((?<issue>\d+)\))?,\s+(?<page>\d+)\.?$/Uu', $publication, $m)) 
			{
				//print_r($m);
				//echo __LINE__ . "\n";
				$matched = true;
			}
		}

		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/(?<journal>.*)\s+\((?<series>\d+)\)\s+(?<volume>\d+)(\s+\((?<issue>\d+)\))?,\s+(?<page>\d+)\.?$/Uu', $publication, $m)) 
			{
				//print_r($m);
				$matched = true;
			}
		}
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/(?<journal>.*),\s+(No.\s+)?(?<volume>\d+)(\s+\((?<issue>\d+)\))?,\s+(?<page>\d+)\s+(?<extra>\[.*\])$/Uu', $publication, $m)) 
			{
				//print_r($m);
				$matched = true;
			}
		}
		
		// book
		if (!$matched)
		{
			//echo $publication;
			if (preg_match('/^(?<journal>.*),\s+(?<page>\d+)\.$/Uu', $publication, $m)) 
			{
				//print_r($m);
				//echo __LINE__ . "\n";
				$matched = true;
			}
		}
		
		
		//if (!$matched) exit();
		//print_r($m);
		*/
		
		if (count($m) > 0)
		{
			$pairs = array();
			foreach ($m as $k => $v)
			{
				if (!is_numeric($k))
				{
					switch ($k)
					{
						case 'journal':
						case 'series':
						case 'volume':
						case 'issue':
						case 'page':
							if ($v != '')
							{
								$pairs[] = "$k='" . addcslashes($v, "'") . "'";
							}
							break;
						default:
							break;
					}
				}
			}
			
			$sql = "UPDATE nz SET " . join(",", $pairs) . " WHERE id=$id;";
			echo $sql . "\n";
		}
		
		if ($extra != '')
		{
			$sql = "UPDATE nz SET publication_extra='" . addcslashes($extra, "'") . "' WHERE id=$id;";
			echo $sql . "\n";
		}
			
		
		
		$count++;

		$result->MoveNext();
	}
	
	//echo "-------\n";
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		echo "-- $offset\n";
		
		if ($offset > 3000) { $done = true; }
	}
	
	
}		

?>
