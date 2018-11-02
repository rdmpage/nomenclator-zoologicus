<?php

// Process a list of missing files

require_once(dirname(__FILE__) . '/simplehtmldom_1_5/simple_html_dom.php');



$basedir = 'html';

$filename = dirname(__FILE__) . '/ids.txt';
$filename = dirname(__FILE__) . '/missing.txt';

$file_handle = fopen($filename, "r");

while (!feof($file_handle)) 
{

	$id = trim(fgets($file_handle));
	$dir = floor($id / 1000);
	
	$f = $dir . '/' . $id . '.html';
	
	if (file_exists($basedir . '/' . $f))
	{
	
	$html = file_get_contents($basedir . '/' . $f);

	$dom = str_get_html($html);

	$obj = new stdclass;
	$obj->id = $id;

	$trs = $dom->find('tr');
	foreach ($trs as $tr)
	{			
		$key = '';
		//echo $tr->plaintext . "\n";
		//echo "------\n";
		$tds = $tr->find('td[class=menuNormal]');
		
		foreach ($tds as $td)
		{
			//echo $td->plaintext . "\n";
			
			if ($key == '')
			{
				$key = $td->plaintext;
			}
			else
			{
				$obj->{$key} = $td->plaintext;
			}
			
			//echo $key;
		}
		//echo $key . "\n";	
		//echo "------\n";	
	}
	
	//print_r($obj);
	
	
	// linked records
	// <table><tr valign=top><td><fieldset><legend>There are additional records that cross-reference this record.</legend><table border=0><tr><td>name</td><td>authority</td><td>publication</td><td>comments</td><td>category</td></tr><tr><td><A href=/NZ/detail.php?uid=8016&d=1>Amechamus</A></td><td>Boucomont 1911</td><td>Ann. Soc. ent. France, 79, 1910, 341.</td><td>(err. pro -anus Horn 1870)</td><td>Col</td></tr><tr><td><A href=/NZ/detail.php?uid=8017&d=1>Amechana</A></td><td>Thomson 1864</td><td>Syst. Cerambycid., 85.</td><td></td><td>Col</td></tr><tr><td><A href=/NZ/detail.php?uid=27235&d=1>Bradycinetus</A></td><td>Horn 1871</td><td>Trans. Amer. ent. Soc., 3, 334.</td><td>(n.n. pro Amechanus Horn 1870) (Geotrupid.)</td><td>Col</td></tr></fieldset></td></tr></table></table>
	
	$trs = $dom->find('fieldset table[border=0] tr td');
	foreach ($trs as $tr)
	{			
		$as = $tr->find('A');
		
		foreach ($as as $a)
		{
			//echo $a->href . "\n";
			
			if (preg_match('/detail.php\?uid=(?<id>\d+)&d=1/',$a->href, $m))
			{
				$obj->related[$m['id']] = $a->plaintext;
			}
		}
	}
	
	//print_r($obj);		
	
	// export 
	
	if (isset($obj->Category))
	{
		echo 'UPDATE nz SET `category`="' . str_replace('"', '', $obj->Category)  .'" WHERE id=' . $id . ';' . "\n";
	}		

	if (isset($obj->Extinct))
	{
		if ($obj->Extinct == 'yes')
		{
			echo 'UPDATE nz SET `extinct`="y" WHERE id=' . $id . ';' . "\n";
		}
	}		

	if (isset($obj->Homonym))
	{
		if ($obj->Homonym == 'yes')
		{
			echo 'UPDATE nz SET `homonym`="y" WHERE id=' . $id . ';' . "\n";
		}
	}		
	
	
	if (isset($obj->related))
	{
		foreach ($obj->related as $k => $v)
		{
			echo 'REPLACE INTO nz_related(id, related_id) VALUES(' . $id . ',' . $k . ');' . "\n";
		}
	}
	
	}		
}

?>