<?php

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

$debug = true;
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

// what patterns match this suffix?
$suffix_patterns = array(

	'aeetus' => '/(aetus|aëtos|aethus)/',

	'ma' => '/(mas|mum|mus|me|mia|mis)$/',
	
	'noura' => '/nura$/',

	'otoma' => '/ostoma$/',
	
	
	'za' => '/(zia|zusa|sa|zus|zias|so|zo)$/',
	'zus' => '/(zius|za|zaea)$/'
);

$suffix = 'ma';
$suffix = 'zus';

$suffixes = array(
'aeetus'
);

$suffixes = array(
'noura'
);

$suffixes = array(
'otoma'
);


foreach ($suffixes as $suffix)
{
	while (!$done)
	{
		$sql = 'SELECT * FROM `nz` WHERE comments LIKE "(pro -' . $suffix . ' %" LIMIT ' . $page . ' OFFSET ' . $offset;
	
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
				if (preg_match('/\(pro -' . $suffix . '\s+(?<author>.*)\)/Uu', $comments, $m))
				{
				
					if ($debug)
					{
						print_r($m);
					}

					$matched = true;
				
					$other_genus = $genus;
				
				
					if (isset($suffix_patterns[$suffix]))
					{
						$other_genus = preg_replace($suffix_patterns[$suffix], $suffix, $other_genus);

						if ($other_genus == $genus)
						{
							echo "-- *** suffix not replaced ***\n";
						}
						else
						{
							$hit = find_genus($other_genus, $m['author']);
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
								$sql = 'UPDATE nz SET relationship="Orthographic" WHERE id=' . $id . ';';
								echo $sql . "\n";

							}
							else
							{
								echo "-- $other_genus " . $m['author'] . " not found ***\n";
							}
						}
					}
					else
					{
						echo "*** BUG you need to set a suffix_patterns[] for $suffix *** \n";
						exit();
					
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
