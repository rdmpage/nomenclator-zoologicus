<?php


// export to RDF



require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//----------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set nz 'utf8'"); 


//----------------------------------------------------------------------------------------
function get_identifiers(&$hit)
{
	global $db;
	
	$sql = "SELECT * FROM nz_id WHERE id=" . $hit->id;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hit->identifiers->{$result->fields['namespace']} = $result->fields['identifier'];
		$result->MoveNext();		
	}
}

//----------------------------------------------------------------------------------------
function get_bhl(&$hit)
{
	global $db;
	
	$sql = "SELECT * FROM nz_bhl WHERE id=" . $hit->id;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hit->identifiers->bhl = $result->fields['PageID'];
		
		if ($result->fields['biostor'] != 0)
		{		
			$hit->identifiers->biostor = $result->fields['biostor'];
		}		
		
		$result->MoveNext();		
	}
}


$page = 1000;
$offset = 0;

$done = false;

$debug = false;
//$debug = true;

while (!$done)
{
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Pseudomaenas%"';
	
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Neoarct%"';
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Ventidius%"';
	
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Prionostemma"';
	$sql = 'SELECT * FROM nz WHERE genus LIKE "Prionomma"';

	$sql = 'SELECT * FROM nz WHERE genus IN ("Prionostemma", "Prionomma")';
	
	

	//$sql = 'SELECT * FROM nz WHERE author LIKE "Distant 1910%"';
	
	$sql .= ' LIMIT ' . $page . ' OFFSET ' . $offset;
			
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$hit = new stdclass;
		$hit->id = $result->fields['id'];
		$hit->genus = utf8_encode($result->fields['genus']);
		$hit->author = utf8_encode($result->fields['author']);
		$hit->publication = utf8_encode($result->fields['publication']);
		
		if ($result->fields['year'] != '')
		{		
			$hit->year = utf8_encode($result->fields['year']);
		}
		
		
		if ($result->fields['comments'] != '')
		{		
			$hit->comments = utf8_encode($result->fields['comments']);
		}

		if ($result->fields['rdmp_comments'] != '')
		{		
			$hit->rdmp_comments = utf8_encode($result->fields['rdmp_comments']);
		}

		if ($result->fields['category'] != '')
		{		
			$hit->category = utf8_encode($result->fields['category']);
		}

		if ($result->fields['extinct'] != '')
		{		
			$hit->extinct = utf8_encode($result->fields['extinct']);
		}
		
		if ($result->fields['homonym'] != '')
		{		
			$hit->homonym = utf8_encode($result->fields['homonym']);
		}

		
		$hit->identifiers = new stdclass;
		
		get_identifiers($hit);	
		get_bhl($hit);	
		
		// related name
		if ($result->fields['related_name'])
		{
			$hit->related_name = $result->fields['related_name'];
			$hit->related_id = $result->fields['related_id'];
			$hit->relationship = $result->fields['relationship'];
		}
		
		if ($debug)
		{
			print_r($hit);
		}
		
		
		// to triples
		$triples = array();
		
		$subject_id = 'http://www.ubio.org/NZ/detail.php?uid=' . $hit->id . '&d=1';
		
		// type
		$triples[] =  '<' . $subject_id . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/ontology/voc/TaxonName#TaxonName> . ';

		// ICZN
		$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#nomenclaturalCode> <http://rs.tdwg.org/ontology/voc/TaxonName#ICZN> . ';

		// name
		$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#uninomial> "' . addcslashes($hit->genus, '"') . '" . ';
		$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#nameComplete> "' . addcslashes($hit->genus, '"') . '" . ';
		$triples[] =  '<' . $subject_id . '> <http://purl.org/dc/elements/1.1/title> "' . addcslashes($hit->genus, '"') . '" . ';

		// authorship
		if (isset($hit->author))
		{
			$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#authorship> "' . addcslashes($hit->author, '"') . '" . ';		
		}
		
		// publication
		if (isset($hit->publication))
		{
			$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/Common#publishedIn> "' . addcslashes($hit->publication, '"') . '" . ';		
		}

		// year
		if (isset($hit->year))
		{
			$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#year> "' . addcslashes($hit->year, '"') . '" . ';		
		}

		// related names
		
		if (isset($hit->related_name))
		{
			$annotation_id = $subject_id . '#annotation';
			
			$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#hasAnnotation> <' . $annotation_id . '> . ';		

			$triples[] =  '<' . $annotation_id . '> <http://www.w3.org/1999/02/22-rdf-syntax-ns#type> <http://rs.tdwg.org/ontology/voc/TaxonName#NomenclaturalNote>  . ';		

			if ($hit->relationship == 'ReplacementNameFor')
			{
				$triples[] =  '<' . $annotation_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#noteType> <http://rs.tdwg.org/ontology/voc/TaxonName#replacementNameFor> . ';		
				$triples[] =  '<' . $annotation_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#subjectTaxonName> <' . $subject_id . '> . ';		
				$triples[] =  '<' . $annotation_id . '> <http://rs.tdwg.org/ontology/voc/TaxonName#objectTaxonName> <http://www.ubio.org/NZ/detail.php?uid=' . $hit->related_id . '&d=1> . ';		
			}
			
		
		}
		
	
		// identifiers
		$publishedInCitation = '';
		
		foreach ($hit->identifiers as $k => $v)
		{
			switch ($k)
			{
				case 'doi':
					if ($publishedInCitation == '')
					{
						$publishedInCitation = $subject_id . '#publishedInCitation';
						$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> <' . $publishedInCitation . '> . ';		
					}
				
					$triples[] =  '<' . $publishedInCitation . '> <http://schema.org/sameAs> <https://doi.org/' . strtolower($v) . '> . ';		
					break;
					
				case 'biostor':
					if ($publishedInCitation == '')
					{
						$publishedInCitation = $subject_id . '#publishedInCitation';
						$triples[] =  '<' . $subject_id . '> <http://rs.tdwg.org/ontology/voc/Common#publishedInCitation> <' . $publishedInCitation . '> . ';		
					}
				
					$triples[] =  '<' . $publishedInCitation . '> <http://schema.org/sameAs> <https://biostor.org/reference/' . $v . '> . ';		
					break;
										
				// to do
				case 'jstor':
				case 'handle':
					break;
					
				case 'bhl':
					break;
					
				case 'ion':
					$triples[] =  '<' . $subject_id . '> <http://schema.org/sameAs> <urn:lsid:organismnames.com:name:' . $v . '> . ';		
					break;
			
			
				default:
					break;
			}
		
		
		}
		
/*
    <tn:nameComplete>Ochyrocera jarocha</tn:nameComplete>
    <tn:genusPart>Ochyrocera</tn:genusPart>
    <tn:specificEpithet>jarocha</tn:specificEpithet>
    <tn:infraspecificEpithet/>
    <tn:authorship>Valdez-Mondragón</tn:authorship>
    <tn:year>2017</tn:year>
    <dwc:nomenclaturalCode>ICZN</dwc:nomenclaturalCode>
    <nmbe:statusString>VALID</nmbe:statusString>
*/		
		
		if ($debug)
		{
			print_r($triples);
		}
		else
		{		
			echo join("\n", $triples) . "\n\n";		
		}

		$result->MoveNext();		
	}
	
	if ($result->NumRows() < $page)
	{
		$done = true;
	}
	else
	{
		$offset += $page;
		
		// If we want to bale out and check it worked
		//if ($offset > 1000) { $done = true; }
	}
}	
	






?>