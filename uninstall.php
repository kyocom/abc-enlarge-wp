<?php
/**
 * Uninstall routine: remove the per-post option meta and plugin settings.
 *
 * @package Inlarge
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( '_inlarge_disabled' );
delete_post_meta_by_key( '_inlarge_galleries_disabled' );
delete_option( 'inlarge_options' );
