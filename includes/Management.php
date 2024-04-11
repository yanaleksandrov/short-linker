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
				'url'          => esc_html__( 'Page URL', 'short-linker' ),
				'redirect-url' => esc_html__( 'Full Link', 'short-linker' ),
				'view-count'   => esc_html__( 'View Count', 'short-linker' ),
				'view-repeat'  => esc_html__( 'Repeat Viewing', 'short-linker' ),
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
		$sortable_columns['view-count']  = array( 'view-count', false );
		$sortable_columns['view-repeat'] = array( 'view-repeat', false );

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
			case 'view-count':
				$value = absint( get_post_meta( $post_id, 'view-count', true ) );
				break;
			case 'view-repeat':
				$value = absint( get_post_meta( $post_id, 'view-repeat', true ) );
				break;
		}
		echo wp_kses_post( $value );
	}
}
