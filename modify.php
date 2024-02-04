#!/opt/plesk/php/8.2/bin/php
<?php

if (! file_exists('domain_id_map.xml')) {
  echo 'Cannot read file domain_id_map.xml';
  exit;
}

if (! file_exists('new_domain_id_map.xml')) {
  echo 'Cannot read file new_domain_id_map.xml';
  exit;
}

if (! file_exists('table_BackupsSettings.xml')) {
  echo 'Cannot read file table_BackupsSettings.xml';
  exit;
}

if (! file_exists('table_BackupsScheduled.xml')) {
  echo 'Cannot read file table_BackupsScheduled.xml';
  exit;
}

$old_domain_ids = simplexml_load_file('domain_id_map.xml');
$old_domain_map = array(); // id => domain
foreach ($old_domain_ids->row as $d){
	$old_domain_map[(int)$d->field[0]] = (string)$d->field[1];
}

$new_domain_ids = simplexml_load_file('new_domain_id_map.xml');
$new_domain_map = array(); // domain => id
foreach ($new_domain_ids->row as $d){
	$new_domain_map[(string)$d->field[1]] = (int)$d->field[0];
}

$table_BackupsSettings = simplexml_load_file('table_BackupsSettings.xml');
$elementsToRemove = array();
foreach ($table_BackupsSettings->row as $k => $s){
	$did = (int)$s->field[0];
  	//$type = (string)$s->field[1];
	//$param = (string)$s->field[2];
	//$value = (string)$s->field[3];

	//This domain doesn't exist anymore, but still has old settings in DB
	if ( ! isset($old_domain_map[$did]) ){
		$elementsToRemove[] = $s; //We don't need it in the output then
		continue;
	}
	$domain = $old_domain_map[$did];
	$new_did = $new_domain_map[$domain];
  
	//echo "$domain : $did => $new_did\n"; ///DEBUG
	// Save to XML object
	$s->field[0] = $new_did;

}
foreach ($elementsToRemove as $e) {
	    unset($e[0]);
}
//Output to new file
$table_BackupsSettings->asXml('table_BackupsSettings_fixed.xml');
echo "File created: table_BackupsSettings_fixed.xml\n";

$table_BackupsScheduled = simplexml_load_file('table_BackupsScheduled.xml');
$elementsToRemove = array();
foreach ($table_BackupsScheduled->row as $k => $s){
	$did = (int)$s->field[1]; //obj_id = domain ID
  	$type = (string)$s->field[2]; //obj_type = domain|client

	if ($type != 'domain'){
		continue;
	}
	//This domain doesn't exist anymore, but still has old settings in DB
	if ( ! isset($old_domain_map[$did]) ){
		$elementsToRemove[] = $s; //We don't need it in the output then
		continue;
	}
	$domain = $old_domain_map[$did];
	$new_did = $new_domain_map[$domain];
  
	$s->field[1] = $new_did;

}
foreach ($elementsToRemove as $e) {
	    unset($e[0]);
}
$table_BackupsScheduled->asXml('table_BackupsScheduled_fixed.xml');
echo "File created: table_BackupsScheduled_fixed.xml\n";
