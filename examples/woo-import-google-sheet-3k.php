<?php
ini_set( 'memory_limit', '1024M' );
require_once( dirname( __DIR__ ) . "/loader.php" );
use Classes\GoogleSheetReader;

$sheets = new GoogleSheetReader();
//$sheetKey = "1e_lzAbjrf8B-vOtS8QM54isu_Q2gS9LFLL8OOQ5OM_4";
//$sheetKey = "1CQxkUek3DELvDDq9DlAIYD_iaknTTKqiW2i8EiFX9vU";
$sheetKey = "1b1gkbp1r4qQfI608SykUTNzEPiWClDcpwbO0SkseIrg";
//$data     = $sheets->read( $sheetKey, "A1:AP21" );

/**
 * TODO
 * - load all data
 * - create breakpoint
 * - create cron job
 * - log & monitoring
 */


function _vtm_log( $data ) {
	$data = $data . "\n";
	file_put_contents( VTM_WPI_ASSETS_PATH . '/brand-code.txt', $data, FILE_APPEND );
}

function _vtmi_get_brand_list() {
	$file = VTM_WPI_ASSETS_PATH . '/brand-code.txt';
	$file = file_get_contents( $file );
	$rows = explode( "\n", $file );
	$data = [];
	foreach ( $rows as $row ) {
		$cells             = explode( "\t", $row );
		$data[ $cells[0] ] = $cells[1];
	}

	return $data;
}

function _vtmi_get_flag(){
	$file = VTM_WPI_ASSETS_PATH.'/flag.txt';
	if (!is_file($file)) return 0;
	return intval(file_get_contents($file));
}

function _vtmi_set_flag($index){
	$file = VTM_WPI_ASSETS_PATH.'/flag.txt';
	file_put_contents($file, $index);
}

$data = $sheets->read( $sheetKey, "A1:AP1523" );

function _vtmi_make_sku( $brand, $index ) {
	$brand_list = _vtmi_get_brand_list();
	if ( isset( $brand_list[ $brand ] ) ) {
		$format = "{$brand_list[$brand]}/%'.06d";

		return sprintf( $format, $index );
	}
	_vtm_log( $brand . ' not exists' );

	return false;
}

