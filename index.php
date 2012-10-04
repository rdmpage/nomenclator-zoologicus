<?php


/*

ATTENTION: Thickbox works but causes weird shit to happen to my layout and CSS. Need better solution

*/

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	'root' , '' ,'nz');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

// how many rows to show per page
$rowsPerPage = 20;

//--------------------------------------------------------------------------------------------------
function do_query($query, $count_sql, $sql, $pageNum = 1)
{
	global $db;
	global $rowsPerPage;
	
	$numrows = 0;
	
	// How many hits?
	$result = $db->Execute($count_sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		$numrows = $result->fields['c'];
	}

	// how many pages we have when using paging?
	$maxPage = ceil($numrows/$rowsPerPage);
	
	// counting the offset
	$offset = ($pageNum - 1) * $rowsPerPage;
	
	$sql .= " LIMIT  $offset, $rowsPerPage";
	
	$query_result = new stdclass;
	$query_result->query = $query;
	$query_result->numrows 	= $numrows;
	$query_result->numpages = $maxPage;
	$query_result->page 	= $pageNum;
	$query_result->hits= array();
	
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
		$hit->rdmp_comments = $result->fields['rdmp_comments'];
		$hit->year = $result->fields['year'];
		$hit->identifiers = array();

		$query_result->hits[] = $hit;

		$result->MoveNext();		
	}
	
	
	// Annotate
	$n = count($query_result->hits);
	for ($i=0;$i<$n;$i++)
	{
		$sql = "SELECT * FROM nz_id WHERE id=" . $query_result->hits[$i]->id;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		while (!$result->EOF) 
		{
			$query_result->hits[$i]->identifiers[$result->fields['namespace']] = $result->fields['identifier'];	
			$result->MoveNext();		
		}
	}
	
	// BHL
	$n = count($query_result->hits);
	for ($i=0;$i<$n;$i++)
	{
		$sql = "SELECT * FROM nz_bhl WHERE id=" . $query_result->hits[$i]->id;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		while (!$result->EOF) 
		{
			$query_result->hits[$i]->identifiers['PageID'] = $result->fields['PageID'];	
			
			if ($result->fields['biostor'] != 0)
			{
				$query_result->hits[$i]->identifiers['biostor'] = $result->fields['biostor'];
			}
			$result->MoveNext();		
		}
	}
	
	
	if ($pageNum > 1)
	{
	   $query_result->prev = $pageNum - 1;
	   $query_result->first = 1;
	} 
	else
	{
	   $query_result->prev = 0;
	   $query_result->first = 0;
	}
	
	if ($pageNum < $maxPage)
	{
	   $query_result->next = $pageNum + 1;
	   $query_result->last = $maxPage;

	} 
	else
	{
	   $query_result->next =0;
	   $query_result->last =0;
	}
	

	return $query_result;	
}


