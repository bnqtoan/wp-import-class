<?php
ini_set( 'memory_limit', '1024M' );
require_once( dirname( __DIR__ ) . "/loader.php" );
use Classes\GoogleSheetReader;
$sheets   = new GoogleSheetReader();
//$sheetKey = "1e_lzAbjrf8B-vOtS8QM54isu_Q2gS9LFLL8OOQ5OM_4";
$sheetKey = "1CQxkUek3DELvDDq9DlAIYD_iaknTTKqiW2i8EiFX9vU";
//$data     = $sheets->read( $sheetKey, "A1:AP21" );
$data     = $sheets->read( $sheetKey, "A1:AP2" );
function _vtmi_simple_map( $data ) {
	$keys  = array_keys( $data[0] );
	$items = [];

	foreach ( $data as $product ) {
		$price = $product['regular_price'] ? $product['regular_price'] : false;
		$sale_price = $product['sale_price'] ? $product['sale_price'] : false;
		$item = [
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
//			'variations'    => [
//				[
//					'attributes' => [
//						'mau_sac' => 'Yellow',
//						'size' => 'XL',
//					],
//					'regular_price' => $price
//				],
//				[
//					'attributes' => [
//						'mau_sac' => 'Blue'
//					],
//					'regular_price' => $price,
//					'sale_price' => $sale_price
//				],
//				[
//					'attributes' => [
//						'mau_sac' => 'Red'
//					],
//					'regular_price' => $price
//				]
//			]
		];

		foreach ( $keys as $key ) {
			switch ( $key ) {
				case 'gallery':
					$item['gallery'] = explode('|',$product[ $key ]);
					break;
				case 'product_cat':
					$cat = $product[ $key ];
					$cat = explode('|', $cat);
					if (count($cat) ==  3){
						unset($cat[2]);
					}
					$item['terms']['product_cat'][] = $cat;
					break;
				case 'brand':
					$item['terms']['brand'][] = $product[ $key ];
					break;
				case 'url':
					$item['post_name'] = $product[ $key ];
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
				default:
					$item[ $key ] = $product[ $key ];
					break;
			}
		}
		array_push( $items, $item );
	}
	return $items;
}
$woo = new VTMWoo();
$data = _vtmi_simple_map( $data );
foreach ($data as $item){
	$woo->saveProduct($item);
}
//file_put_contents(VTM_WPI_ASSETS_PATH.'/data.json', json_encode($data));