<?php
namespace ShortLinker;

/**
 * Manage links in dashboard table
 *
 * @since      1.0.0
 * @package    ShortLinker
 */
class Management {

	/**
	 * Class constructor
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$post_type = Constants::SHORT_LINKER_CPT;

		add_filter( "manage_{$post_type}_posts_columns", array( $this, 'add_new_columns' ) );
		add_filter( "manage_edit-{$post_type}_sortable_columns", array( $this, 'add_sortable_columns' ) );
		add_filter( "manage_{$post_type}_posts_custom_column", array( $this, 'fill_new_columns' ), 10, 2 );

		add_filter( 'request', array( $this, 'make_sortable_columns' ) );
	}

	/**
	 * Add new columns.
	 *
	 * @param array $post_columns Columns list.
	 * @return array
	 */
	public function add_new_columns( $post_columns ) {
		$post_columns = array_merge(
			$post_columns,
			array(
				'url'           => esc_html__( 'Page URL', 'short-linker' ),
				'redirect-url'  => esc_html__( 'Full Link', 'short-linker' ),
				'general-views' => esc_html__( 'General views', 'short-linker' ),
				'unique-views'  => esc_html__( 'Unique views', 'short-linker' ),
			)
		);

		return $post_columns;
	}

	/**
	 * Add new sortable columns.
	 *
	 * @param array $sortable_columns Columns list.
	 * @return array
	 */
	public function add_sortable_columns( $sortable_columns ) {
		$sortable_columns['general-views'] = array( 'general-views', false );
		$sortable_columns['unique-views']  = array( 'unique-views', false );

		return $sortable_columns;
	}

	/**
	 * Fill new columns.
	 *
	 * @param string $colname Column name.
	 * @param int    $post_id Post ID.
	 */
	public function fill_new_columns( $colname, $post_id ) {
		$value = '';
		switch ( $colname ) {
			case 'url':
				$post_url = esc_attr( get_post_permalink( $post_id ) );
				$value    = sprintf( '<a href="%s" target="_blank">%s</a>', $post_url, $post_url );
				break;
			case 'redirect-url':
				$redirect_link = esc_attr( get_post_meta( $post_id, 'redirect-link', true ) );
				$value         = sprintf( '<a href="%s" target="_blank">%s</a>', $redirect_link, $redirect_link );
				break;
			case 'general-views':
				$value = absint( get_post_meta( $post_id, 'general-views', true ) );
				break;
			case 'unique-views':
				$value = absint( get_post_meta( $post_id, 'unique-views', true ) );
				break;
		}
		echo wp_kses_post( $value );
	}

	/**
	 * Make columns sortable
	 *
	 * @param array $vars Query params.
	 * @return mixed
	 */
	public function make_sortable_columns( $vars ) {
		$orderby = sanitize_text_field( $vars['orderby'] ?? '' );
		if ( in_array( $orderby, array( 'general-views', 'unique-views' ), true ) ) {
			$vars['meta_key'] = $orderby; // phpcs:ignore
			$vars['orderby']  = 'meta_value_num';
		}
		return $vars;
	}
}
