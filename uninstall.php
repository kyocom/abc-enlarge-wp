<?php
/**
 * Uninstall routine: remove the per-post option meta.
 *
 * @package ABC_Enlarge
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( '_abc_enlarge_disabled' );
delete_post_meta_by_key( '_abc_enlarge_galleries_disabled' );