//--------------------------------------------------------------------------------------------------
function default_display()
{
	global $db;
	global $rowsPerPage;
		
	display_top('');
	display_search_box('genus');

	echo "<h1>Nomenclator Zoologicus</h1>";
	echo '<p>by <a rel="external" href="http://iphylo.blogspot.com">Rod Page</a></p>';
	
	echo '<h2>Introduction</h2>';
	
	//
	//echo '<div style="float:right;"><img src="images/volumes.png" /></div>';
	echo '<p>This web site displays a mapping between the digitised version of <a rel="external" href="http://uio.mbl.edu/NomenclatorZoologicus/">Nomenclator Zoologicus from uBio</a>, and bibliographic databases such as the <a rel="external" href="http://www.biodiversitylibrary.org">Biodiversity Heritage Library</a> (BHL). The goal is to make it possible to find the original publication of an animal genus. If the publication exists in BHL then you can go directly to the page in the reference where the name was published.</p>';
	
	
	
	echo '<p>You can search by genus name, author and year, or publication. Examples include:</p>
	<ul>
		<li>- Search for names published by <a href="?mode=author&q=Distant+1910">Distant in 1910</a></li>
		<li>- Search for names published in <a href="?publication=Ann. Mag. nat. Hist."><i>Ann. Mag. nat. Hist.</i></a></li>
		<li>- Search for names published in <a href="?publication=Proc.%20biol.%20Soc.%20Washington"><i>Proc. biol. Soc. Washington</i></a></li>
		<li>- Search for a genus name, such as <a href="?genus=Protomyobia"><i>Protomyobia</i></a></li>
		<li>- Search for names that start <a href="?genus=Neoarct*"><i>Neoarct</i></a></li>
	</ul>';

	

	echo '<h2>Summary</h2>';
	
	echo '<p>Table below shows number of records in Nomenclator Zoologicus that have been matched to records in the <a rel="external" href="http://www.organismnames.com">Index of Organism Names</a>, together with how many records have been mapped to biblographic identifiers, such as DOIs, pages in the <a rel="external" href="http://www.biodiversitylibrary.org">Biodiversity Heritage Library</a>, and references in <a rel="external" href="http://biostor.org">BioStor</a>.</p>';
	
	
	echo '<table cellspacing="4" >';
	
	$num = 0;
	
	// 
	$sql = "SELECT COUNT(id) AS c FROM nz";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		$num = $result->fields['c'];
		echo "<tr><td>Number of records: </td><td align='right'>" . $result->fields['c'] . "</td><td></td></tr>";
	}
	
	$sql = "SELECT COUNT(id) AS c FROM nz_id where namespace='ion'";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>Index of Organism Names</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}	
	
	$sql = "SELECT COUNT(DISTINCT(id)) AS c FROM nz_bhl";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>BHL</td><td align='right'>" . $result->fields['c'] . "</td>";
		
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}	
	
	$sql = "SELECT COUNT(DISTINCT(id)) AS c FROM nz_bhl WHERE (biostor <> 0)";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>BioStor</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}		
	
	
	$sql = "SELECT COUNT(id) AS c FROM nz_id where namespace='doi'";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>DOI</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}

	$sql = "SELECT COUNT(id) AS c FROM nz_id where namespace='hdl'";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>Handle</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}
	
	$sql = "SELECT COUNT(id) AS c FROM nz_id where namespace='jstor'";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>JSTOR</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}

	$sql = "SELECT COUNT(id) AS c FROM nz_id where namespace='cinii'";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>CiNii</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}

	$sql = "SELECT COUNT(id) AS c FROM nz_id where namespace='url' AND identifier LIKE 'http://books.google%'";
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	if ($result->NumRows() == 1)
	{
		echo "<tr><td>Google Books</td><td align='right'>" . $result->fields['c'] . "</td>";
		echo '<td>' . floor(100*$result->fields['c']/$num) . '%' . "</td></tr>";
	}
	
	
	echo '</table>';
	
	display_bottom();	
	
}

//--------------------------------------------------------------------------------------------------
function display_pagination($term, $q)
{
	//echo '<hr/>';
	echo '<div style="padding-top:10px;padding-bottom:10px;">';
	if ($q->first != 0)
	{
		echo '<span class="on"><a href="?' . $term . '=' . $q->query . '&page=1">|&lt;</a></span>';	
	}
	else
	{
		echo '<span class="off">|&lt;</span>';
	}
	echo "&nbsp;";
	if ($q->prev != 0)
	{
		echo '<span class="on"><a href="?' . $term . '=' . $q->query . '&page=' . $q->prev . '">&lt;</a></span>';	
	}
	else
	{
		echo '<span class="off">&lt;</span>';
	}
	echo "&nbsp;";
	if ($q->next != 0)
	{
		echo '<span class="on"><a href="?' . $term . '=' . $q->query . '&page=' . $q->next . '">&gt;</a></span>';	
	}
	else
	{
		echo '<span class="off">&gt;</span>';
	}
	echo "&nbsp;";
	if ($q->last != 0)
	{
		echo '<span class="on"><a href="?' . $term . '=' . $q->query . '&page=' . $q->last . '">&gt;|</a></span>';	
	}
	else
	{
		echo '<span class="off">&gt;|</span>';
	}
	echo "  Showing page " . $q->page . " of " . $q->numpages . " pages";
	//echo '<hr/>';
	echo '</div>';
}

