<?php
/**
 * Find all the 404 URI's present in the POST content.
 *
 * @package WordPress
 * @subpackage Wp_Inbody_404
 */

/**
 * Class Not Found Url.
 */
class Wp_Inbody_404_Urls_Finder {

	/**
	 * Fine all the url in given string.
	 *
	 * @param string $content Wp content.
	 *
	 * @return array
	 */
	public function find_urls_in_content( string $content ): array {
		$pattern = '/(https?:\/\/[^\s"\'<>]+)/';
		preg_match_all( $pattern, $content, $matches );

		return $matches[0];
	}

	/**
	 * Return 404 urls for all posts.
	 *
	 * @param array $post_types Post Types.
	 * @param int $post_page_page Post per page.
	 * @param array $post_status Post status.
	 * @param array $date_between Fixed period between dates.
	 *
	 * @return array
	 */
	public function posts( array $post_types = [ 'post', 'page' ], int $post_page_page = 1000, array $post_status = [ 'publish' ], array $date_between = [] ): array {
		global $wpdb;

		$between = [
			'2001-02-01',
			date('Y-m-d'),
		];

		if ( ! empty( $date_between ) ) {
			$between[0] = $date_between[0];
			$between[1] = $date_between[1];
		}

		$paged              = 1;
		$not_found_URLs     = array();
		$post_types_string  = "'" . implode( "', '", $post_types ) . "'";
		$post_status_string = "'" . implode( "', '", $post_status ) . "'";
		$offset             = ( $paged - 1 ) * $post_page_page;
		$query              = sprintf(
			"SELECT ID, post_type, post_status, post_content FROM %1s WHERE post_type IN (%2s) AND post_status IN (%3s) AND post_content REGEXP '(http|https)://[^$.?#].[^\s]*' AND post_date >= '%4s' AND post_date <= '%5s' LIMIT %d OFFSET %d",
			$wpdb->posts,
			$post_types_string,
			$post_status_string,
			$between[0],
			$between[1],
			$post_page_page,
			$offset
		);
		$results            = $wpdb->get_results( $query );

		foreach ( $results as $found_posts ) {
			$found_urls = $this->find_urls_in_content( $found_posts->post_content );
			if ( ! empty( $found_urls ) ) {
				$not_found_URLs[ $found_posts->ID ] = $found_urls;
			}
		}

		return $not_found_URLs;
	}

	/**
	 * Get all the 404 URL's from the WP database.
	 *
	 * @param array $args Wp Query arguments.
	 *
	 * @return array
	 */
	public function get_all_404s( array $args = [] ): array {
		$default_args = [
			'post_type'      => [ 'post', 'page' ],
			'post_page_page' => 1000,
			'post_status'    => [ 'publish' ]
		];

		$args        = array_merge( $default_args, $args );
		$urls        = $this->posts( $args['post_type'], $args['post_page_page'], $args['post_status'], $args['between'] );
		$founds_urls = array();
		if ( empty( $urls ) ) {
			return $founds_urls;
		}

		foreach ( $urls as $post_id => $urlArray ) {
			$is_404 = [];
			foreach ( $urlArray as $url ) {
				if ( $this->is_404( $url ) ) {
					$is_404[] = $url;
				}
			}

			if ( ! empty( $is_404 ) ) {
				$founds_urls[ $post_id ] = $is_404;
			}
		}

		return $founds_urls;
	}

	/**
	 * Is a give url findable.
	 *
	 * @param string $url Url to find the status
	 *
	 * @return bool
	 */
	public function is_404( string $url ): bool {
		if ( ! str_contains( $url, site_url() ) ) {
			return false;
		}

		$response = wp_safe_remote_get( $url );
		$status   = wp_remote_retrieve_response_code( $response );

		$allowed_status = apply_filters( 'vip_wp_not_found_urls_finder_allowed_status', [ 200 ], $status );

		return ! in_array( $status, $allowed_status, true );
	}
}
