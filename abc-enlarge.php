<?php
/**
 * Plugin Name:       ABC Enlarge
 * Plugin URI:        https://github.com/kyocom/abc-enlarge-wp
 * Description:        Inline image zoom for WordPress powered by the abc-enlarge jQuery plugin. Automatically adds the "abc-enlarge" class to linked images in post content, and lets you disable enlargement per post (enabled by default).
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            kyocom (Kyo Ichida)
 * Author URI:        https://github.com/kyocom
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       abc-enlarge
 * Domain Path:       /languages
 *
 * @package ABC_Enlarge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'ABC_ENLARGE_VERSION', '1.0.0' );
define( 'ABC_ENLARGE_FILE', __FILE__ );
define( 'ABC_ENLARGE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABC_ENLARGE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Post meta key that, when truthy, disables enlargement for a single post.
 * Enlargement is ENABLED by default (absence of the flag == enabled).
 */
define( 'ABC_ENLARGE_META_KEY', '_abc_enlarge_disabled' );

/**
 * Whether abc-enlarge should run for the given post.
 *
 * @param int|WP_Post|null $post Post ID or object. Defaults to current post.
 * @return bool True when enlargement is enabled for the post.
 */
function abc_enlarge_is_enabled_for_post( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	$disabled = (bool) get_post_meta( $post->ID, ABC_ENLARGE_META_KEY, true );

	/**
	 * Filter whether abc-enlarge is enabled for a specific post.
	 *
	 * @param bool    $enabled Whether enlargement is enabled.
	 * @param WP_Post $post    The post object.
	 */
	return (bool) apply_filters( 'abc_enlarge_is_enabled_for_post', ! $disabled, $post );
}

/**
 * Register (but do not enqueue) the front-end script.
 */
function abc_enlarge_register_assets() {
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_register_script(
		'abc-enlarge',
		ABC_ENLARGE_URL . 'assets/js/jquery.abc-enlarge' . $suffix . '.js',
		array( 'jquery' ),
		ABC_ENLARGE_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'abc_enlarge_register_assets' );

/**
 * Enqueue the script on singular views where enlargement is enabled.
 */
function abc_enlarge_maybe_enqueue() {
	if ( ! is_singular() ) {
		return;
	}
	if ( ! abc_enlarge_is_enabled_for_post( get_queried_object_id() ) ) {
		return;
	}
	wp_enqueue_script( 'abc-enlarge' );
}
add_action( 'wp_enqueue_scripts', 'abc_enlarge_maybe_enqueue', 20 );

/**
 * Add the "abc-enlarge" class to linked images in post content.
 *
 * Only images wrapped in an <a> that points to an image file are targeted,
 * so the enlarge/high-res swap works and non-linked images are never broken.
 *
 * @param string $content The post content.
 * @return string Filtered content.
 */
function abc_enlarge_filter_content( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( ! is_singular() ) {
		return $content;
	}
	if ( ! abc_enlarge_is_enabled_for_post( get_the_ID() ) ) {
		return $content;
	}
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}

	// Match <a href="...image..."> ... <img ...> ... </a>
	$pattern = '#(<a\b[^>]*\bhref\s*=\s*(["\'])(?<href>[^"\']+?)\2[^>]*>\s*)(?<img><img\b[^>]*>)#i';

	$content = preg_replace_callback(
		$pattern,
		function ( $m ) {
			$href = html_entity_decode( $m['href'], ENT_QUOTES );

			// Strip query/fragment before checking the extension.
			$path = strtok( $href, '?#' );
			if ( ! preg_match( '#\.(jpe?g|png|gif|webp|avif|bmp|svg)$#i', (string) $path ) ) {
				return $m[0]; // Not an image link — leave untouched.
			}

			$img = $m['img'];

			// Already has the class? leave it.
			if ( preg_match( '#\bclass\s*=\s*(["\'])[^"\']*\babc-enlarge\b[^"\']*\1#i', $img ) ) {
				return $m[0];
			}

			if ( preg_match( '#\bclass\s*=\s*(["\'])(.*?)\1#i', $img, $cm ) ) {
				// Append to existing class attribute.
				$new_class = 'class=' . $cm[1] . trim( $cm[2] . ' abc-enlarge' ) . $cm[1];
				$new_img   = str_replace( $cm[0], $new_class, $img );
			} else {
				// No class attribute — add one right after <img.
				$new_img = preg_replace( '#<img\b#i', '<img class="abc-enlarge"', $img, 1 );
			}

			return $m[1] . $new_img;
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'abc_enlarge_filter_content', 20 );

require_once ABC_ENLARGE_DIR . 'includes/class-abc-enlarge-admin.php';
ABC_Enlarge_Admin::init();
