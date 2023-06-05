<?php
/**
 * @wordpress-plugin
 * Plugin Name:       WP INBODY 404's CLI
 * Plugin URI:        https://iamarunchaitanyajami.com
 * Description:       This plugin is useful to run custom cli that run a query on the posts table to find 404 in the body of each post type.
 * Version:           1.0.0
 * Author:            Arun Chaitanya Jami
 * Author URI:        https://iamarunchaitanyajami.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-inbody-404
 * @link              https://iamarunchaitanyajami.com
 * @since             1.0.0
 * @package           Wp_Inbody_404
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Load required files.
 */
require_once __DIR__ . '/not-found-urls/class-wp-inbody-404-urls.php';
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/not-found-urls/class-wp-inbody-404-urls-cli.php';
}
