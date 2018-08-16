<?php
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/wp/wp-load.php');
define('VTM_WPI_ROOT_PATH', __DIR__);
define('VTM_WPI_ASSETS_PATH', VTM_WPI_ROOT_PATH.'/dev-assets');
define('APP_SITE_URL', get_bloginfo('url'));

require_once(__DIR__.'/classes/Reader.php');
require_once(__DIR__.'/classes/GoogleSheetReader.php');

require_once ('VTMHelper.php');
require_once ('VTMReport.php');
require_once ('VTMCrawler.php');
require_once ('VTMTerm.php');
require_once ('VTMPost.php');
require_once ('VTMWoo.php');


function _vtm_save_attachment_signature( $metadata, $attachment_id ) {
	VTMCrawler::setAttachmentSignature($attachment_id);
	return $metadata;
}

add_filter( 'wp_generate_attachment_metadata', '_vtm_save_attachment_signature', 10, 2 );