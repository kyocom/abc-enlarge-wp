<?php
/**
 * Settings page: choose which post types abc-enlarge is enabled for.
 *
 * @package ABC_Enlarge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers a Settings -> ABC Enlarge page with one checkbox per candidate
 * post type. Only the checked post types are enabled; unchecking one opts it
 * out globally.
 */
class ABC_Enlarge_Settings {

	const OPTION_GROUP = 'abc_enlarge_group';
	const PAGE_SLUG    = 'abc-enlarge';

	/**
	 * Wire up hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( ABC_ENLARGE_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Add the options page under the Settings menu.
	 */
	public static function add_page() {
		add_options_page(
			__( 'ABC Enlarge', 'abc-enlarge' ),
			__( 'ABC Enlarge', 'abc-enlarge' ),
			'manage_options',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Register the setting and its sanitizer.
	 */
	public static function register() {
		register_setting(
			self::OPTION_GROUP,
			ABC_ENLARGE_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
			)
		);
	}

	/**
	 * Keep only valid, known post-type slugs.
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array Sanitized option value.
	 */
	public static function sanitize( $input ) {
		$candidates = abc_enlarge_candidate_post_types();
		$selected   = array();

		if ( is_array( $input ) && isset( $input['post_types'] ) && is_array( $input['post_types'] ) ) {
			$clean    = array_map( 'sanitize_key', $input['post_types'] );
			$selected = array_values( array_intersect( $clean, $candidates ) );
		}

		return array( 'post_types' => $selected );
	}

	/**
	 * Add a "Settings" link on the Plugins screen.
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public static function action_links( $links ) {
		$url  = admin_url( 'options-general.php?page=' . self::PAGE_SLUG );
		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'abc-enlarge' ) . '</a>';
		array_unshift( $links, $link );
		return $links;
	}

	/**
	 * Render the settings page.
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$candidates = abc_enlarge_candidate_post_types();
		$enabled    = abc_enlarge_enabled_post_types();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'ABC Enlarge', 'abc-enlarge' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( self::OPTION_GROUP ); ?>
				<h2><?php esc_html_e( 'Enabled post types', 'abc-enlarge' ); ?></h2>
				<p class="description">
					<?php esc_html_e( 'Image enlargement runs only on the post types you check here. Unchecked post types are disabled.', 'abc-enlarge' ); ?>
				</p>
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Post types', 'abc-enlarge' ); ?></th>
							<td>
								<fieldset>
									<?php foreach ( $candidates as $pt ) : ?>
										<?php $obj = get_post_type_object( $pt ); ?>
										<?php if ( ! $obj ) { continue; } ?>
										<label style="display:block;margin:0 0 6px;">
											<input
												type="checkbox"
												name="<?php echo esc_attr( ABC_ENLARGE_OPTION ); ?>[post_types][]"
												value="<?php echo esc_attr( $pt ); ?>"
												<?php checked( in_array( $pt, $enabled, true ) ); ?>
											/>
											<?php echo esc_html( $obj->labels->name ); ?>
											<code><?php echo esc_html( $pt ); ?></code>
										</label>
									<?php endforeach; ?>
									<?php if ( empty( $candidates ) ) : ?>
										<p><?php esc_html_e( 'No eligible post types were found.', 'abc-enlarge' ); ?></p>
									<?php endif; ?>
								</fieldset>
							</td>
						</tr>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
