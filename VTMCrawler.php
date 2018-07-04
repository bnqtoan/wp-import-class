<?php

class VTMCrawler {
	static function isRemoteFile( $url ) {
		return strpos( $url, 'http' ) > - 1;
	}

	static function isBase64Image( $text ) {
		return strpos( $text, 'data:image/image/jpeg;base64' ) > -1;
	}

	static function getFileSignature( $fileContent ) {
		return md5( $fileContent );
	}

	static function writeBase64Image($content){
		$image_parts = explode(";base64,", $content);
		$image_type_aux = explode("image/", $image_parts[0]);
		$image_type = $image_type_aux[2];
		$image_base64 = base64_decode($image_parts[1]);
		$path = ABSPATH . '/tmp/' . md5( $content ) . '.'.$image_type;
		file_put_contents($path, $image_base64);
		return $path;
	}

	static function setAttachmentSignature( $attachmentId ) {
		$url = wp_get_attachment_image_src( $attachmentId, 'full' );
		if ( $url ) {
			$url  = $url[0];
			$file = str_replace( APP_SITE_URL, ABSPATH, $url );
			$file = str_replace( '//', '/', $file );
			if ( is_file( $file ) ) {
				return update_post_meta( $attachmentId, '_signature', self::getFileSignature( file_get_contents( $file ) ) );
			}
		}

		return false;
	}

	static function isFileExist( $fileContent ) {
		global $wpdb;
		$sign         = self::getFileSignature( $fileContent );
		$sql          = "SELECT post_id FROM `{$wpdb->postmeta}` WHERE `meta_key` = '_signature' and `meta_value`='$sign'";
		$sql          = $wpdb->prepare( $sql, [] );
		$attachmentId = $wpdb->get_var( $sql );
		if ( $attachmentId ) {
			return [
				'id'  => $attachmentId,
				'url' => wp_get_attachment_image_src( $attachmentId, 'full' )[0]
			];
		}

		return false;
	}

	static function downloadFile( $url ) {
		$fileContent = false;
		$type        = false;

		if ( self::isBase64Image( $url ) ) {
			$url = self::writeBase64Image($url);
		}
		if ( self::isRemoteFile( $url ) ) {
			$response    = wp_remote_get( $url, [
				'timeout' => 120
			] );
			$fileContent = wp_remote_retrieve_body( $response );
			$type        = wp_remote_retrieve_header( $response, 'content-type' );
		} else {
			if ( is_file( $url ) ) {
				$fileContent = file_get_contents( $url );
				$type        = wp_check_filetype( basename( $url ) )['type'];
			}
		}
		if ( $fileContent && $type ) {
			return [
				'content' => $fileContent,
				'type'    => $type,
				'name'    => basename( $url )
			];
		}

		return false;
	}

	static function uploadFile( $file ) {
		$fileExists = self::isFileExist( $file['content'] );
		if ( $fileExists ) {
			return array(
				'attachment_id' => $fileExists['id'],
				'url'           => $fileExists['url']
			);
		} else {
			$type   = $file['type'];
			$mirror = wp_upload_bits( $file['name'], '', $file['content'] );
			if ( $mirror && ! $mirror['error'] && $type ) {
				$attachment    = array(
					'post_title'     => $file['name'],
					'post_mime_type' => $type,
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $mirror['file'] );
				if ( ! is_wp_error( $attachment_id ) ) {
					require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $mirror['file'] );
					wp_update_attachment_metadata( $attachment_id, $attachment_data );
				}

				return array(
					'attachment_id' => $attachment_id,
					'url'           => $mirror['url']
				);
			}
		}

		return false;
	}

	static function downloadImageToWP( $url ) {
		$file = self::downloadFile( $url );
		return self::uploadFile( $file );
	}
}