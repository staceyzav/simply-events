<?php
/**
 * Plugin Name: Simply Events
 * Plugin URI:  https://simplydesign.com/simply-events
 * Description: Events CPT with date range, location, PDF, photo, and category taxonomy. Upcoming events feed via [simply_events] shortcode. WP repo candidate — zero dependencies.
 * Author:      Simply Design
 * Author URI:  https://simplydesign.com
 * Version:     1.3.0
 * License:     GPL-2.0-or-later
 * Text Domain: simply-events
 * Requires at least: 5.4
 * Requires PHP: 7.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SIMPLY_EVENTS_VERSION', '1.3.0' );
define( 'SIMPLY_EVENTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SIMPLY_EVENTS_URL',  plugin_dir_url( __FILE__ ) );

require_once SIMPLY_EVENTS_PATH . 'includes/cpt.php';
require_once SIMPLY_EVENTS_PATH . 'includes/shortcode.php';

// ==========================================================================
// SINGLE EVENT TEMPLATE
// ==========================================================================

add_filter( 'single_template', 'simply_events_single_template' );

function simply_events_single_template( $template ) {
	if ( is_singular( 'simply_event' ) ) {
		return SIMPLY_EVENTS_PATH . 'templates/single-event.php';
	}
	return $template;
}

add_action( 'wp_enqueue_scripts', 'simply_events_enqueue_single' );

function simply_events_enqueue_single() {
	if ( ! is_singular( 'simply_event' ) ) return;
	wp_enqueue_style(
		'simply-events-single',
		SIMPLY_EVENTS_URL . 'assets/css/simply-events-single.css',
		array(),
		SIMPLY_EVENTS_VERSION
	);
}