//--------------------------------------------------------------------------------------------------
function display_page($q)
{
	// Results
	echo '<div style="border-bottom:1px solid black;">';
	echo '<table cellpadding="2" cellspacing="2" width="100%">';
	
	echo '<tr>';
	echo '<th colspan="6" style="border-bottom:1px solid black;">Nomenclator Zoologicus</th>';
	echo '<td></td>';
	echo '<td colspan="1"></td>';
	echo '<th colspan="8" style="border-bottom:1px solid black;">Bibliographic identifiers</th>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<th style="border-bottom:1px solid black;">Id</th>';
	echo '<th style="border-bottom:1px solid black;">Genus</th>';
	echo '<th style="border-bottom:1px solid black;">Author</th>';
	echo '<th style="border-bottom:1px solid black;">Citation</th>';
	echo '<th style="border-bottom:1px solid black;">Comment</th>';
	echo '<th style="border-bottom:1px solid black;">Year</th>';
	echo '<th style="border-bottom:1px solid black;">Search</th>';
	echo '<th style="border-bottom:1px solid black;">ION</th>';
	echo '<th colspan="2" style="border-bottom:1px solid black;">BHL</th>';
	echo '<th style="border-bottom:1px solid black;">BioStor</th>';
	echo '<th style="border-bottom:1px solid black;">DOI</th>';
	echo '<th style="border-bottom:1px solid black;">Handle</th>';
	echo '<th style="border-bottom:1px solid black;">JSTOR</th>';
	echo '<th style="border-bottom:1px solid black;">CiNii</th>';
	echo '<th style="border-bottom:1px solid black;">URL</th>';
	echo '</tr>';
	
	foreach ($q->hits as $hit)
	{
		echo '<tr>';
		
		echo '<td align="right">';
		echo '<a rel="external" href="http://www.ubio.org/NZ/detail.php?uid=' .$hit->id . '&d=1" target="_new" title="Nomenclator Zoologicus at uBio">';
		echo $hit->id;
		echo '</a>';
		echo '</td>';

		echo '<td>';
		echo '<a href="?genus=' . $hit->genus . '">' . $hit->genus . '</a>';
		echo '</td>';
		
		echo '<td>';
		echo '<a href="?author=' . urlencode($hit->author) . '">' . str_replace(' ', '&nbsp;', $hit->author) . '</a>';
		echo '</td>';		

		echo '<td>';
		if (preg_match('/^(?<pub>.*)(?<rest>,\s+(\()?\d+.*)$/Uu', $hit->publication, $m))
		{
			echo '<a href="?publication=' . urlencode($m['pub']) . '">' . $m['pub'] . '</a>' . $m['rest'];
		}
		else
		{
			echo '<a href="?publication=' . urlencode($hit->publication) . '">' . $hit->publication . '</a>';
		}
		echo '</td>';

		echo '<td width="30px;">';
		echo $hit->comments;
		if ($hit->rdmp_comments != '')
		{
			echo '<span style="background-color:orange">' . $hit->rdmp_comments . '</span>';
		}
		echo '</td>';

		echo '<td>';
		echo $hit->year;
		echo '</td>';
		
		echo '<td align="center">';
		
		$parameters = array();
		
		$parameters['name'] = $hit->genus;
		$parameters['author'] = $hit->author;
		$parameters['publication'] = $hit->publication;
		$parameters['year'] = $hit->year;
		
		$url = 'http://biostor.org/microcitation.php?' . http_build_query($parameters);
		
		echo '<a href="' . $url . '" target="_new"><img src="images/magnifier.png" border="0"/></a>';
		echo '</td>';
		
		//-----------------
		// ION
		echo '<td align="right">';
		if (isset($hit->identifiers['ion']))
		{
			echo '<a rel="external" href="http://www.organismnames.com/details.htm?lsid=' . $hit->identifiers['ion'] 
			. '" target="_new" title="Go to name in Index of Organism Names">' . $hit->identifiers['ion'] . '</a>';
		}
		echo '</td>';
		
		//-----------------
		// BHL
		
		echo '<td align="right">';
		if (isset($hit->identifiers['PageID']))
		{
			echo '<a class="thickbox" title="BHL page ' . $hit->identifiers['PageID'] 
				. '" href="http://biostor.org/bhl_image.php?PageID=' . $hit->identifiers['PageID'] 
				. '" rel="gallery-bhl"><img border="0" src="images/picture_empty.png"></a>';
		}
		echo '</td>';
		
		echo '<td align="right">';
		if (isset($hit->identifiers['PageID']))
		{
		
			echo '<a rel="external" href="http://www.biodiversitylibrary.org/page/' . $hit->identifiers['PageID'] 
			. '" target="_new" title="BHL page">' . $hit->identifiers['PageID'] . '</a>';
		}
		echo '</td>';

		echo '<td align="right">';
		if (isset($hit->identifiers['biostor']))
		{
			echo '<a rel="external" href="http://biostor.org/reference/' . $hit->identifiers['biostor'] 
			. '" target="_new" title="Go to publivation in BioStor">' . $hit->identifiers['biostor'] . '</a>';
		}
		echo '</td>';

		
		// DOI
		echo '<td>';
		if (isset($hit->identifiers['doi']))
		{
			echo '<a rel="external" href="http://dx.doi.org/' . $hit->identifiers['doi'] 
			. '" target="_new" title="DOI for publication">' . $hit->identifiers['doi'] . '</a>';
		}
		echo '</td>';

		// Handle
		echo '<td>';
		if (isset($hit->identifiers['hdl']))
		{
			echo '<a rel="external" href="http://hdl.handle.net/' . $hit->identifiers['hdl'] 
			. '" target="_new" title="Handle for publication">' . $hit->identifiers['hdl'] . '</a>';
		}
		echo '</td>';


		echo '<td align="right">';
		if (isset($hit->identifiers['jstor']))
		{
			echo '<a rel="external" href="http://www.jstor.org/stable/' . $hit->identifiers['jstor'] 
			. '" target="_new" title="Go to publication in JSTOR">' . $hit->identifiers['jstor'] . '</a>';
		}
		echo '</td>';

		echo '<td align="right">';
		if (isset($hit->identifiers['cinii']))
		{
			echo '<a rel="external" href="http://ci.nii.ac.jp/naid/' . $hit->identifiers['cinii'] 
			. '" target="_new" title="Go to publication in CiNii">' . $hit->identifiers['cinii'] . '</a>';
		}
		echo '</td>';
		
		echo '<td align="right">';
		if (isset($hit->identifiers['url']))
		{
			echo '<a rel="external" href="'. $hit->identifiers['url'] 
			. '" target="_new" title="Go to URL">' . substr($hit->identifiers['url'], 0, 20) . '</a>';
		}
		echo '</td>';
		
		
		echo '</tr>';
	
	}
	echo '</table>';
	echo '</div>';
	echo '<p/>';
	
	
	echo '<div>';
	echo '<table>';
	echo '<tr><td>Id</td><td>Record number in digitised Nomenclator Zoologicus (click to view on uBio\'s web site)</td></tr>';
	echo '<tr><td>Author</td><td>Author of name</td></tr>';
	echo '<tr><td>Publication</td><td>Citation in Nomenclator Zoologicus (click for more names from this reference)</td></tr>';
	echo '<tr><td>Comment</td><td>Comment (if any) on record</td></tr>';
	echo '<tr><td>Year</td><td>Year name was published</td></tr>';

	echo '<tr><td><img border="0" src="images/magnifier.png"></td><td>Click to search for microcitation in BHL</td></tr>';
	
	echo '<tr><td>ION</td><td>Record for this name in the <a href="http://www.organismnames.com">Index to Organism Names</a></td></tr>';
	

	echo '<tr><td><img border="0" src="images/picture_empty.png"></td><td>Click to view image of page</td></tr>';
	echo '<tr><td>BHL</td><td>Page number in BHL. Click to view on <a href="http://www.biodiversitylibrary.org">Biodiversity Heritage Library</a> website</td></tr>';

	echo '<tr><td>BioStor</td><td>Link to reference in <a href="http://biostor.org">BioStor</a></td></tr>';
	echo '<tr><td>DOI</td><td>Link to reference on publisher\'s web site</td></tr>';
	echo '<tr><td>Handle</td><td>Link to reference in a digital repository</td></tr>';
	echo '<tr><td>JSTOR</td><td>Link to reference on <a href="http://www.jstor.org">JSTOR</a></td></tr>';
	echo '<tr><td>CiNii</td><td>Link to reference in <a href="http://ci.nii.ac.jp/">Scholarly and Academic Information Navigator</a> (CiNii)</td></tr>';
	echo '<tr><td>URL</td><td>Link to reference (or page)</td></tr>';

	echo '<tr><td></td></tr>';
	echo '</table>';
	echo '</div>';
	
}


