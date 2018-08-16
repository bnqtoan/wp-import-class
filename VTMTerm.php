<?php

class VTMTerm {
	static function saveTermMetas( $term ) {

	}

	/**
	 * @param $term WP_Term
	 * @param $metas array
	 */
	static function saveMetas( $term, $metas ) {
		if ( ! is_array( $metas ) ) {
			return;
		}
		foreach ( $metas as $key => $meta ) {
			$meta = array_merge( [
				'type' => 'default'
			], $meta );

			$value = $meta['value'];
			if ( VTMHelper::isUrl( $value ) ) {
				$attachment = VTMCrawler::downloadImageToWP( $value );
				if ( $attachment ) {
					$value = $attachment['attachment_id'];
				}
			}
			switch ( $meta['type'] ) {
				case 'acf':
					if ( function_exists( 'update_field' ) ) {
						update_field( $key, $value, $term );
					}
					break;
				default:
					update_term_meta( $term->term_id, $key, $value );
					break;
			}
		}
	}

	static function createTermsL1( $terms, $taxonomy, $parent = 0 ) {

		$report = [];

		$tax = get_taxonomy( $taxonomy );
		if ( $tax->hierarchical ) {
			foreach ( $terms as $term ) {
				$term_id    = false;
				$term       = array_merge( [
					'name'        => false,
					'description' => ''
				], $term );
				$termName   = $term['name'];
				$reportItem = [
					'term_name' => $termName,
					'exists' => 0,
					'can_create' => 0
				];
				$termExists = term_exists( $termName, $taxonomy, $parent );
				if ( ! $termExists ) {
					$_term = wp_insert_term( $termName, $taxonomy, [
						'parent'      => $parent,
						'description' => $term['description']
					] );
					if ( ! is_wp_error( $_term ) ) {
						$term_id = $_term['term_id'];
						$reportItem['can_create'] = 1;
					}
				} else {
					$term_id = $termExists['term_id'];
					$reportItem['exists'] = 1;
					if ( $term['description'] ) {
						wp_update_term( $parent, $taxonomy, [
							'description' => $term['description']
						] );
					}
				}

				if ( $term_id ) {
					$tax = get_term( $term_id, $taxonomy );
					self::saveMetas( $tax, $term['metas'] );
				} else {

				}

				array_push($report, $reportItem);
			}
			VTMHelper::toCSV(VTM_WPI_ASSETS_PATH.'/report-'.time().'.csv', $report);
			return $parent;
		}

		return false;
	}

	/**
	 * No inheritance support for now
	 *
	 * @param array $terms
	 * @param $taxonomy string
	 * @param int $parent
	 *
	 * @return array|bool|int|WP_Error
	 */
	static function createTerms( $terms, $taxonomy, $parent = 0 ) {
		$sample = [
			'name'  => '',
			'metas' => [
				'icon' => [
					'type'  => '',
					'value' => 'acf'
				]
			]
		];
		$tax    = get_taxonomy( $taxonomy );
		if ( $tax->hierarchical ) {
			foreach ( $terms as $term ) {
				$termExists = term_exists( $term, $taxonomy, $parent );

				if ( ! $termExists ) {
					$parent = wp_insert_term( $term, $taxonomy, [
						'parent' => $parent
					] );
					if ( ! is_wp_error( $parent ) ) {
						$parent = $parent['term_id'];
					}
				} else {
					$parent = $termExists['term_id'];
				}

				$tax = get_term( $parent, $taxonomy );
				self::saveMetas( $tax, $term['metas'] );
			}

			return $parent;
		}

		return false;
	}

	/**
	 * @param $terms array of terms name
	 * @param $taxonomy
	 * @param null $postId
	 */
	static function saveTerms( $terms, $taxonomy, $postId = null ) {
		$tax = get_taxonomy( $taxonomy );
		if ( $tax->hierarchical ) {
			$ids = [];
			foreach ( $terms as $term ) {
				$vertical_term = self::createTerms( $term, $taxonomy );
				array_push( $ids, $vertical_term );
			}
			$terms = $ids;
		}
		if ( $postId ) {
			wp_set_post_terms( $postId, $terms, $taxonomy );
		}
	}
}