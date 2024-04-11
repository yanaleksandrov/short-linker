<?php
namespace ShortLinker;

/**
 * Register new meta boxes
 *
 * @since      1.0.0
 * @package    ShortLinker
 */
class Metabox {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'add_meta_boxes', array( $this, 'add_link_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register new meta box
	 *
	 * @since    1.0.0
	 */
	public function add_link_meta_box() {
		add_meta_box(
			'short-linker-meta-box',
			esc_html__( 'Link info', 'short-linker' ),
			array( $this, 'render_link_meta_box' ),
			Constants::SHORT_LINKER_CPT,
			'normal'
		);
	}

	/**
	 * Output new meta box
	 *
	 * @since    1.0.0
	 */
	public function render_link_meta_box() {
		$post_id       = get_the_ID();
		$redirect_link = sanitize_text_field( get_post_meta( $post_id, 'redirect-link', true ) );
		$general_views = absint( get_post_meta( $post_id, 'general-views', true ) );
		$unique_views  = absint( get_post_meta( $post_id, 'unique-views', true ) );
		?>
		<div class="short-linker">
			<label class="short-linker-label">
				<?php esc_html_e( 'Redirect to:', 'short-linker' ); ?>
				<br>
				<input class="short-linker-url" type="url" value="<?php echo esc_attr( $redirect_link ); ?>" name="redirect-link" placeholder="<?php esc_attr_e( 'Write valid URL', 'short-linker' ); ?>" autocomplete="off">
			</label>

			<label class="short-linker-label half">
				<?php esc_html_e( 'General views', 'short-linker' ); ?>:
				<strong><?php echo wp_kses_post( $general_views ); ?></strong>
			</label>

			<label class="short-linker-label half">
				<?php esc_html_e( 'Unique views', 'short-linker' ); ?>:
				<strong><?php echo wp_kses_post( $unique_views ); ?></strong>
			</label>
		</div>
		<?php
	}

	/**
	 * Save meta box value
	 *
	 * @param int     $post_id Current post ID.
	 * @param WP_Post $post    Post object.
	 * @return mixed
	 */
	public function save_meta_box( $post_id, $post ) {
		$post_id   = get_the_ID();
		$post_type = get_post_type( $post_id );

		if ( Constants::SHORT_LINKER_CPT !== $post_type ) {
			return $post_id;
		}

		$nonce = sanitize_text_field( $_POST['_wpnonce'] ?? '' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'update-post_' . $post_id ) ) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$redirect_link = sanitize_text_field( $_POST['redirect-link'] ?? '' );
		if ( ! empty( $redirect_link ) && filter_var( $redirect_link, FILTER_VALIDATE_URL ) !== false ) {
			update_post_meta( $post_id, 'redirect-link', $redirect_link );
		} else {
			delete_post_meta( $post_id, 'redirect-link' );
		}

		return $post_id;
	}
}
