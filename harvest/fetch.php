<?php

// Template to fecth URLs

require_once(dirname(__FILE__) . '/lib.php');


$base_dir = 'html';

$filename = dirname(__FILE__) . '/ids.txt';
$filename = dirname(__FILE__) . '/missing.txt';



$file_handle = fopen($filename, "r");

$count = 1;
$keys = array();

$skip = true;

while (!feof($file_handle)) 
{

	$id = trim(fgets($file_handle));
	
	echo "$id\n";

	//if ($id > 309646)
	{
		$skip = false;
	}
	if (!$skip)
	{
	
	
		$url = 'http://ubio.org/NZ/detail.php?uid=' . $id . '&d=1';
	
		//echo $url . "\n";
	
		//echo $html;
	
		$html = get($url);
	
		if ($html == '')
		{
			//exit();
		}	
		else
		{
			
			$dir = floor($id / 1000);
		
			$dir = dirname(__FILE__) . "/$base_dir/" . $dir;
			if (!file_exists($dir))
			{
				$oldumask = umask(0); 
				mkdir($dir, 0777);
				umask($oldumask);
			}
		
			$f = $dir . '/' . $id . '.html';
			$file = fopen($f, "w");
			fwrite($file, $html);
			fclose($file);
		}
	
	
		if ($count++ % 5 == 0)
		{
			$rand = rand(1000000, 3000000);
			echo '-- sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
			usleep($rand);
		}
	}
}

?>