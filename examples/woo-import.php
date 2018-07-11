<?php
require_once( "../loader.php" );

$woo = new VTMWoo();

$price      = 15500 * mt_rand( 1, 20 );
$sale_price = $price - ( mt_rand( 5, 20 ) * $price ) / 100;

echo $woo->saveProduct( [
	'post_title'    => 'Product ' . time(),
	'post_content'  => 'This is just a product',
	'feature_image' => VTM_WPI_ASSETS_PATH . '/images/image.jpg',
	'gallery'       => [
		VTM_WPI_ASSETS_PATH . '/images/image-2.jpg',
		VTM_WPI_ASSETS_PATH . '/images/image-3.jpg'
	],
	'regular_price'         => $price,
	'sale_price'    => $sale_price,
	'terms'         => [
		'product_cat' => [ "Tooth brush" ],
		'brand' => [ "Sensodyne" ],
	],
	'variations'    => [
		[
			'attributes' => [
				'mau-sac' => 'Yellow',
				'size' => 'XL',
			],
			'regular_price' => $price + 5000
		],
		[
			'attributes' => [
				'mau-sac' => 'Blue'
			],
			'regular_price' => $price + 5000,
			'sale_price' => $sale_price + 1000
		],
		[
			'attributes' => [
				'mau-sac' => 'Red'
			],
			'regular_price' => $price
		]
	]
] );