;
function _vtmi_simple_map( $data ) {

	$meta_string = "metas_thong_so_ky_thuat\tmetas_tong_quan\tmetas_download	metas_reviews	metas_hinh_anh_san_pham	metas_hinh_anh	metas_thong_so_ki_thuat	metas_y_tuong_thiet_ke	metas_video_huong_dan	metas_phong_cach_thiet_ke	metas_cowon_s_jeteffect_5	metas_concept	metas_review	metas_thong_so	metas_thu_vien_anh	metas_windows_driver	metas_jbl_premium_finish_program	metas_jbl_synthesis_project_array	metas_tong_quan_series_loa_jbl_array	metas_tinh_nang	metas_phu_kien	metas_kich_thuoc	metas_thuoc_tinh_ky_thuat";
	$meta_string = explode( "\t", $meta_string );

	$include_in_content = array_map( function ( $item ) {
		//$item = explode("metas_", $item);
		return $item;
	}, $meta_string );

	$ignore = [
		'url' => '/1more-capsule'
	];


	$keys  = array_keys( $data[0] );
	$items = [];

	foreach ( $data as $product ) {
		if ( in_array( $product['url'], $ignore ) || ( isset( $product['Exclude'] ) && $product['Exclude'] == 'x' ) ) {
			echo "ignore ".$product['post_title']."\n";
			continue;
		}
		$price      = $product['regular_price'] ? $product['regular_price'] : false;
		$sale_price = $product['sale_price'] ? $product['sale_price'] : false;
		$item       = [
			'post_title'    => '',
			'post_content'  => '',
			'feature_image' => '',
			'gallery'       => [],
			'regular_price' => false,
			'sale_price'    => false,
			'terms'         => [
				'product_cat' => [],
				'brand'       => []
			],
			'metas'         => [
				'old_id'            => [
					'value' => 0
				],
				'old_url'           => [
					'value' => ''
				],
				'old_display'       => [
					'value' => ''
				],
				'thuong_hieu'       => [
					'value' => ''
				],
				'thuoc_tinh_moi'    => [
					'value' => '',
					'type'  => 'acf'
				],
				'tong_quan'         => [
					'value' => '',
					'type'  => 'acf'
				],
				'thong_so_ky_thuat' => [
					'value' => '',
					'type'  => 'acf'
				],
				'title'             => [
					'value' => '',
					'type'  => 'seo'
				],
				'description'       => [
					'value' => '',
					'type'  => 'seo'
				],
				'keywords'          => [
					'value' => '',
					'type'  => 'seo'
				],
				'advanced_meta'     => [
					'value' => '',
					'type'  => 'seo'
				]
			],
		];

		$content = [];
		foreach ( $keys as $key ) {
			if ( in_array( $key, $include_in_content ) || $key == 'post_content' ) {
				if ( $key == 'post_content' ) {
					$item[ $key ] = $product[ $key ];
				} else {
					//echo $product[$key];
					array_push( $content, $product[ $key ] );
				}
			} else {
				switch ( $key ) {
					case 'gallery':
						$item['gallery'] = explode( '|', $product[ $key ] );
						break;
					case 'product_cat':
						$cat = $product[ $key ];
						$cat = explode( '|', $cat );
						if ( count( $cat ) == 3 ) {
							unset( $cat[2] );
						}
						$item['terms']['product_cat'][] = $cat;
						break;
					case 'brand':
						$item['terms']['brand'][] = $product[ $key ];
						break;
					case 'url':
						$item['post_name']                 = $product[ $key ];
						$item['metas']['old_url']['value'] = $product[ $key ];
						break;
					case 'display':
						$item['metas']['old_display']['value'] = $product[ $key ];
						break;
					case 'metas_old_id':
						$item['metas']['old_id']['value'] = $product[ $key ];
						break;
					case 'metas_old_url':
						$item['metas']['old_url']['value'] = $product[ $key ];
						break;
					case 'metas_title':
						$item['metas']['title']['value'] = $product[ $key ];
						break;
					case 'metas_description':
						$item['metas']['description']['value'] = $product[ $key ];
						break;
					case 'metas_keywords':
						$item['metas']['keywords']['value'] = $product[ $key ];
						break;
					case 'metas_advanced_meta':
						$item['metas']['advanced_meta']['value'] = $product[ $key ];
						break;
					case 'post_content':

						break;
					default:
						$item[ $key ] = $product[ $key ];
						break;
				}
			}
		}
		$item['post_content'] .= implode( "\n", $content );
		array_push( $items, $item );
	}

	return $items;
}

$woo  = new VTMWoo();
$data = _vtmi_simple_map( $data );

$block = 2; // everytime I import x product
$count = 0;
$success = 0;
$fail = 0;

$flag = _vtmi_get_flag();

foreach ( $data as $i => $item ) {
	if ($i <= $flag) continue;
	if ($i > $flag + $block) {
		file_put_contents(VTM_WPI_ASSETS_PATH.'/import-log.txt', "\nImport $count,\tSuccess: $success,\tFail: $fail\n\n", FILE_APPEND);
		return;
	}
	$insert = $woo->saveProduct( $item );
	$count++;
	if ($insert){
		file_put_contents(VTM_WPI_ASSETS_PATH.'/import-log.txt', "Success to import {$item['post_title']}\n", FILE_APPEND);
		$success++;
	}
	else{
		file_put_contents(VTM_WPI_ASSETS_PATH.'/import-log.txt', "Fail to import {$item['post_title']}\n", FILE_APPEND);
		$fail++;
	}
	_vtmi_set_flag($i);
}
//file_put_contents(VTM_WPI_ASSETS_PATH.'/data.json', json_encode($data));