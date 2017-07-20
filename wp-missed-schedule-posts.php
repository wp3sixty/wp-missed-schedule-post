<?php
/*
Plugin Name: WP Missed Schedule Posts
Description: Auto publish future post missed by WordPress cron
Author: newvariable
Contributors: newvariable,wp3sixty,sanketparmar,divyarajmasani,shaileesheth
Author URI: https://newvariable.com
Version: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

//bail of not wordpress path
if ( false === defined( 'ABSPATH' ) ) {
	return;
}
//plugin basename for further refrence
$nv_wpms_base_name = plugin_basename( __FILE__ );

//Set default check interval - every 10 min
if ( false === defined( 'WPMSP_INTERVAL' ) ) {
	define( 'WPMSP_INTERVAL', 15 * MINUTE_IN_SECONDS );
}
//Set post limit
if ( false === defined( 'WPMSP_POST_LIMIT' ) ) {
	define( 'WPMSP_POST_LIMIT', 20 );
}
//Hook into WordPress
add_action( 'init', 'nv_wpmsp_init', 0 );
//Plugin Actions
add_filter( 'plugin_action_links_' . $nv_wpms_base_name, 'nv_wpmsp_plugin_activation_link', 10, 1 );
add_filter( 'plugin_row_meta', 'nv_wpmsp_plugin_row_meta', 10, 2 );

/**
 * Check timestamp from transient and published all missed posts
 */
function nv_wpmsp_init() {

	$last_scheduled_missed_time = get_transient( 'wp_scheduled_missed_time' );

	$time = current_time( 'timestamp', 0 );

	if ( false !== $last_scheduled_missed_time && absint( $last_scheduled_missed_time ) > ( $time - WPMSP_INTERVAL ) ) {
		return;
	}

	set_transient( 'wp_scheduled_missed_time', $time, WPMSP_INTERVAL );

	global $wpdb;

	$sql_query = "SELECT ID FROM {$wpdb->posts} WHERE ( ( post_date > 0 && post_date <= %s ) ) AND post_status = 'future' LIMIT 0,%d";

	$sql = $wpdb->prepare( $sql_query, current_time( 'mysql', 0 ), WPMSP_POST_LIMIT );

	$scheduled_post_ids = $wpdb->get_col( $sql );

	if ( ! count( $scheduled_post_ids ) ) {
		return;
	}

	foreach ( $scheduled_post_ids as $scheduled_post_id ) {
		if ( ! $scheduled_post_id ) {
			continue;
		}

		wp_publish_post( $scheduled_post_id );
	}
}


/**
 * Add plugin activation link
 *
 * @param $links
 *
 * @return array
 */
function nv_wpmsp_plugin_activation_link( $links ) {
	$links[] = '<a href="edit.php?post_status=future&post_type=post">' . esc_html__( 'Miss', 'nv-wpmsp' ) . '</a>';

	return $links;
}

/**
 * Add link in plugin row meta
 *
 * @param $links
 *
 * @return array
 */

function nv_wpmsp_plugin_row_meta( $links, $file ) {
	if ( false === is_admin() ) {
		return;
	}


	if ( false === current_user_can( 'administrator' ) ) {
		return;
	}

	if ( $file == plugin_basename( __FILE__ ) ) {
		$links[] = '<a href="https://newvariable.com/contact/">' . esc_html__( 'Contact', 'nv-wpmsp' ) . '</a>';
	}

	return $links;
}