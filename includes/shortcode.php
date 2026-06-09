<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================================================
// ENQUEUE
// ==========================================================================

add_action( 'wp_enqueue_scripts', 'simply_events_enqueue' );

function simply_events_enqueue() {
	wp_enqueue_style(
		'simply-events',
		SIMPLY_EVENTS_URL . 'assets/css/simply-events.css',
		array(),
		SIMPLY_EVENTS_VERSION
	);
	wp_enqueue_script(
		'simply-events',
		SIMPLY_EVENTS_URL . 'assets/js/simply-events.js',
		array(),
		SIMPLY_EVENTS_VERSION,
		true
	);
}


// ==========================================================================
// SHORTCODE — [simply_events]
//
// Attributes:
//   title       Section heading. Default: "Upcoming Events"
//   limit       Max events to show. Default: 5
//   show_filter Show category filter tabs. Default: true
//   cta_text    CTA button label. Default: "View Full Schedule"
//   cta_url     CTA button URL. Default: /events
//   category    Slug — pre-filter to a specific category. Default: all
//   show_future Include upcoming events (start date >= today). Default: true
//   show_past   Include past events (start date < today). Default: false
//   order       Sort by start date: ASC (soonest first) or DESC (latest first). Default: ASC
//   view        Display mode: grid or list. Default: grid
// ==========================================================================

add_shortcode( 'simply_events', 'simply_events_shortcode' );

