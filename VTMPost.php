<?php

class VTMPost {

	function __construct() {
		$this->ID = false;
	}


	function getId() {
		return $this->ID;
	}

	function setId($id){
		$this->ID = $id;
		return $this->getId();
	}

	static function clearAll($filter= []){
		$filter = array_merge([
			'post_type' => 'post',
			'posts_per_page' => -1
		], $filter);
		$q = new WP_Query($filter);

		while ($q->have_posts()){ $q->the_post();
			wp_delete_post(get_the_ID(), true);
			echo "Deleted ".get_the_ID()."\n";
		}
	}

	function savePost( $data, $default = [] ) {
		$data         = array_merge( $default, $data );
		$metas        = $data['metas'];
		$featureImage = $data['feature_image'];
		$terms        = $data['terms'];

		if ( $data['ID'] ) {
			$id = wp_update_post( $data );
		} else {
			$id = wp_insert_post( $data );
		}
		if ( ! is_wp_error( $id ) ) {
			$this->ID = $id;
			$this->saveMeta( $id, $metas );
		}
		if ( $featureImage ) {
			$this->setPostThumbnailCool( $featureImage );
		}

		if ( is_array( $terms ) ) {
			foreach ( $terms as $key => $termNames ) {
				$this->saveTerms( $termNames, $key );
			}
		}

		return $this;
	}

	function saveMeta( $postId, $metas ) {
		if (!is_array($metas)) return;
		foreach ( $metas as $key => $meta ) {
			$meta = array_merge( [
				'type' => 'default'
			], $meta );
			switch ( $meta['type'] ) {
				case 'acf':
					if ( function_exists( 'update_field' ) ) {
						update_field( $key, $meta['value'], $postId );
					}
					break;
				default:
					update_post_meta( $postId, $key, $meta['value'] );
					break;
			}
		}
	}

	function saveTerms( $terms, $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );
		if ( $tax->hierarchical ) {
			$ids = [];
			foreach ( $terms as $term ) {
				$termExists = term_exists( $term, $taxonomy );
				if ( $termExists !== 0 && $termExists !== null ) {
					array_push( $ids, $termExists['term_id'] );
				} else {
					$insertTerm = wp_insert_term( $term, $taxonomy );
					if ( ! is_wp_error( $insertTerm ) ) {
						array_push( $ids, $insertTerm['term_id'] );
					}
				}
			}
			$terms = $ids;
		}
		wp_set_post_terms( $this->ID, $terms, $taxonomy );
	}

	static function setPostThumbnail( $postId, $attachmentId ) {
		return set_post_thumbnail( $postId, $attachmentId );
	}

	function setPostThumbnailCool( $url ) {
		$attachment = VTMCrawler::downloadImageToWP( $url );
		if ( $attachment ) {
			return $this->setPostThumbnail( $this->ID, $attachment['attachment_id'] );
		}

		return false;
	}

	static function generateAttachmentsSignature() {
		global $wpdb;
		$sql   = "SELECT ID FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment'";
		$posts = $wpdb->get_col( $sql );
		foreach ( $posts as $id ) {
			VTMCrawler::setAttachmentSignature( $id );
		}
	}
}