<?php

// Map groups to ION classificxation or other higer taxa groups.
// Goal is t get clean, full names for each of mapping to other databases

require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	'root' , '' ,'ion');

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$db->EXECUTE("set names 'utf8'"); 

$debug = true;



//----------------------------------------------------------------------------------------
function get_ion_groups($group)
{
	global $db;
	
	$groups = array();
	
	$group_sql = '';
	
	switch ($group)
	{
		// Arachn
		case 'Arachn':
			$group_sql = '"%-Arachnida%"';
			break;		
		
		// Orthoptera
		case 'Orth':
			$group_sql = '"%-Orthoptera%"';
			break;
	
		default:
			$group_sql = '"%-' . $group . '%"';
			break;
			
	}
	
	$sql = 'SELECT * FROM `names_groups` WHERE `key` LIKE ' . $group_sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$groups[] = '"' . $result->fields['name'] . '"';
		$result->MoveNext();
	}	
	
	// print_r($groups);
	
	return $groups;
	

}



$nz_groups = array(
"Dipt",
"Dipt ( Bibionid.",
"Dipt (Acalypt.).",
"Dipt (Agromyzid.",
"Dipt (Anthomyiid",
"Dipt (Anthomyzid",
"Dipt (Asilid.).",
"Dipt (Bibionid.)",
"Dipt (Blepharoce",
"Dipt (Bombyliid.",
"Dipt (Borborid.)",
"Dipt (Calliphori",
"Dipt (Calypt.)",
"Dipt (Calypt.).",
"Dipt (Cecidomyii",
"Dipt (Ceratopogo",
"Dipt (Chironomid",
"Dipt (Chloropid.",
"Dipt (Coelopid.)",
"Dipt (Conopid.)",
"Dipt (Conopid.).",
"Dipt (Cordylurid",
"Dipt (Culicid.).",
"Dipt (Cyrthophle",
"Dipt (Dexiid.)",
"Dipt (Dexiid.).",
"Dipt (Dexiinae).",
"Dipt (Diopsid.).",
"Dipt (Dolichopod",
"Dipt (Drosophili",
"Dipt (Dryomyzid.",
"Dipt (Empid.)",
"Dipt (Empid.).",
"Dipt (Ephydrid.)",
"Dipt (Geomyzid.)",
"Dipt (Helomyzid.",
"Dipt (Heteroneur",
"Dipt (Hippobosci",
"Dipt (larva)",
"Dipt (Larva).",
"Dipt (larval).",
"Dipt (Lauxaniid.",
"Dipt (Leptid.)",
"Dipt (Leptid.).",
"Dipt (Lonchaeid.",
"Dipt (Micropezid",
"Dipt (Milichiid.",
"Dipt (Muscid).",
"Dipt (Muscid.)",
"Dipt (Muscid.).",
"Dipt (Muscid., C",
"Dipt (Muscid., P",
"Dipt (Mycetophil",
"Dipt (Nemestrini",
"Dipt (Oestrid.).",
"Dipt (Ortalid.)",
"Dipt (Ortalid.).",
"Dipt (Phasiinae)",
"Dipt (Phorid.)",
"Dipt (Phorid.).",
"Dipt (Pipunculid",
"Dipt (Platypezid",
"Dipt (Psilid.).",
"Dipt (Psychodid.",
"Dipt (Ptychopter",
"Dipt (Rhopalomer",
"Dipt (Sapromyzid",
"Dipt (Sarcophagi",
"Dipt (Scatomyzid",
"Dipt (Sciarid.)",
"Dipt (Sciarid.).",
"Dipt (Sciomyzid.",
"Dipt (sic)",
"Dipt (Simuliid.)",
"Dipt (Stratiomyi",
"Dipt (Syrphid.)",
"Dipt (Syrphid.).",
"Dipt (Tabanid.)",
"Dipt (Tabanid.).",
"Dipt (Tachinid.)",
"Dipt (Tachininae",
"Dipt (Tanyderid.",
"Dipt (Tanypezid.",
"Dipt (Therevid.)",
"Dipt (Tipulid.)",
"Dipt (Tipulid.).",
"Dipt (Trypetid.)",
"Dipt,",
"Dipt.",
"Dipt. (Cecidomyi",
"Dipt. (Stratiomy",
"Dipt.(Anthomyiid",
);