function simply_events_shortcode( $atts ) {

	$atts = shortcode_atts( array(
		'title'       => __( 'Upcoming Events', 'simply-events' ),
		'limit'       => 5,
		'show_filter' => 'true',
		'cta_text'    => '',
		'cta_url'     => '',
		'category'    => '',
		'show_future' => 'true',
		'show_past'   => 'false',
		'order'       => 'ASC',
		'view'        => 'grid',
	), $atts, 'simply_events' );

	$limit       = absint( $atts['limit'] );
	$show_filter = filter_var( $atts['show_filter'], FILTER_VALIDATE_BOOLEAN );
	$show_future = filter_var( $atts['show_future'], FILTER_VALIDATE_BOOLEAN );
	$show_past   = filter_var( $atts['show_past'],   FILTER_VALIDATE_BOOLEAN );
	$order       = strtoupper( $atts['order'] ) === 'DESC' ? 'DESC' : 'ASC';
	$view        = $atts['view'] === 'list' ? 'list' : 'grid';
	$title       = esc_html( $atts['title'] );
	$cta_text    = esc_html( $atts['cta_text'] );
	$cta_url     = esc_url( $atts['cta_url'] );

	// Build date meta_query based on show_future / show_past
	$meta_query = array();
	if ( $show_future && ! $show_past ) {
		$meta_query[] = array(
			'key'     => '_event_start_date',
			'value'   => current_time( 'Y-m-d' ),
			'compare' => '>=',
			'type'    => 'DATE',
		);
	} elseif ( $show_past && ! $show_future ) {
		$meta_query[] = array(
			'key'     => '_event_start_date',
			'value'   => current_time( 'Y-m-d' ),
			'compare' => '<',
			'type'    => 'DATE',
		);
	}
	// Both true or both false → no date restriction

	$tax_query = array();
	if ( ! empty( $atts['category'] ) ) {
		$tax_query[] = array(
			'taxonomy' => 'simply_event_cat',
			'field'    => 'slug',
			'terms'    => sanitize_text_field( $atts['category'] ),
		);
	}

	$query_args = array(
		'post_type'      => 'simply_event',
		'posts_per_page' => $limit,
		'meta_key'       => '_event_start_date',
		'orderby'        => 'meta_value',
		'order'          => $order,
	);

	if ( ! empty( $meta_query ) ) {
		$query_args['meta_query'] = $meta_query;
	}

	if ( ! empty( $tax_query ) ) {
		$query_args['tax_query'] = $tax_query;
	}

	$events = new WP_Query( $query_args );

	// Get all categories for filter tabs
	$categories = get_terms( array(
		'taxonomy'   => 'simply_event_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
	) );

	ob_start();
	?>
	<div class="se-events-block">

		<div class="se-events-header">

			<h2 class="se-events-title"><?php echo $title; ?></h2>

			<?php if ( $show_filter && ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
			<nav class="se-events-filter" aria-label="<?php esc_attr_e( 'Filter events by category', 'simply-events' ); ?>">
				<button class="se-filter-btn is-active" data-cat="all">
					<?php esc_html_e( 'All', 'simply-events' ); ?>
				</button>
				<?php foreach ( $categories as $cat ) : ?>
				<button class="se-filter-btn" data-cat="<?php echo esc_attr( $cat->slug ); ?>">
					<?php echo esc_html( $cat->name ); ?>
				</button>
				<?php endforeach; ?>
			</nav>
			<?php endif; ?>

			<?php if ( $cta_url && $cta_text ) : ?>
			<a href="<?php echo $cta_url; ?>" class="se-events-cta">
				<?php echo $cta_text; ?>
			</a>
			<?php endif; ?>

		</div>

		<?php if ( $events->have_posts() ) : ?>
		<div class="se-events-<?php echo $view; ?>"><?php // phpcs:ignore ?>
			<?php while ( $events->have_posts() ) : $events->the_post(); ?>
				<?php
				$post_id  = get_the_ID();
				$start    = get_post_meta( $post_id, '_event_start_date', true );
				$end      = get_post_meta( $post_id, '_event_end_date', true );
				$location = get_post_meta( $post_id, '_event_location', true );
				$pdf      = get_post_meta( $post_id, '_event_pdf', true );

				// Build data-cats for JS filtering
				$terms    = get_the_terms( $post_id, 'simply_event_cat' );
				$cat_slugs = '';
				if ( $terms && ! is_wp_error( $terms ) ) {
					$cat_slugs = implode( ' ', wp_list_pluck( $terms, 'slug' ) );
				}

				// Category label — first term name
				$cat_label = '';
				if ( $terms && ! is_wp_error( $terms ) ) {
					$cat_label = $terms[0]->name;
				}

				// Date formatting
				$start_ts     = $start ? strtotime( $start ) : false;
				$end_ts       = ( $end && $end !== $start ) ? strtotime( $end ) : false;
				$start_day    = $start_ts ? date( 'd', $start_ts ) : '';
				$start_month  = $start_ts ? date( 'M', $start_ts ) : '';
				$start_year   = $start_ts ? date( 'Y', $start_ts ) : '';
				$end_day      = $end_ts ? date( 'd', $end_ts ) : '';
				$end_month    = $end_ts ? date( 'M', $end_ts ) : '';
				?>
				<article class="se-event-card ss-card" data-cats="<?php echo esc_attr( $cat_slugs ); ?>">

					<div class="se-event-card__date">
						<div class="se-event-card__date-start">
							<span class="se-event-card__day"><?php echo esc_html( $start_day ); ?></span>
							<span class="se-event-card__month"><?php echo esc_html( strtoupper( $start_month ) ); ?></span>
						</div>
						<?php if ( $end_ts ) : ?>
						<div class="se-event-card__date-end">
							<span class="se-event-card__sep">-</span>
							<div class="se-event-card__date-end-col">
								<span class="se-event-card__day se-event-card__day--small"><?php echo esc_html( $end_day ); ?></span>
								<span class="se-event-card__month"><?php echo esc_html( strtoupper( $end_month ) ); ?></span>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( $start_year ) : ?>
						<span class="se-event-card__year"><?php echo esc_html( $start_year ); ?></span>
						<?php endif; ?>
					</div>

					<div class="se-event-card__body ss-card-body">
						<h3 class="se-event-card__title">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="se-event-card__title-link"><?php the_title(); ?></a>
						</h3>
						<?php if ( $location ) : ?>
						<p class="se-event-card__location"><?php echo esc_html( $location ); ?></p>
						<?php endif; ?>
						<?php if ( $cat_label ) : ?>
						<p class="se-event-card__category"><?php echo esc_html( $cat_label ); ?></p>
						<?php endif; ?>
					</div>

				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>

		<?php else : ?>
		<p class="se-events-empty"><?php esc_html_e( 'No upcoming events scheduled.', 'simply-events' ); ?></p>
		<?php endif; ?>

	</div>
	<?php
	return ob_get_clean();
}
