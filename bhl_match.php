<?php

// Match NZ to a BHL item


$config['cache']   = dirname(__FILE__) . '/cache';
$config['api_key'] = '0d4f0303-712e-49e0-92c5-2113a5959159';

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/micro.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set nz 'utf8'"); 



//----------------------------------------------------------------------------------------
function get($url)
{
	$data = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	curl_setopt ($ch, CURLOPT_HEADER,		  1);  
	
	// timeout (seconds)
	curl_setopt ($ch, CURLOPT_TIMEOUT, 120);

	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST,		  0);  
	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER,		  0);  
	
	$curl_result = curl_exec ($ch); 
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		
		// print_r($info);		
		 
		$header = substr($curl_result, 0, $info['header_size']);
		
		// echo $header;
		
		//exit();
		
		$data = substr($curl_result, $info['header_size']);
		
	}
	return $data;
}



//----------------------------------------------------------------------------------------
function get_item($ItemID, $force = false)
{
	global $config;

	// get BHL item
	$filename = $config['cache'] . '/' . $ItemID . '.json';

	if (!file_exists($filename) || $force)
	{
		$url = 'https://www.biodiversitylibrary.org/api2/httpquery.ashx?op=GetItemMetadata&itemid=' 
			. $ItemID . '&ocr=t&pages=t&apikey=' . $config['api_key'] . '&format=json';
			
		// echo $url . "\n";

		$json = get($url);
		file_put_contents($filename, $json);
	}

	$json = file_get_contents($filename);
	$item_data = json_decode($json);
	
	return $item_data;

}


//----------------------------------------------------------------------------------------



$ItemID = 42909;
$sql = 'SELECT * FROM nz WHERE publication LIKE "Arch. ent. Paris, 1,%"';

$item = get_item($ItemID);


// index by page name

$pages = array();

foreach ($item->Result->Pages as $Page)
{
	// print_r($Page->PageNumbers);
	
	if (count($Page->PageNumbers) == 1)
	{
		if (preg_match('/Page\%(?<page>\d+)/', $Page->PageNumbers[0]->Number, $m))
		{
			$number = $m['page'];
			
			if (!isset($pages[$number]))
			{
				$pages[$number] = array();
			}
			$data = new stdclass;
			$data->OcrText = $Page->OcrText;
			$data->PageID = $Page->PageID;
			
			
			$pages[$number][] = $data;
		}
	
	
	}
}

// print_r($pages);

// exit();

// get NZdata

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$hit = new stdclass;
	$hit->id = $result->fields['id'];
	$hit->genus = utf8_encode($result->fields['genus']);
	$hit->author = utf8_encode($result->fields['author']);
	$hit->publication = utf8_encode($result->fields['publication']);
	
	// get page number
	
	$m = parse($hit->publication);
	
	// print_r($m);
	
	if (isset($m['page']))
	{
		$page_number = $m['page'];
		
		echo "-- $page_number\n";
		echo "-- " . $hit->genus . "\n";
	
		if (isset($pages[$page_number]))
		{
			if (count($pages[$page_number]) == 1)
			{
				$score = 3;
			
				// echo "Name = " . $hit->genus . "\n";
				// echo "$page_number = " . $pages[$page_number][0]->PageID . "\n";
				
				// test for name itself
				$text = $pages[$page_number][0]->OcrText;
				
				
				$text = preg_replace('/\n/', '', $text);
				$text = preg_replace('/[\.|,]/', '', $text);
				$text = strtolower($text);
				
				//echo $text;
				
				$target = strtolower($hit->genus);
				
				$pos = strpos($text, $target);
				
				if ($pos === false)
				{
					// Approx match?
					
					$target_parts = explode(' ', $target);			
					$num_parts = count($target_parts);
					
					$parts = explode(' ', $text);
					
					$n = count($parts);
					$found = false;
					$i = 0;
					while ($i < $n - $num_parts && !$found)
					{
						$j = 0;
						$p = array();
						while ($j < $num_parts)
						{
							$p[] = $parts[$i + $j];
							$j++;
						}
					
						$s = join(' ', $p);
						
						$d = levenshtein($s, $target);
						
						if ($d <= 2) {
							$found = true;
							$score = $d;
						}
						
						
						$i++;
						
					}				
				
				}
				else
				{
					// Exact match
					$score = 0;
				}
				echo "-- Score: $score\n";
					
					
				if ($score <= 2)
				{
					// SQL to update database					
					echo 'REPLACE INTO nz_bhl(id,PageID,score) VALUES(' . $hit->id . ',' . $pages[$page_number][0]->PageID . ',' . $score . ');' . "\n";
				}
				
			
			}
		
		}
	
	
	}
	
	


	$result->MoveNext();		
}


?>




