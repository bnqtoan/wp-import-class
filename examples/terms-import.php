<?php
require_once( dirname( __DIR__ ) . "/loader.php" );

function reset_terms(){
	$terms = get_terms([
		'taxonomy' => 'category',
		'hide_empty' => false
	]);
	foreach ($terms as $term){
		wp_delete_term($term->term_id, 'category');
	}
}

reset_terms();

foreach (glob(VTM_WPI_ASSETS_PATH.'/brand-data/*.json') as $file){
	$data = file_get_contents( $file);
	if ( $data ) {
		$data = json_decode( $data, true );
		foreach ( $data as &$term ) {
			$term = [
				'name' => $term['title'],
				'metas' => [
					'image' => [
						'value' => "http://3kshop.vn/".$term['img'],
						'type' => 'acf'
					]
				]
			];
		}
		VTMTerm::createTermsL1( $data, 'category' );
	}
}