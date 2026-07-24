<?php
/**
 * Plugin Name:       ABC Enlarge
 * Plugin URI:        https://github.com/kyocom/abc-enlarge-wp
 * Description:        Inline image zoom for WordPress powered by the abc-enlarge jQuery plugin. Automatically adds the "abc-enlarge" class to linked images in post content, and lets you disable enlargement per post (enabled by default).
 * Version:           1.2.0
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            ABC Japon (Kyo Ichida)
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

define( 'ABC_ENLARGE_VERSION', '1.2.0' );
define( 'ABC_ENLARGE_FILE', __FILE__ );
define( 'ABC_ENLARGE_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABC_ENLARGE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Post meta key that, when truthy, disables enlargement for a single post.
 * Enlargement is ENABLED by default (absence of the flag == enabled).
 */
define( 'ABC_ENLARGE_META_KEY', '_abc_enlarge_disabled' );

/**
 * Post meta key that, when truthy, excludes WordPress galleries.
 * Galleries are INCLUDED by default (absence of the flag == applied).
 */
define( 'ABC_ENLARGE_GALLERY_META_KEY', '_abc_enlarge_galleries_disabled' );

/**
 * Option name storing the plugin settings (which post types are enabled).
 */
define( 'ABC_ENLARGE_OPTION', 'abc_enlarge_options' );

/**
 * All post types the user may choose from on the settings page.
 *
 * Built-in post and page plus every public custom post type that supports the
 * content editor.
 *
 * @return string[]
 */
function abc_enlarge_candidate_post_types() {
	$custom = get_post_types(
		array(
			'public'   => true,
			'_builtin' => false,
		),
		'names'
	);

	$post_types = array_merge( array( 'post', 'page' ), array_values( $custom ) );

	$post_types = array_values(
		array_filter(
			array_unique( $post_types ),
			function ( $pt ) {
				return post_type_supports( $pt, 'editor' );
			}
		)
	);

	/**
	 * Filter the selectable post types shown on the settings page.
	 *
	 * @param string[] $post_types Array of post type slugs.
	 */
	return (array) apply_filters( 'abc_enlarge_post_types', $post_types );
}

/**
 * Post types abc-enlarge is currently enabled for (per the settings page).
 *
 * Until the settings are saved the default is every candidate post type, so
 * the plugin keeps working out of the box; unchecking a type opts it out.
 *
 * @return string[]
 */
function abc_enlarge_enabled_post_types() {
	$candidates = abc_enlarge_candidate_post_types();
	$options    = get_option( ABC_ENLARGE_OPTION );

	// Not configured yet -> default to all candidates.
	if ( ! is_array( $options ) || ! isset( $options['post_types'] ) ) {
		return $candidates;
	}

	return array_values( array_intersect( (array) $options['post_types'], $candidates ) );
}

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

	// Global gate: the post type must be enabled on the settings page.
	if ( ! in_array( $post->post_type, abc_enlarge_enabled_post_types(), true ) ) {
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
 * Whether abc-enlarge should also apply to WordPress galleries for the post.
 *
 * @param int|WP_Post|null $post Post ID or object. Defaults to current post.
 * @return bool True when galleries are included.
 */
function abc_enlarge_galleries_enabled_for_post( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	$disabled = (bool) get_post_meta( $post->ID, ABC_ENLARGE_GALLERY_META_KEY, true );

	/**
	 * Filter whether abc-enlarge applies to galleries for a specific post.
	 *
	 * @param bool    $enabled Whether galleries are included.
	 * @param WP_Post $post    The post object.
	 */
	return (bool) apply_filters( 'abc_enlarge_galleries_enabled_for_post', ! $disabled, $post );
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
 * Add the "abc-enlarge" class to eligible images in post content.
 *
 * Outside galleries only images wrapped in an <a> that points to an image
 * file are targeted, so nothing breaks. Inside WordPress galleries (when the
 * per-post gallery option is on) images are made enlargeable regardless of
 * their link setting by resolving a full-size image URL.
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
	$post_id = get_the_ID();
	if ( ! abc_enlarge_is_enabled_for_post( $post_id ) ) {
		return $content;
	}
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}

	// Detect a WordPress gallery (classic [gallery] is already expanded here,
	// since do_shortcode runs on the_content at priority 11, before us at 20).
	$has_gallery = ( false !== stripos( $content, 'wp-block-gallery' ) )
		|| (bool) preg_match( '/class\s*=\s*["\'][^"\']*\bgallery(?:-[a-z0-9-]+)?\b/i', $content );

	// Gallery-free content takes the fast, proven regex path unchanged.
	if ( ! $has_gallery || ! class_exists( 'DOMDocument' ) ) {
		return abc_enlarge_apply_to_linked_images( $content );
	}

	return abc_enlarge_apply_with_galleries( $content, abc_enlarge_galleries_enabled_for_post( $post_id ) );
}
add_filter( 'the_content', 'abc_enlarge_filter_content', 20 );

/**
 * Fast path: add the class to images wrapped in an <a> linking to an image file.
 *
 * @param string $content The post content.
 * @return string Filtered content.
 */
