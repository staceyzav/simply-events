<?php
/**
 * Single Event Template
 * Loaded via single_template filter for simply_event posts.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

if ( have_posts() ) :
	the_post();

	$post_id       = get_the_ID();
	$remove_header = get_post_meta( $post_id, '_event_remove_header',  true );
	$start         = get_post_meta( $post_id, '_event_start_date',     true );
	$end           = get_post_meta( $post_id, '_event_end_date',       true );
	$location      = get_post_meta( $post_id, '_event_location',       true );
	$location_url  = get_post_meta( $post_id, '_event_location_url',   true );
	$location_logo = get_post_meta( $post_id, '_event_location_logo',  true );
	$pdf           = get_post_meta( $post_id, '_event_pdf',            true );
	$pdf_label     = get_post_meta( $post_id, '_event_pdf_label',      true );
	$cta_url       = get_post_meta( $post_id, '_event_cta_url',        true );
	$cta_text      = get_post_meta( $post_id, '_event_cta_text',       true );
	$credits = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$label = get_post_meta( $post_id, "_event_credit_label_{$i}", true );
		$value = get_post_meta( $post_id, "_event_credit_value_{$i}", true );
		if ( $value ) {
			$credits[] = array( 'label' => $label, 'value' => $value );
		}
	}
	$start_fmt     = $start ? date_i18n( 'F j, Y', strtotime( $start ) ) : '';
	$end_fmt       = ( $end && $end !== $start ) ? date_i18n( 'F j, Y', strtotime( $end ) ) : '';

	// Category eyebrow — first assigned term
	$terms    = get_the_terms( $post_id, 'simply_event_cat' );
	$category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

	if ( ! $remove_header ) :
		?>
		<div class="se-single-header">

			<div class="se-single-header__image">
				<?php the_post_thumbnail( 'large', array( 'alt' => get_the_title() ) ); ?>
				<?php if ( $credits ) : ?>
				<div class="se-single-header__credit">
					<?php foreach ( $credits as $credit ) : ?>
					<div>
						<?php if ( $credit['label'] ) : ?>
						<strong><?php echo esc_html( $credit['label'] ); ?>:</strong>
						<?php endif; ?>
						<?php echo esc_html( $credit['value'] ); ?>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>

			<div class="se-single-header__content">

				<?php if ( $category ) : ?>
				<p class="ss-eyebrow se-single-header__eyebrow"><?php echo esc_html( $category ); ?></p>
				<?php endif; ?>

				<h1 class="se-single-header__title"><?php the_title(); ?></h1>

				<?php if ( $start_fmt ) : ?>
				<p class="se-single-header__dates">
					<?php echo esc_html( $start_fmt ); ?>
					<?php if ( $end_fmt ) : ?>
						<span class="se-single-header__date-sep">&ndash;</span>
						<?php echo esc_html( $end_fmt ); ?>
					<?php endif; ?>
				</p>
				<?php endif; ?>

				<?php if ( $location ) : ?>
				<div class="se-single-header__location-wrap">
					<?php if ( $location_logo ) : ?>
					<img src="<?php echo esc_url( $location_logo ); ?>" alt="" class="se-single-header__location-logo">
					<?php endif; ?>
					<h5 class="se-single-header__location">
						<?php if ( $location_url ) : ?>
						<a href="<?php echo esc_url( $location_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $location ); ?></a>
						<?php else : ?>
						<?php echo esc_html( $location ); ?>
						<?php endif; ?>
					</h5>
				</div>
				<?php endif; ?>

				<?php if ( $pdf || ( $cta_url && $cta_text ) ) : ?>
				<div class="se-single-header__buttons">
					<?php if ( $pdf ) : ?>
					<a href="<?php echo esc_url( $pdf ); ?>" class="ss-btn se-single-header__pdf-link" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $pdf_label ? $pdf_label : __( 'Download PDF', 'simply-events' ) ); ?>
					</a>
					<?php endif; ?>
					<?php if ( $cta_url && $cta_text ) : ?>
					<a href="<?php echo esc_url( $cta_url ); ?>" class="ss-btn ss-btn-outline se-single-header__cta-link" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $cta_text ); ?>
					</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>

			</div>

		</div>
		<?php
	endif; // $remove_header
	?>

	<div class="se-single-body">
		<?php the_content(); ?>
	</div>

<?php
endif; // have_posts

get_footer();
