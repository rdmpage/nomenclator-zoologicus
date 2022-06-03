<?php

// Match micro citations to identifiers using my microcitation service

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/micro.php');
require_once(dirname(__FILE__) . '/utils.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$query = "Canad. Ent.%";

$query = "Ann. Mag. nat. Hist. (13)%";

$query = "Cah% B% M%";

$query = "Proc. malac. Soc. London%";


$query = "Ann. Mag. nat. Hist., (8)%";

$query = "Trans. Linn. Soc. London, (2)%";
//$query = "Trans. Linn. Soc. London, Zool., (2)%";

$query = "J. Wash. Acad. Sci.%";

$query = "New Zealand J. Zool.%";

$query = "Can.Ent.%";
$query = "Can. Ent.%";
//$query = "Canad. Ent.%";

$query = "Ann. ent. Soc. Amer.%";

$query = "J.Kans.ent.Soc.%";

$query = "Kontyu%";

$query = "Acta ent. sin.%";

$query = "J. Proc. Linn. Soc. London, Zool.%";



if (1)
{
	$sql = 'SELECT * FROM nz WHERE publication LIKE ' . $db->qstr($query);
	
	//$sql .= ' AND year=1931';
}
else
{
	$sql = 'SELECT * FROM nz 
	LEFT JOIN nz_bhl USING(id)
	WHERE publication LIKE ' . $db->qstr($query)
	
	;
}



if (0)
{
	$author = 'Marsh 1879';

	$sql = 'SELECT * FROM nz WHERE author = ' . $db->qstr($author);
	
	
	$sql = 'SELECT * FROM nz WHERE publication LIKE "Amer. J. Sci.%" AND year LIKE "186%" LIMIT 10';
	

	//$sql = 'SELECT * FROM nz WHERE author = "Distant 1910" AND publication LIKE "Ann%"';


	// echo $sql . "\n";
}	

if (1)
{
	$sql = 'SELECT * FROM nz WHERE id=57732';
}




$include_authors_in_search = true;
$include_authors_in_search = false;

$include_year_in_search = false;


$hits = array();

$fail = array();

$missed = array();

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$hit = new stdclass;
	$hit->id = $result->fields['id'];
	$hit->genus = $result->fields['genus'];
	$hit->author = $result->fields['author'];
	$hit->publication = $result->fields['publication'];
	$hit->comments = $result->fields['comments'];
	$hit->year = $result->fields['year'];
	$hits[] = $hit;
	
	//print_r($hit);
	
	// hack
	if (!preg_match('/\.$/', $hit->publication))
	{
		$hit->publication .= '.';
	}
	$hit->publication = preg_replace('/\s+\.$/', '.', $hit->publication);
	
	$m = parse($hit->publication);
	
	echo "-- " . $hit->publication . "\n";
	
	
	
	
	// print_r($m);	
	
	$parameters = array();
	
	switch ($m['journal'])
	{
		case 'Acta ent. sin.':
			$parameters['issn'] = '0454-6296';
			break;
		
	
		case 'Amer. J. Sci.':
			$parameters['issn'] = '0002-9599';
			break;
			
		case 'Ann. ent. Soc. Amer.':
		case 'Ann. ent. Soc. Amer., Columbus':
		case 'Ann. Ent. Soc. Amer.':
			$parameters['issn'] = '0013-8746';
			break;
	
		case 'Ann. Mag. nat. Hist.':
		case 'Ann. Mag. nat. Hist. Lond.':
			$parameters['issn'] = '0374-5481';
			break;
			
		case 'Cah Biol Mar':
		case 'Cah. Biol. mar.':
		case 'Cahiers Biol. mar.':
		case 'Cahiers de Biologie Marine':
			$parameters['issn'] = '0007-9723';
			break;

		case 'Canad. Ent.':
		case 'Can. Ent.':
		case 'Can.Ent.':
			$parameters['issn'] = '0008-347X';
			break;
			
		case 'J.Kans.ent.Soc.':
		case 'J.Kans.Ent.Soc.':
			$parameters['issn'] = '0022-8567';
			break;
			
		case 'J. Proc. Linn. Soc. London, Zool.':
			$parameters['issn'] = '1945-9475';
			break;
		
		case 'J. Wash. Acad. Sci.':
			$parameters['issn'] = '0043-0439';
			break;
			
		case 'Kontyu':
		case 'Kontyû':
			$parameters['issn'] = '0013-8770';
			break;
		

		case 'New Zealand J. Zool.':
			$parameters['issn'] = '0301-4223';
			break;
			
			
	
		case 'Proc. malac. Soc. London':
		case 'Proc. Malac. Soc. London':
			$parameters['issn'] = '0025-1194';
			break;
			
		case 'Trans. Linn. Soc. London, Zool.':
		case 'Trans. Linn. Soc. London, (2) Zool.':
			$parameters['issn'] = '1945-9440';
			break;
			
		case 'Trans. Linn. Soc. London':
		
			// Transactions of the Linnean Society of London. 2nd Series. Zoology	
			if ($hit->year >= 1875 && $hit->year <= 1936)
			{
				$parameters['issn'] = '1945-9440';
			}		
			break;
			
	
		default:
			$parameters['issn'] = '0000-0000';
			
			echo "-- Can't match journal to ISSN: " . $m['journal'] . "\n";
			
			$fail[] = $hit->publication;
			//print_r($m);	
			//exit();
			
			break;
	}
	if ($m['series'] != '')
	{
		$parameters['series'] = $m['series'];
	}
	
	if ($m['issue'] != '')
	{
		$parameters['issue'] = $m['issue'];
	}
	
	
	$parameters['volume'] = $m['volume'];
	$parameters['page'] = $m['page'];
	
	// special cases
	if ($parameters['issn'] == '0002-9599' && isset($parameters['series']))
	{
		$parameters['volume'] = 's' . $parameters['series'] . '-' . $parameters['volume'];
		unset($parameters['series']);
	}
	
	
	
	if ($include_authors_in_search)
	{
		$parameters['authors'] = $hit->author;
		$parameters['authors'] = preg_replace('/\s+[0-9]{4}/u', '', $parameters['authors']);
	}

	if ($include_year_in_search)
	{
		$parameters['year'] = $hit->year;
	}

		
	//print_r($parameters);
	
	$url = 'http://localhost/~rpage/microcitation/www/index.php?' . http_build_query($parameters);
	
	echo "--  $url\n";
		
	$json = get($url);
	
	//echo $json;
	
	$obj = json_decode($json);

	// print_r($obj);

	if (isset($obj->found) && $obj->found)
	{
		// default
		if (1)
		{
			if (count($obj->results) == 1)
			{	
				if (isset($obj->results[0]->doi))
				{
					$sql = 'REPLACE INTO nz_id(id, namespace, identifier) VALUES (' . $hit->id . ',"doi","' . $obj->results[0]->doi . '");';
				
					echo $sql . "\n";			
				}

				if (isset($obj->results[0]->jstor))
				{
					$sql = 'REPLACE INTO nz_id(id, namespace, identifier) VALUES (' . $hit->id . ',"jstor","' . $obj->results[0]->jstor . '");';
				
					echo $sql . "\n";			
				}
				
				if (isset($obj->results[0]->wikidata))
				{
					$sql = 'REPLACE INTO nz_id(id, namespace, identifier) VALUES (' . $hit->id . ',"wikidata","' . $obj->results[0]->wikidata . '");';
				
					echo $sql . "\n";			
				}
				
				if (isset($obj->results[0]->cinii))
				{
					$sql = 'REPLACE INTO nz_id(id, namespace, identifier) VALUES (' . $hit->id . ',"cinii","' . $obj->results[0]->cinii . '");';
				
					echo $sql . "\n";			
				}
				
				
			}
			
		}
		
		else
		{
		
			// If we have multiples because I have too many copies in mciro database
			foreach ($obj->results as $r)
			{
				if (isset($r->doi))
				{
					$sql = 'INSERT INTO nz_id(id, namespace, identifier) VALUES (' . $hit->id . ',"doi","' . $r->doi . '");';
				
					echo $sql . "\n";			
				}
			}
		}
		
		
		
	}
	else
	{
		$missed[] = $hit->publication;
	}
	
	$result->MoveNext();		
}

echo "\nPublication strings not matched to database\n";
print_r($missed);

echo "\nPublication strings not parsed\n";
print_r($fail);

?>