<?php
ini_set('memory_limit','1024M');
require_once( dirname( __DIR__ ) . "/loader.php" );
//$woo = new VTMWoo();
//foreach (glob( VTM_WPI_ASSETS_PATH . '/json-data/*.json') as $file){
//	$woo->saveProductJson($file);
//}

\Classes\Reader::readExcel(VTM_WPI_ASSETS_PATH.'/products.xls');