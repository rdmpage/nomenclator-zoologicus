<?php

// Look up citation in BioStor

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/utils.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 



$query = "Aa%";


//$sql = 'SELECT * FROM nz WHERE genus LIKE ' . $db->qstr($query);

$query = 'Ann. Soc. ent. Belg%';
$query = 'Ann. Soc. ent. Belg%';
$query = 'Proc. U.S. nat. Mus.%';

//$query = 'Proc. Zool. Soc. Lond%';

//$query = 'P%';

$query = 'J. Bombay nat. Hist. Soc.%';
$query = 'Trans. ent. Soc. London%';
$query = "Ent. News%";
//$query = "Spixiana%";
//$query = "Spixiana (Muench)%";
//$query = "Proc% %ent. Soc. Wash%";
//$query='Ann% Mag% Nat% Hist%';
//$query="Ann. S. Afr. Mus.%";
//$query = 'Proc. Linn. Soc. N.S. Wales%';
//$query = "Proc. zool. Soc. London%";
//$query = 'Notes Leyden Mus.%';
//$query='Bull. Brit. Mus. Nat. Hist. (Ent.)%';

//$query = "Rev. suisse Zool%";

//$query = "Canad. Ent.%";

//$query = "Proc. biol. Soc. Washington%";

//$query = 'Zool. Anz.%';
$query = 'Mém. Soc. zool. France%';

$query = 'Bull. Brit. Orn. Cl.%';

$query = 'Genera Insect.%';
$query = 'Genera Insect., 205%';

$query = 'Bull.U.S.natn.Mus. No.286%';

$query = 'Ann. Transvaal Mus.%';

$query = 'Australian Zool.%';


if (1)
{
	$sql = 'SELECT * FROM nz WHERE publication LIKE ' . $db->qstr($query);
	
	// BHL
	//$sql .= ' AND year < 1923';
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
	$author = 'Mathews 1925';

	$sql = 'SELECT * FROM nz WHERE author = ' . $db->qstr($author);

	echo $sql . "\n";
}	

if (0)
{
	$id = 62589;
	$sql = 'SELECT * FROM nz WHERE id = '. $id;

	
}




$hits = array();

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
	
	
	
	if (preg_match('/Bull.U.S.natn.Mus.\s*No.\s*(\d+): (\d+)/', $hit->publication, $m))
	{
		$hit->publication = 'Bulletin - United States National Museum, ' . $m[1] . ', ' . $m[2] . '.';
	}
	
	
	//print_r($hit);
	
	$parameters = array();
	
	$parameters['name'] = $hit->genus;
	$parameters['author'] = $hit->author;
	$parameters['publication'] = $hit->publication;
	$parameters['year'] = $hit->year;
	
	$parameters['format'] = 'json';
	
	
	$url = 'http://direct.biostor.org/microcitation.php?' . http_build_query($parameters);
	echo "-- " . $hit->genus . " " . $hit->publication . "\n";
	
	//echo $url . "\n";
	
	$json = get($url);
	//echo $json;
	
	if ($json != '')
	{
		$obj = json_decode($json);
		
		//print_r($obj);
		
		if (count($obj->matches) != 0)
		{
			foreach ($obj->matches as $PageID => $v)
			{
				if (0)
				{
					echo $hit->id . "\t" . $PageID . "\t" . $v->score . "\t";
					if ($v->ubio)
					{
						echo "Y";
					}
					else
					{
						echo "N";
					}
					// ? biostor
					echo "\t";
					if (isset($obj->references->$PageID))
					{
						echo $obj->references->$PageID;
					}
					else
					{
						echo '';
					}
				}
				else
				{
					$keys = array();
					$values = array();
					
					$keys[] = 'id';
					$values[] = $hit->id;

					$keys[] = 'PageID';
					$values[] = $PageID;

					$keys[] = 'score';
					$values[] = $v->score;
					
					$keys[] = 'ubio';
					if ($v->ubio)
					{
						$values[] = '"Y"';
					}
					else
					{
						$values[] = '"N"';
					}
					if (isset($obj->references->$PageID))
					{
						$keys[] = 'biostor';
						$values[] = $obj->references->$PageID;
					}		
					
					echo "INSERT IGNORE INTO nz_bhl(" . join(",", $keys) . ') VALUES (' . join(',', $values) . ');';
					
					
					
					
				
				
				
				}
				echo "\n";
			}
		}
	}
	
	//$url = http://biostor.org/microcitation.php?name=Canoixus&author=Roelofs+1873&publication=Ann.+Soc.+ent.+Belgique%2C+16%2C+172&year=1873&format=html
	
	
	$result->MoveNext();		
}

?>