function abc_enlarge_apply_to_linked_images( $content ) {
	// Match <a href="...image..."> ... <img ...>
	$pattern = '#(<a\b[^>]*\bhref\s*=\s*(["\'])(?<href>[^"\']+?)\2[^>]*>\s*)(?<img><img\b[^>]*>)#i';

	return preg_replace_callback(
		$pattern,
		function ( $m ) {
			if ( ! abc_enlarge_href_is_image( $m['href'] ) ) {
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
}

/**
 * Whether an href points to an image file.
 *
 * @param string $href The URL.
 * @return bool
 */
function abc_enlarge_href_is_image( $href ) {
	$href = html_entity_decode( (string) $href, ENT_QUOTES );
	$path = strtok( $href, '?#' ); // Strip query/fragment before the extension check.
	return (bool) preg_match( '#\.(jpe?g|png|gif|webp|avif|bmp|svg)$#i', (string) $path );
}

/**
 * DOM path: handle content that contains a WordPress gallery.
 *
 * Non-gallery images keep the safe "linked image only" rule. Gallery images
 * are made enlargeable (when $apply_galleries) by resolving a large image URL
 * and ensuring an <a href> wrapper, so they work regardless of link setting.
 *
 * @param string $content         The post content.
 * @param bool   $apply_galleries Whether to include gallery images.
 * @return string Filtered content.
 */
function abc_enlarge_apply_with_galleries( $content, $apply_galleries ) {
	$dom  = new DOMDocument();
	$prev = libxml_use_internal_errors( true );

	// The XML prolog pins UTF-8; the wrapper gives a single, known root node.
	$loaded = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="abc-enlarge-root">' . $content . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );

	if ( ! $loaded ) {
		return abc_enlarge_apply_to_linked_images( $content );
	}

	$xpath = new DOMXPath( $dom );
	$roots = $xpath->query( '//*[@id="abc-enlarge-root"]' );
	$root  = $roots ? $roots->item( 0 ) : null;
	if ( ! $root ) {
		return abc_enlarge_apply_to_linked_images( $content );
	}

	$images  = $xpath->query( './/img', $root );
	$changed = false;

	if ( $images ) {
		foreach ( $images as $img ) {
			$class = $img->getAttribute( 'class' );
			if ( preg_match( '/\babc-enlarge\b/', $class ) ) {
				continue;
			}

			$parent = $img->parentNode;

			if ( abc_enlarge_node_in_gallery( $img ) ) {
				if ( ! $apply_galleries ) {
					continue;
				}
				$large = abc_enlarge_resolve_large_url( $img );
				if ( '' === $large ) {
					continue;
				}
				if ( $parent && 'a' === strtolower( $parent->nodeName ) ) {
					$parent->setAttribute( 'href', $large );
				} else {
					$anchor = $dom->createElement( 'a' );
					$anchor->setAttribute( 'href', $large );
					$parent->replaceChild( $anchor, $img );
					$anchor->appendChild( $img );
				}
				abc_enlarge_add_class( $img, $class );
				$changed = true;
			} elseif ( $parent && 'a' === strtolower( $parent->nodeName )
				&& abc_enlarge_href_is_image( $parent->getAttribute( 'href' ) ) ) {
				abc_enlarge_add_class( $img, $class );
				$changed = true;
			}
		}
	}

	if ( ! $changed ) {
		return $content;
	}

	$html = '';
	foreach ( $root->childNodes as $child ) {
		$html .= $dom->saveHTML( $child );
	}
	return $html;
}

/**
 * Append the abc-enlarge class to an image node.
 *
 * @param DOMElement $img   The image node.
 * @param string     $class Its current class attribute.
 */
function abc_enlarge_add_class( $img, $class ) {
	$class = trim( $class );
	$img->setAttribute( 'class', '' === $class ? 'abc-enlarge' : $class . ' abc-enlarge' );
}

/**
 * Whether a node sits inside a WordPress gallery container.
 *
 * @param DOMNode $node The node to test.
 * @return bool
 */
function abc_enlarge_node_in_gallery( $node ) {
	for ( $p = $node->parentNode; $p && XML_ELEMENT_NODE === $p->nodeType; $p = $p->parentNode ) {
		if ( ! ( $p instanceof DOMElement ) || ! $p->hasAttribute( 'class' ) ) {
			continue;
		}
		$c = ' ' . preg_replace( '/\s+/', ' ', $p->getAttribute( 'class' ) ) . ' ';
		if ( false !== strpos( $c, ' gallery ' )
			|| false !== strpos( $c, ' wp-block-gallery ' )
			|| preg_match( '/ gallery-columns-\d+ /', $c ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Resolve a large image URL for a gallery image, never returning a non-image.
 *
 * Priority: existing image-file link, then wp-image-{id} full size, then the
 * src with any -WxH size suffix stripped, then the src itself (inline enlarge
 * only) so the image can never break.
 *
 * @param DOMElement $img The image node.
 * @return string Large image URL, or '' when none can be determined.
 */
function abc_enlarge_resolve_large_url( $img ) {
	$parent = $img->parentNode;
	if ( $parent && 'a' === strtolower( $parent->nodeName ) ) {
		$href = $parent->getAttribute( 'href' );
		if ( abc_enlarge_href_is_image( $href ) ) {
			return $href;
		}
	}

	if ( preg_match( '/\bwp-image-(\d+)\b/', $img->getAttribute( 'class' ), $mm )
		&& function_exists( 'wp_get_attachment_image_url' ) ) {
		$url = wp_get_attachment_image_url( (int) $mm[1], 'full' );
		if ( $url ) {
			return $url;
		}
	}

	$src = $img->getAttribute( 'src' );
	if ( '' !== $src ) {
		$full = preg_replace( '/-\d+x\d+(\.(?:jpe?g|png|gif|webp|avif|bmp))$/i', '$1', $src );
		if ( $full !== $src ) {
			return $full;
		}
		if ( abc_enlarge_href_is_image( $src ) ) {
			return $src; // No larger size known — enlarge the current image inline.
		}
	}

	return '';
}

require_once ABC_ENLARGE_DIR . 'includes/class-abc-enlarge-admin.php';
ABC_Enlarge_Admin::init();

require_once ABC_ENLARGE_DIR . 'includes/class-abc-enlarge-settings.php';
ABC_Enlarge_Settings::init();