//--------------------------------------------------------------------------------------------------
function display_genus($query, $pageNum = 1)
{
	global $config;
	global $db;
	
	$query = str_replace("*", "%", $query);
	
	$count_sql = 'SELECT COUNT(id) AS c FROM nz WHERE genus LIKE ' . $db->qstr($query);
	$sql = "SELECT * FROM nz "
	. " WHERE genus LIKE " . $db->qstr($query) 
	. " ORDER BY genus";
	
	$q = do_query($query, $count_sql, $sql, $pageNum);
	
	display_top($query);	
	display_search_box('genus');
	echo '<h2>Showing results for genus "' . $query . '"</h2>';	
	display_pagination('genus', $q);
	display_page($q);
	display_pagination('genus', $q);
	display_bottom();
}

//--------------------------------------------------------------------------------------------------
function display_author($query, $pageNum = 1)
{
	global $config;
	global $db;
	
	$count_sql = 'SELECT COUNT(id) AS c FROM nz WHERE author = ' . $db->qstr($query);
	$sql = "SELECT * FROM nz WHERE author= " . $db->qstr($query) . " ORDER BY author";
		
	$q = do_query($query, $count_sql, $sql, $pageNum);
	
	display_top($query);
	display_search_box('author');
	echo '<h2>Showing results for author "' . $query . '"</h2>';
	display_pagination('author', $q);
	display_page($q);
	display_pagination('author', $q);
	display_bottom();
}


