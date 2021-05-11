<?php

// parse microcitation

function parse ($publication)
{
	// parse 
	$m = array();
	$matched = false;

	// Parse citation

	$page_pattern 		= "(?<page>(\d+|[xvlci]+))";
	$issue_pattern 		= "(\s*\((?<issue>\d+(-\d+)?)\))?";
	$volume_pattern 	= "(?<volume>\d+[A-Z]?(-\d+)?)";
	//$date_pattern 		= "(\d+\s+)?(\w+(-\w+)?)(\s+\d+)?";
	$date_pattern 		= "(\d+\s+)?([A-Z]|[a-z]|\'|-)+(\s+\d+)?";
	$journal_pattern 	= "(?<journal>.*)(\s+\((?<series>\d+)\))?";
	
	// Ann. Mag. nat. Hist., (8) 1, 129, 131.
	if (!$matched)
	{
		//echo $publication;
		
		if (preg_match('/(?<journal>.*),\s+\((?<series>\d+)\)\s+(?<volume>\d+),\s*(?<page>\d+),\s+(?<other_page>\d+)\./', $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}	
	
	// Trans. Linn. Soc. London, Zool., (2) 1, 585.	
	if (!$matched)
	{
		//echo $publication;
		
		if (preg_match('/(?<journal>.*),\s+\((?<series>\d+)\)\s+(?<volume>\d+),\s*(?<page>\d+)\./', $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}	
	
	
	// Proc. malac. Soc. London, 21, 150, 182.
	if (!$matched)
	{
		//echo $publication;
		
		$pattern = '/' . $journal_pattern . ',\s+' . $volume_pattern . ',\s+' . $page_pattern . '\,\s+/Uu';
		//echo $pattern . "\n";
		
		if (preg_match($pattern, $publication, $m)) 
		{
			//print_r($m);
			//exit();
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}		

	// modern journal with date
	if (!$matched)
	{
		//echo $publication;
		
		$pattern = '/' . $journal_pattern . '\s+' . $volume_pattern . $issue_pattern . ',\s*' . $date_pattern . ':\s*' . $page_pattern . '\.$/Uu';
		//echo $pattern . "\n";
		
		if (preg_match($pattern, $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}
	
	// Ann. Mag. nat. Hist., (8) 6, 587.
	if (!$matched)
	{
		//echo $publication;
		
		if (preg_match('/(?<journal>.*),\s+\((?<series>\d+)\)\s+(?<volume>\d+[A-Z]?(-\d+)?)[:|,]\s*(?<page>(\d+|[xvlci]+))\./', $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}	
	
	// Canad. Ent., 9, 70.
	if (!$matched)
	{
		//echo $publication;
		
		$pattern = '/' . $journal_pattern . ',\s+' . $volume_pattern . ',\s+' . $page_pattern . '\.$/Uu';
		//echo $pattern . "\n";
		
		if (preg_match($pattern, $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}
	
	// Proc. malac. Soc. London 15, 20
	if (!$matched)
	{
		// echo $publication;
		
		$pattern = '/' . $journal_pattern . '\s+' . $volume_pattern . '[,|\.]?\s+' . $page_pattern . '\.$/Uu';
		//echo $pattern . "\n";
		
		if (preg_match($pattern, $publication, $m)) 
		{
			//print_r($m);
			//exit();
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}

	
	
	if (!$matched)
	{
		//echo $publication;
		
		$pattern = '/' . $journal_pattern . '\s+' . $volume_pattern . $issue_pattern . ':\s*' . $page_pattern . '\.$/Uu';
		//echo $pattern . "\n";
		
		if (preg_match($pattern, $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}
	
	// book
	if (!$matched)
	{
		//echo $publication;
		
		$pattern = '/' . $journal_pattern . ':\s*' . $page_pattern . '\.$/Uu';
		//echo $pattern . "\n";
		
		if (preg_match($pattern, $publication, $m)) 
		{
			//print_r($m);
			//echo __LINE__ . "\n";
			$matched = true;
		}
	}
	
	
	//print_r($m);
	
	return $m;
}		


if (0)
{
	$publications = array(
		'Genus (Wroclaw) 5(1-2), 30 June: 4.',
		'Insecta Mundi 8(1-2), March-June: 81.',
		'Annalen des Naturhistorischen Museums in Wien Serie A Mineralogie und Petrographie Geologie und Palaeontologie Anthropologie und Praehistorie 96A, Dezember: 22.',
		'Vertebrata Palasiatica 32(3): 163.',
		'Canad. Ent., 9, 70.',
		'Ann. Mag. nat. Hist., (8) 1, 129, 131.'
	);
	
	foreach ($publications as $publication)
	{
		$m = parse($publication);
		print_r($m);
	}
}


