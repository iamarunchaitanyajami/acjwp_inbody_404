<?php
/**
 * WP-CLI commands for 404-URL's CHECKING.
 *
 * @package WordPress
 * @subpackage Wp_Inbody_404
 */

/**
 * Various commands relating to 404-URL's CHECKING.
 */
class Wp_Inbody_404_Urls_Cli {

	/**
	 * Find all the 404-URL's in body content.
	 *
	 * [--post_types]
	 * : Multiple post types separated by ,
	 *
	 * [--limit]
	 * : Post limit.
	 *
	 * [--post_status]
	 * : Post status.
	 *
	 * [--between]
	 * : Run the command for a specific period of time.
	 *
	 * @subcommand find-404-url
	 *
	 * @param       $args
	 * @param array $args_assoc
	 */
	public function find_404_url( $args, array $args_assoc = [] ) {
		$this->perform_bulk_operations( true );

		$args    = array();
		$between = array(
			'2016-06-01',
			date( 'Y-m-d' ),
		);

		if ( ! empty( $args_assoc['between'] ) ) {
			$is_date = preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]),[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $args_assoc['between'] );
			if ( ! $is_date ) {
				WP_CLI::log( 'Please provide --between param in YYYY-MM-DD,YYYY-MM-DD format' );

				return;
			}

			$between = explode( ',', $args_assoc['between'] );
		}

		if ( ! empty( $args_assoc['post_types'] ) ) {
			$args['post_types'] = explode( ',', $args_assoc['post_types'] );
		}

		if ( ! empty( $args_assoc['post_status'] ) ) {
			$args['post_status'] = explode( ',', $args_assoc['post_status'] );
		}

		if ( ! empty( $args_assoc['limit'] ) ) {
			$args['limit'] = $args_assoc['limit'];
		}

		$not_found_url_posts = [];
		$not_found_url       = new Wp_Inbody_404_Urls_Finder();

		$start_date = $between[0];
		while ( $start_date <= $between[1] ) {
			WP_CLI::log( sprintf( 'Running for date: %s', $start_date ) );

			$next_date       = date( 'Y-m-d', strtotime( $start_date . '+1 day' ) );
			$args['between'] = array(
				$start_date,
				$next_date
			);

			$urls = $not_found_url->get_all_404s( $args );
			WP_CLI::log( sprintf( 'Total processed data for date %s is %d', $start_date, count( $urls ) ) );
			if ( empty( $urls ) ) {
				WP_CLI::log( 'No posts found.' );

				$start_date = $next_date;
				continue;
			}

			$not_found_url_posts = array_replace( $not_found_url_posts, $urls );

			$start_date = $next_date;

			sleep( 1 );

			$this->start_inmemory_cleanup();
		}

		WP_CLI::log( sprintf( 'Gross Total Posts # %d', count( $not_found_url_posts ) ) );

		WP_CLI::Success( wp_json_encode( $not_found_url_posts ) );

		WP_CLI::log( 'Run Ends' );

		$this->perform_bulk_operations( false );
	}

	/**
	 * Disable term counting so that terms are not all recounted after every term operation.
	 */
	public function perform_bulk_operations( bool $defer ) {
		wp_defer_term_counting( $defer );
	}

	/**
	 * Reset the local WordPress object cache and WP query cache.
	 *
	 * This only cleans the local cache in WP_Object_Cache, without
	 * affecting memcache
	 */
	public function start_inmemory_cleanup() {
		global $wpdb, $wp_object_cache;

		$wpdb->queries = array();

		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}

		$wp_object_cache->group_ops      = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache          = array();

		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset(); // important
		}
	}
}

WP_CLI::add_command( 'inbody404', 'Wp_Inbody_404_Urls_Cli' );