//--------------------------------------------------------------------------------------------------
function display_publication($query, $pageNum = 1)
{
	global $config;
	global $db;
		
	$count_sql = 'SELECT COUNT(id) AS c FROM nz WHERE publication LIKE ' . $db->qstr($query . '%');
	$sql = "SELECT * FROM nz WHERE publication LIKE " . $db->qstr($query . '%') . " ORDER BY year";
	
	$q = do_query($query, $count_sql, $sql, $pageNum);
	
	
	display_top($query);
	display_search_box('publication');
	echo '<h2>Showing results for publication "' . $query . '"</h2>';	
	display_pagination('publication', $q);
	display_page($q);
	display_pagination('publication', $q);
	display_bottom();
}

//--------------------------------------------------------------------------------------------------
function display_top($query)
{
echo '<html>
	<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		
	echo '<script type="text/javascript" src="js/jquery.js"></script>';
	echo '<script type="text/javascript" src="js/thickbox.js"></script>';
	echo '<link rel="stylesheet" type="text/css" href="css/ThickBox.css" media="screen" />';
	echo '<link type="text/css" href="css/main.css" rel="stylesheet" />';
		
	$title = 'Nomenclator Zoologicus';
	if ($query != '')
	{
		$title = $query . ' - ' . $title;
	}
	echo '<title>' . $title . '</title>';
	echo '</head>
	<body>';
	
	if ($query != '')
	{
		echo '<a href="index.php">Home</a>';
	}
}

//--------------------------------------------------------------------------------------------------
function display_search_box($mode = "genus")
{
	echo '<div style="float:right;">
			<form method="get" action="index.php">
			<input type="hidden" name="mode" value="' . $mode . '" >
			<input type="search"  name="q" id="q" value="" placeholder="' . $mode . '">
			<input type="submit" value="Search" >
			</form>
		</div>
	';
}

//--------------------------------------------------------------------------------------------------
function display_bottom()
{

	echo '</body>
</html>';
}

//--------------------------------------------------------------------------------------------------
function display_search($query, $mode)
{
	switch ($mode)
	{
		case 'genus':
			display_genus($query, 1);
			break;

		case 'author':
			display_author($query, 1);
			break;

		case 'publication':
			display_publication($query, 1);
			break;
			
		default:
			default_display();
			break;
	}

}

//--------------------------------------------------------------------------------------------------
function main()
{
	global $config;
	global $debug;
	
	$query = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
	
	$pageNum = 1;
	// if $_GET['page'] defined, use it as page number
	if(isset($_GET['page']))
	{
		$pageNum = $_GET['page'];
	}
	
	// Mode
	$mode = 'genus';
	if(isset($_GET['mode']))
	{
		$mode = $_GET['mode'];
		switch ($mode)
		{
			case 'genus':
			case 'author':
			case 'publication':
				break;
				
			default:
				$mode = 'genus';
		}
	}
	

	$genus = '';
	
	if (isset($_GET['q']))
	{
		$query = $_GET['q'];
		display_search($query, $mode);
	}
	
	
	if (isset($_GET['genus']))
	{	
		$genus = $_GET['genus'];
		display_genus($genus, $pageNum);
	}
	
	if (isset($_GET['author']))
	{	
		$author = $_GET['author'];
		display_author($author, $pageNum);
	}
	
	if (isset($_GET['publication']))
	{	
		$publication = $_GET['publication'];
		display_publication($publication, $pageNum);
	}	
	

}


main();



?>