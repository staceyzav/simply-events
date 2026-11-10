<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================================================
// iCAL FEED — /events/calendar.ics
// Subscribe URL for Apple Calendar, Google Calendar, Outlook, etc.
// ==========================================================================

add_action( 'init', 'se_ical_rewrite' );

function se_ical_rewrite() {
	add_rewrite_rule( '^events/calendar\.ics$', 'index.php?simply_events_ical=1', 'top' );
}

add_filter( 'query_vars', 'se_ical_query_var' );

function se_ical_query_var( $vars ) {
	$vars[] = 'simply_events_ical';
	return $vars;
}

add_action( 'template_redirect', 'se_ical_output' );

function se_ical_output() {
	if ( ! get_query_var( 'simply_events_ical' ) ) return;

	$events = new WP_Query( array(
		'post_type'      => 'simply_event',
		'posts_per_page' => -1,
		'meta_key'       => '_event_start_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
	) );

	header( 'Content-Type: text/calendar; charset=UTF-8' );
	header( 'Content-Disposition: inline; filename="events.ics"' );
	header( 'Cache-Control: no-cache, must-revalidate' );

	$site_name = get_bloginfo( 'name' );
	$site_host = parse_url( home_url(), PHP_URL_HOST );

	echo "BEGIN:VCALENDAR\r\n";
	echo "VERSION:2.0\r\n";
	echo "PRODID:-//Simply Events//Simply Events//EN\r\n";
	echo "CALSCALE:GREGORIAN\r\n";
	echo "METHOD:PUBLISH\r\n";
	echo "X-WR-CALNAME:" . se_ical_escape( $site_name ) . " Events\r\n";

	while ( $events->have_posts() ) {
		$events->the_post();
		$post_id    = get_the_ID();
		$e_start    = get_post_meta( $post_id, '_event_start_date', true );
		$e_end      = get_post_meta( $post_id, '_event_end_date',   true );
		$start_time = get_post_meta( $post_id, '_event_start_time', true );
		$end_time   = get_post_meta( $post_id, '_event_end_time',   true );
		$location   = get_post_meta( $post_id, '_event_location',   true );

		if ( ! $e_start ) continue;

		$uid = 'simply-event-' . $post_id . '@' . $site_host;

		if ( $start_time ) {
			// Timed event — DTSTART/DTEND as local datetime (no timezone suffix = floating)
			$dtstart_val = str_replace( '-', '', $e_start ) . 'T' . str_replace( ':', '', $start_time ) . '00';
			$dtstart_line = 'DTSTART:' . $dtstart_val;

			if ( $e_end && $end_time ) {
				$dtend_val  = str_replace( '-', '', $e_end ) . 'T' . str_replace( ':', '', $end_time ) . '00';
			} else {
				$dtend_val  = $dtstart_val;
			}
			$dtend_line = 'DTEND:' . $dtend_val;
		} else {
			// All-day event — DATE value type; end date is exclusive
			$end_date     = ( $e_end && $e_end >= $e_start ) ? $e_end : $e_start;
			$end_excl     = date( 'Ymd', strtotime( $end_date . ' +1 day' ) );
			$dtstart_line = 'DTSTART;VALUE=DATE:' . str_replace( '-', '', $e_start );
			$dtend_line   = 'DTEND;VALUE=DATE:' . $end_excl;
		}

		$title   = se_ical_escape( get_the_title() );
		$excerpt = se_ical_escape( wp_strip_all_tags( get_the_excerpt() ) );
		$loc     = $location ? se_ical_escape( $location ) : '';
		$url     = get_permalink( $post_id );

		echo "BEGIN:VEVENT\r\n";
		echo "UID:{$uid}\r\n";
		echo "{$dtstart_line}\r\n";
		echo "{$dtend_line}\r\n";
		echo "SUMMARY:{$title}\r\n";
		if ( $excerpt ) echo "DESCRIPTION:{$excerpt}\r\n";
		if ( $loc )     echo "LOCATION:{$loc}\r\n";
		echo "URL:{$url}\r\n";
		echo "END:VEVENT\r\n";
	}
	wp_reset_postdata();

	echo "END:VCALENDAR\r\n";
	exit;
}

function se_ical_escape( $value ) {
	return str_replace(
		array( '\\',  ',',  ';',  "\n"  ),
		array( '\\\\', '\\,', '\\;', '\\n' ),
		$value
	);
}