$nz_groups=array("Moll  (Problemat",
"Moll (? Coel.)",
"Moll (Achatinid.",
"Moll (Aegocerati",
"Moll (Aeolidiid.",
"Moll (Baicaliid.",
"Moll (Blainville",
"Moll (Buccinid.)",
"Moll (Celyptopto",
"Moll (Cerithiid.",
"Moll (Cerithiida",
"Moll (Chit.).",
"Moll (Clausiliid",
"Moll (Columbelli",
"Moll (Conic.)",
"Moll (Conid.)",
"Moll (Cyclophori",
"Moll (Cypraeid.)",
"Moll (Discocerat",
"Moll (Endodontid",
"Moll (Enid.).",
"Moll (Eolidid.).",
"Moll (Fasciolari",
"Moll (Fusid.).",
"Moll (Helicid.)",
"Moll (Helicid.).",
"Moll (Helicinid.",
"Moll (Hipponycid",
"Moll (Hydrobiid.",
"Moll (Hyolith.).",
"Moll (Hyolitha)",
"Moll (Hyolithida",
"Moll (Ischnochit",
"Moll (Lam.)",
"Moll (Lam.).",
"Moll (Lamell.)",
"Moll (Lamell.).",
"Moll (Lamellibra",
"Moll (Laternulid",
"Moll (Leach)",
"Moll (Lepidopleu",
"Moll (Limacid.).",
"Moll (Limacidae)",
"Moll (Limacinid.",
"Moll (Limapontii",
"Moll (Limnaeid.)",
"Moll (Lithodomid",
"Moll (Lucinid.).",
"Moll (Mactrid.)",
"Moll (Mactrid.).",
"Moll (Melaniid.)",
"Moll (Modiolopsi",
"Moll (Muricid.).",
"Moll (Mytilid.).",
"Moll (Nassid.).",
"Moll (Naticid.).",
"Moll (Nautilid.)",
"Moll (Ostreid.).",
"Moll (Ovulid.).",
"Moll (Paludestri",
"Moll (Peraclid.)",
"Moll (Physid.).",
"Moll (Planorbid.",
"Moll (Platyclyme",
"Moll (Pleurodont",
"Moll (Pomatiasid",
"Moll (Problemati",
"Moll (Pterop.).",
"Moll (Pteropoda)",
"Moll (Pupillid.)",
"Moll (Scrobicula",
"Moll (Sphaeriid.",
"Moll (Streptaxid",
"Moll (Subulinid.",
"Moll (Succineid.",
"Moll (Tellinid.)",
"Moll (Thiarid.).",
"Moll (Trigoniid.",
"Moll (Turrid.).",
"Moll (Turritelli",
"Moll (Unionid.).",
"Moll (Valvatid.)",
"Moll (Venerid.)",
"Moll (Venerid.).",
"Moll (Viviparid.",
"Moll (Volutid.)",
"Moll (Zonitid.).",
"Moll(Acavacea)",
"Moll(Achatinacea",
"Moll(Agmata)",
"Moll(Ambonychiac",
"Moll(Arcacea)",
"Moll(Astartacea)",
"Moll(Blvalv)",
"Moll(Buliminacea",
"Moll(Camaenacea)",
"Moll(Cambroscler",
"Moll(Camenida)",
"Moll(Cardiacea)",
"Moll(Ceph,)",
"Moll(Cerithiacea",
"Moll(Cerithiopsa",
"Moll(Clausiliace",
"Moll(Cocculinace",
"Moll(Conacea)",
"Moll(Corbiculace",
"Moll(Cyclophorac",
"Moll(Dreissenace",
"Moll(Ellobiacea)",
"Moll(Epitoniacea",
"Moll(Eulimacea)",
"Moll(Euomphalace",
"Moll(Galeommatac",
"Moll(Gasteopoda)",
"Moll(Halobiacea)",
"Moll(Helcionella",
"Moll(Helicacea)",
"Moll(Helixariona",
"Moll(Hippuritace",
"Moll(Hyolith)",
"Moll(Hyolitha)",
"Moll(Hyolithida)",
"Moll(Hyolithidae",
"Moll(Lepetellace",
"Moll(Loxonematac",
"Moll(Lymnaeacea)",
"Moll(Macluritace",
"Moll(Megalodonta",
"Moll(Microdomata",
"Moll(Modiomorpha",
"Moll(Mollusca",
"Moll(Mollusca cl",
"Moll(Muricacea)",
"Moll(Mytilacea)",
"Moll(Neomphalace",
"Moll(Neritacea)",
"Moll(Nuculanacea",
"Moll(Oriostomata",
"Moll(Patellacea)",
"Moll(Pectinacea)",
"Moll(Pholadacea)",
"Moll(Pholadomyac",
"Moll(Planorbacea",
"Moll(Poromyacea)",
"Moll(Praecardiac",
"Moll(Problematic",
"Moll(Punctacea)",
"Moll(Pupillacea)",
"Moll(Rissoacea)",
"Moll(Streptaxace",
"Moll(Tellinacea)",
"Moll(Trigoniacea",
"Moll(Triphoracea",
"Moll(Trochacea)",
"Moll(Unionacea)",
"Moll(Valvatacea)",
"Moll(Veneracea)",
"Moll(Viviparacea",
"Moll. (Lam.).",
);


