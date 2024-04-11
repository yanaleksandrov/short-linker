<?php
/**
 * Plugin Name:       Short Linker
 * Plugin URI:        http://codyshop.ru
 * Description:       Plugin for generating shortened links
 * Author:            Yan Aleksandrov
 * Author URI:        http://codyshop.ru
 * Text Domain:       short-linker
 * Domain Path:       /lang/
 * Requires PHP:      7.4
 * Requires at least: 5.5
 * Version:           1.0.0
 *
 * @package ShortLinker
 */

use ShortLinker\{
	CPT,
	I18n,
	Metabox,
	Singleton,
	Constants,
	Management
};

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Require Composer autoloader if it exists.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

if ( ! class_exists( 'Short_Linker' ) ) {

	/**
	 * Main class of plugin.
	 *
	 * @since 1.0.0
	 */
	class Short_Linker extends Singleton {

		/**
		 * Adds all the methods to appropriate hooks or shortcodes.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct();

			$versions = $this->is_not_supported_versions();
			if ( $versions ) {
				add_action(
					'admin_notices',
					function () use ( $versions ) {
						$php_version = sanitize_text_field( $versions['php'] ?? '' );
						$wp_version  = sanitize_text_field( $versions['wp'] ?? '' );

						echo wp_kses_post(
							'<div class="notice notice-error is-dismissible">' . sprintf(
								/* translators: %1$s - plugin name, %2$s - php version, %3$s - WP version */
								__( 'For %1$s plugin to work are required minimum versions: %2$s %3$s', 'short-linker' ),
								'<strong>Short Linker</strong>',
								$php_version,
								$wp_version
							) . '</div>'
						);
					}
				);

				return;
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueues_admin' ) );
			add_action( 'template_redirect', array( $this, 'add_redirect' ) );

			new CPT();
			new I18n();
			new Metabox();
			new Management();
		}

		/**
		 * Check if the PHP and WordPress versions is supported
		 *
		 * @return array|bool
		 * @since 1.0.0
		 */
		public function is_not_supported_versions() {
			global $wp_version;
			if ( version_compare( PHP_VERSION, '7.4', '<' ) || version_compare( $wp_version, '5.5', '<' ) ) {
				return array(
					'php' => sprintf(
						/* translators: %1$s - required php version, %2$s - php version in using */
						esc_html__( 'PHP - %1$s (you are using %2$s version)', 'short-linker' ),
						'7.4',
						PHP_VERSION
					),
					'wp'  => sprintf(
						/* translators: %1$s - required WP version, %2$s - WP version in using */
						esc_html__( 'WordPress - %1$s (you are using %2$s version)', 'short-linker' ),
						'5.5',
						$wp_version
					),
				);
			}
			return false;
		}

		/**
		 * Enqueue scripts and styles in admin console
		 *
		 * @since 1.0.0
		 */
		public function enqueues_admin() {
			$post_id   = get_the_ID();
			$post_type = get_post_type( $post_id );

			if ( Constants::SHORT_LINKER_CPT !== $post_type ) {
				return;
			}

			wp_enqueue_script( 'short-linker', plugins_url( '/assets/js/admin.js', __FILE__ ), array( 'jquery' ), Constants::SHORT_LINKER_VERSION, true );
			wp_enqueue_style( 'short-linker-css', plugins_url( '/assets/css/admin.css', __FILE__ ), array(), Constants::SHORT_LINKER_VERSION );
		}

		/**
		 * Redirect to Full URL
		 */
		public function add_redirect() {
			$post_id   = get_the_ID();
			$post_type = get_post_type( $post_id );

			if ( Constants::SHORT_LINKER_CPT !== $post_type || ! $post_id ) {
				return;
			}

			if ( ! session_id() ) {
				session_start();
			}

			$session_key            = "last_click_time_$post_id";
			$redirect_link          = sanitize_text_field( get_post_meta( $post_id, 'redirect-link', true ) );
			$last_click_time        = absint( $_SESSION[ $session_key ] ?? 0 );
			$current_click_time     = absint( time() );
			$last_click_is_outdated = ( $current_click_time - $last_click_time ) > Constants::SHORT_LINKER_SESSION_DURATION_SECONDS;

			if ( $redirect_link && filter_var( $redirect_link, FILTER_VALIDATE_URL ) !== false ) {
				if ( $last_click_is_outdated ) {
					$unique_views = absint( get_post_meta( $post_id, 'unique-views', true ) );
					$unique_views = ++$unique_views;
					update_post_meta( $post_id, 'unique-views', $unique_views );
				}

				$general_views = absint( get_post_meta( $post_id, 'general-views', true ) );
				$general_views = ++$general_views;
				update_post_meta( $post_id, 'general-views', $general_views );

				$_SESSION[ $session_key ] = time();
			} else {
				$redirect_link = home_url( '/404/' );
			}
			$redirect_link = wp_sanitize_redirect( $redirect_link );

			wp_safe_redirect( $redirect_link );
			exit;
		}
	}

	Short_Linker::get_instance();
}
