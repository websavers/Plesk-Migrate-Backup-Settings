#!/opt/plesk/php/8.2/bin/php
<?php

define('PWD', realpath(dirname(__FILE__)) );

if (! file_exists('old_client_id_map.xml')) {
	echo 'Cannot read file old_client_id_map.xml';
	exit;
}

if (! file_exists('new_client_id_map.xml')) {
	echo 'Cannot read file new_client_id_map.xml';
	exit;
}

if (! file_exists('old_domain_id_map.xml')) {
	echo 'Cannot read file old_domain_id_map.xml';
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

/** 
 * Domain mapping
 */
$old_domain_ids = simplexml_load_file('old_domain_id_map.xml');
$old_domain_map = array(); // id => domain
foreach ($old_domain_ids->row as $d){
	$old_domain_map[(int)$d->field[0]] = (string)$d->field[1];
}

$new_domain_ids = simplexml_load_file('new_domain_id_map.xml');
$new_domain_map = array(); // domain => id
foreach ($new_domain_ids->row as $d){
	$new_domain_map[(string)$d->field[1]] = (int)$d->field[0];
}

/**
 * Client mapping
 */

$old_client_ids = simplexml_load_file('old_client_id_map.xml');
$old_client_map = array(); // id => login
foreach ($old_client_ids->row as $c){
	$old_client_map[(int)$c->field[0]] = (string)$c->field[1];
}

$new_client_ids = simplexml_load_file('new_client_id_map.xml');
$new_client_map = array(); // domain => id
foreach ($new_client_ids->row as $c){
	$new_client_map[(string)$c->field[1]] = (int)$c->field[0];
}

/**
 * Process Table BackupsSettings
 */
$table_BackupsSettings = simplexml_load_file('table_BackupsSettings.xml');
$elementsToRemove = array();
foreach ($table_BackupsSettings->row as $k => $s){
	$id = (int)$s->field[0];
	$type = (string)$s->field[1];
	//$param = (string)$s->field[2];
	//$value = (string)$s->field[3];

	switch($type){

		case 'domain':

			//This domain doesn't exist anymore, but still has old settings in DB
			if ( ! isset($old_domain_map[$id]) ){
				$elementsToRemove[] = $s;
				continue 2;
			}
			$domain = $old_domain_map[$id];
			$new_id = $new_domain_map[$domain];

			// Save to XML object
			$s->field[0] = $new_id;

			break;

		case 'client':

			//This client doesn't exist anymore, but still has old settings in DB
			if ( ! isset($old_client_map[$id]) ){
				$elementsToRemove[] = $s;
				continue 2;
			}
			$login = $old_client_map[$id];
			$new_id = $new_client_map[$login];

			// Save to XML object
			$s->field[0] = $new_id;

			break;

	}



}
foreach ($elementsToRemove as $e) { //remove old/unused settings
	unset($e[0]);
}
//Output to new file
$table_BackupsSettings->asXml('table_BackupsSettings_fixed.xml');
echo "File created: table_BackupsSettings_fixed.xml\n";

/**
 * Process Table BackupsScheduled
 */
$table_BackupsScheduled = simplexml_load_file('table_BackupsScheduled.xml');
$elementsToRemove = array();
foreach ($table_BackupsScheduled->row as $k => $s){
	$id = (int)$s->field[1]; //obj_id = domain or client ID
	$type = (string)$s->field[2]; //obj_type = domain|client



	switch($type){

		case 'domain':

			//This domain doesn't exist anymore, but still has old settings in DB
			if ( ! isset($old_domain_map[$id]) ){
				$elementsToRemove[] = $s; //We don't need it in the output then
				continue;
			}
			$domain = $old_domain_map[$id];
			$new_id = $new_domain_map[$domain];

			// Save to XML object
			$s->field[1] = $new_id;

			break;

		case 'client':

			//This client doesn't exist anymore, but still has old settings in DB
			if ( ! isset($old_client_map[$id]) ){
				$elementsToRemove[] = $s;
				continue;
			}
			$login = $old_client_map[$id];
			$new_id = $new_client_map[$login];

			// Save to XML object
			$s->field[1] = $new_id;

			break;

	}

}
foreach ($elementsToRemove as $e) { //remove old/unused settings
		unset($e[0]);
}
$table_BackupsScheduled->asXml('table_BackupsScheduled_fixed.xml');
echo "File created: table_BackupsScheduled_fixed.xml\n";