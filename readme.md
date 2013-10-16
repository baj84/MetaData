# MetaData PHP Class

A small library for scraping all the meta data from a given URL, including open graph tags.

## Usage
	require_once('metadata.class.php');

	$metaData = MetaData::fetch('http://www.ted.com/talks/ken_robinson_says_schools_kill_creativity.html');
	
	// returns an associative array
	var_dump($metaData->tags());

	foreach ($metaData as $key => $value) {
		echo "$key => $value";
	}