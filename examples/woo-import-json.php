<?php
require_once( dirname( __DIR__ ) . "/loader.php" );
$woo = new VTMWoo();
foreach (glob( VTM_WPI_ASSETS_PATH . '/json-data/*.json') as $file){
	$woo->saveProductJson($file);
}