<?php
/**
 * Admin-side handling: per-post "disable enlargement" option.
 *
 * @package ABC_Enlarge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the post meta and the classic-editor meta box that let an author
 * disable abc-enlarge for an individual post. Enlargement is enabled by
 * default, so the stored meta only ever records the "disabled" state.
 */
class ABC_Enlarge_Admin {

	const NONCE_ACTION = 'abc_enlarge_save_meta';
	const NONCE_NAME   = 'abc_enlarge_nonce';

	/**
	 * Wire up hooks.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta' ), 10, 2 );
	}

	/**
	 * Post types that should expose the option.
	 *
	 * @return string[]
	 */
	protected static function post_types() {
		/**
		 * Filter the post types that get the abc-enlarge toggle.
		 *
		 * @param string[] $post_types Array of post type slugs.
		 */
		return (array) apply_filters( 'abc_enlarge_post_types', array( 'post', 'page' ) );
	}

	/**
	 * Register the meta so it is available to the block editor (REST) too.
	 */
	public static function register_meta() {
		foreach ( self::post_types() as $post_type ) {
			register_post_meta(
				$post_type,
				ABC_ENLARGE_META_KEY,
				array(
					'type'          => 'boolean',
					'single'        => true,
					'default'       => false,
					'show_in_rest'  => true,
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Add the classic-editor meta box.
	 */
	public static function add_meta_box() {
		foreach ( self::post_types() as $post_type ) {
			add_meta_box(
				'abc-enlarge',
				__( 'ABC Enlarge', 'abc-enlarge' ),
				array( __CLASS__, 'render_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render the meta box contents.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_meta_box( $post ) {
		$disabled = (bool) get_post_meta( $post->ID, ABC_ENLARGE_META_KEY, true );

		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<p>
			<label>
				<input type="checkbox" name="abc_enlarge_disabled" value="1" <?php checked( $disabled ); ?> />
				<?php esc_html_e( 'Disable image enlargement for this post', 'abc-enlarge' ); ?>
			</label>
		</p>
		<p class="description">
			<?php esc_html_e( 'Enlargement is enabled by default. Check this to turn it off for this post only.', 'abc-enlarge' ); ?>
		</p>
		<?php
	}

	/**
	 * Persist the option on save.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_meta( $post_id, $post ) {
		// Bail on autosave / revisions / bulk edits without our form.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! in_array( $post->post_type, self::post_types(), true ) ) {
			return;
		}

		// Only act when our classic meta box was actually submitted.
		// (The block editor writes the meta via REST and is handled separately.)
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$disabled = ! empty( $_POST['abc_enlarge_disabled'] );

		if ( $disabled ) {
			update_post_meta( $post_id, ABC_ENLARGE_META_KEY, true );
		} else {
			// Keep the DB clean: default (enabled) stores nothing.
			delete_post_meta( $post_id, ABC_ENLARGE_META_KEY );
		}
	}
}
