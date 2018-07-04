<?php
require_once( "../loader.php" );

$post = new VTMPost();

$post->savePost( [
	'post_title'    => 'This is post title',
	'post_type'     => 'post',
	'post_status'   => 'publish',
	'feature_image' => "https://letweb.net/wp-content/uploads/2018/05/Layout336x280.png", // accept remote url & absolute url
	'terms'         => [
		'category' => [ "My Category" ],
		'post_tag' => [ "tag1, tag2" ]
	],
	'metas'         => [
		'my_meta_key' => [
			'value' => "My meta value"
		],
		'my_acf_field' => [
			'type' => 'acf',
			'value' => "My acf field value"
		]
	]
] );

echo $post->getId();