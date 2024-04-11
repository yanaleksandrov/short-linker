<?php
namespace ShortLinker;

/**
 * Add custom post types
 *
 * @since      1.0.0
 * @package    ShortLinker
 */
class CPT {

	/**
	 * Add new CPT
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_cpt' ) );
	}

	/**
	 * Register post type
	 *
	 * @return void
	 */
	public function register_cpt() {
		register_post_type(
			Constants::SHORT_LINKER_CPT,
			array(
				'label'               => esc_html__( 'Short Links', 'short-linker' ),
				'labels'              => array(
					'name'          => esc_html__( 'Short Links', 'short-linker' ),
					'singular_name' => esc_html__( 'Short Link', 'short-linker' ),
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'delete_with_user'    => false,
				'exclude_from_search' => false,
				'capability_type'     => 'page',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'query_var'           => true,
				'menu_icon'           => 'dashicons-admin-links',
				'supports'            => array( 'title' ),
			)
		);
	}
}