$nz_groups=array(
'Lep (Satyrid.).',
);

$ion = array();

$not_matched = array();

$update = array();

foreach ($nz_groups as $g)
{
	$matched = false;
	
	$temp_key = $g;
	
	//$temp_key = str_replace("Amph", "Amphibia", $temp_key);
	

	if (preg_match('/(?<one>[A-Z]\w+)[\.|,]?\s*\((?<two>[^\)]+)\)?\.?/', $temp_key, $m))
	{
		$matched = true;
		print_r($m);
		
		$pattern = "%-" . $m['one'] . '%-' . str_replace(".", "", $m['two']) . '%';
		
		echo $pattern . "\n";
		
		$sql = 'SELECT * FROM `names_groups` WHERE `key` LIKE "' . $pattern . '" AND name LIKE "' . str_replace(".", "", $m['two']) . '%"';
		
		echo $sql . "\n";
		
		//$sql .= ' LIMIT 1';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		if ($result->NumRows() == 1)
		{			
			$ion_group = $result->fields['name'];
			
			echo "$g $ion_group\n";
			
			$ion[] = '"' . $ion_group . '"';
			
			$update[] = 'UPDATE nz SET rdmp_category="' . $ion_group . '" WHERE category="' . $g . '";';
			
		
		}

	}
	
	if (!$matched)
	{
		$not_matched[] = $g;
	}
}



print_r($ion);

print_r($not_matched);

print_r($update);

echo join("\n", $update) . "\n";

echo join(",\n", $ion) . "\n";


/*
$page = 10;
$offset = 0;

$result = $db->Execute('SET max_heap_table_size = 1024 * 1024 * 1024');
$result = $db->Execute('SET tmp_table_size = 1024 * 1024 * 1024');

$count = 0;

$done = false;

while (!$done)
{
	$sql = 'SELECT * FROM `nz` WHERE id > 251649 LIMIT ' . $page . ' OFFSET ' . $offset;


	$sql = 'SELECT * FROM `nz` WHERE ion IS NULL AND category="Orth" LIMIT ' . $page . ' OFFSET ' . $offset;
	
	// Birds are a mess
	//$sql = 'SELECT * FROM `nz` WHERE ion IS NULL AND category="Aves" LIMIT ' . $page . ' OFFSET ' . $offset;

	$sql = 'SELECT * FROM `nz` WHERE ion IS NULL AND category="Arachn" LIMIT ' . $page . ' OFFSET ' . $offset;
	
	
	
	//$sql = 'SELECT * FROM nz WHERE genus="Taenionema"';
	//$sql = 'SELECT * FROM nz WHERE author="Distant 1910"  LIMIT ' . $page . ' OFFSET ' . $offset;
	//$sql = 'SELECT * FROM nz WHERE publication LIKE "Proc. ent. Soc. Washington%"  LIMIT ' . $page . ' OFFSET ' . $offset;

	$sql = 'SELECT * FROM nz WHERE author LIKE "Sørensen%"  LIMIT ' . $page . ' OFFSET ' . $offset;
	$sql = 'SELECT * FROM nz WHERE author LIKE "Soerensen%"  LIMIT ' . $page . ' OFFSET ' . $offset;

	$sql = 'SELECT * FROM nz WHERE author ="Riley 1884" AND ion IS NULL  LIMIT ' . $page . ' OFFSET ' . $offset;

//	$sql = 'SELECT * FROM nz WHERE Genus="Mesanthura" AND ion IS NULL  LIMIT ' . $page . ' OFFSET ' . $offset;
	
	$sql = 'SELECT * FROM nz WHERE Genus="Kerkophorus" AND ion IS NULL  LIMIT ' . $page . ' OFFSET ' . $offset;
	
	

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF && ($result->NumRows() > 0)) 
	{			
		$id = $result->fields['id'];
		$genus = $result->fields['genus'];
		$author = $result->fields['author'];
		$comments = $result->fields['comments'];
		$group = $result->fields['category'];
		
		echo "-- $id $genus $author $comments\n";
		
		if (0)
		{
			$hit = match_ion($genus, $author);
		}
		else
		{
			$hit = match_ion_group($genus, $group);
		}
		if ($hit)
		{
			if ($debug)
			{
				// print_r($hit);
			}
			$sql = '';
			$sql .= 'UPDATE nz SET ion=' . $hit->ion . ' WHERE id=' . $id . ';' . "\n";
			$sql .= 'REPLACE INTO nz_id(id, namespace, identifier) VALUES(' . $id . ', "ion",' . $hit->ion . ');';
			echo $sql . "\n";
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
	}
	
	
}	

*/	

?>
