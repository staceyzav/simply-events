<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ==========================================================================
// TAXONOMY — simply_event_cat
// Shared with any CPT that calls register_taxonomy_for_object_type().
// ==========================================================================

add_action( 'init', 'simply_events_register_taxonomy' );

function simply_events_register_taxonomy() {
	register_taxonomy( 'simply_event_cat', array( 'simply_event' ), array(
		'labels' => array(
			'name'              => __( 'Event Categories', 'simply-events' ),
			'singular_name'     => __( 'Event Category', 'simply-events' ),
			'search_items'      => __( 'Search Categories', 'simply-events' ),
			'all_items'         => __( 'All Categories', 'simply-events' ),
			'edit_item'         => __( 'Edit Category', 'simply-events' ),
			'update_item'       => __( 'Update Category', 'simply-events' ),
			'add_new_item'      => __( 'Add New Category', 'simply-events' ),
			'new_item_name'     => __( 'New Category Name', 'simply-events' ),
			'menu_name'         => __( 'Categories', 'simply-events' ),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'event-category' ),
		'show_in_rest'      => true,
	) );
}


// ==========================================================================
// CPT — simply_event
// ==========================================================================

add_action( 'init', 'simply_events_register_cpt' );

function simply_events_register_cpt() {
	register_post_type( 'simply_event', array(
		'labels' => array(
			'name'               => __( 'Events', 'simply-events' ),
			'singular_name'      => __( 'Event', 'simply-events' ),
			'add_new'            => __( 'Add New Event', 'simply-events' ),
			'add_new_item'       => __( 'Add New Event', 'simply-events' ),
			'edit_item'          => __( 'Edit Event', 'simply-events' ),
			'new_item'           => __( 'New Event', 'simply-events' ),
			'view_item'          => __( 'View Event', 'simply-events' ),
			'search_items'       => __( 'Search Events', 'simply-events' ),
			'not_found'          => __( 'No events found', 'simply-events' ),
			'not_found_in_trash' => __( 'No events found in trash', 'simply-events' ),
			'menu_name'          => __( 'Events', 'simply-events' ),
		),
		'public'             => true,
		'has_archive'        => 'events',
		'rewrite'            => array( 'slug' => 'events' ),
		'supports'           => array( 'title', 'editor', 'thumbnail' ),
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-calendar-alt',
		'menu_position'      => 5,
		'taxonomies'         => array( 'simply_event_cat' ),
	) );
}


// ==========================================================================
// META BOX — Event Details
// Fields: start date, end date, location, location URL, location logo, PDF,
//         CTA URL, CTA text.
// Featured image handles the photo (registered via 'thumbnail' support above).
// ==========================================================================

add_action( 'add_meta_boxes', 'simply_events_add_meta_box' );

function simply_events_add_meta_box() {
	add_meta_box(
		'simply_event_details',
		__( 'Event Info', 'simply-events' ),
		'simply_events_meta_box_cb',
		'simply_event',
		'normal',
		'high'
	);
}

function simply_events_meta_box_cb( $post ) {
	wp_nonce_field( 'simply_events_save_meta', 'simply_events_nonce' );

	$start        = get_post_meta( $post->ID, '_event_start_date',    true );
	$end          = get_post_meta( $post->ID, '_event_end_date',      true );
	$location     = get_post_meta( $post->ID, '_event_location',      true );
	$location_url = get_post_meta( $post->ID, '_event_location_url',  true );
	$location_logo= get_post_meta( $post->ID, '_event_location_logo', true );
	$pdf          = get_post_meta( $post->ID, '_event_pdf',           true );
	$pdf_label    = get_post_meta( $post->ID, '_event_pdf_label',     true );
	$cta_url      = get_post_meta( $post->ID, '_event_cta_url',       true );
	$cta_text     = get_post_meta( $post->ID, '_event_cta_text',      true );
	$athlete      = get_post_meta( $post->ID, '_event_athlete',       true );
	$photographer = get_post_meta( $post->ID, '_event_photographer',  true );
	$competition  = get_post_meta( $post->ID, '_event_competition',   true );
	?>
	<style>
		.se-meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
		.se-meta-grid .se-meta-full { grid-column: 1 / -1; }
		.se-meta-field label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; }
		.se-meta-field input[type="date"],
		.se-meta-field input[type="text"],
		.se-meta-field input[type="url"] { width: 100%; padding: 6px 8px; border: 1px solid #ddd; border-radius: 3px; font-size: 13px; }
		.se-meta-upload-row { display: flex; gap: 8px; align-items: center; }
		.se-meta-upload-row input { flex: 1; }
		.se-meta-note { font-weight: 400; color: #888; font-size: 11px; display: block; margin-top: 2px; }
		.se-meta-divider { grid-column: 1 / -1; border: none; border-top: 1px solid #eee; margin: 4px 0; }
	</style>
	<div class="se-meta-grid">

		<div class="se-meta-field">
			<label for="event_start_date"><?php esc_html_e( 'Start Date', 'simply-events' ); ?> <span style="color:red">*</span></label>
			<input type="date" id="event_start_date" name="event_start_date" value="<?php echo esc_attr( $start ); ?>">
		</div>
		<div class="se-meta-field">
			<label for="event_end_date"><?php esc_html_e( 'End Date', 'simply-events' ); ?> <em style="font-weight:400;color:#888">(optional)</em></label>
			<input type="date" id="event_end_date" name="event_end_date" value="<?php echo esc_attr( $end ); ?>">
		</div>

		<hr class="se-meta-divider">

		<div class="se-meta-field se-meta-full">
			<label for="event_location"><?php esc_html_e( 'Location', 'simply-events' ); ?></label>
			<input type="text" id="event_location" name="event_location" value="<?php echo esc_attr( $location ); ?>" placeholder="e.g. Snowbird Resort">
		</div>
		<div class="se-meta-field">
			<label for="event_location_url"><?php esc_html_e( 'Location URL', 'simply-events' ); ?></label>
			<span class="se-meta-note"><?php esc_html_e( 'Map or website', 'simply-events' ); ?></span>
			<input type="url" id="event_location_url" name="event_location_url" value="<?php echo esc_attr( $location_url ); ?>" placeholder="https://...">
		</div>
		<div class="se-meta-field">
			<label><?php esc_html_e( 'Location Logo', 'simply-events' ); ?></label>
			<span class="se-meta-note"><?php esc_html_e( 'Optional image', 'simply-events' ); ?></span>
			<div class="se-meta-upload-row">
				<input type="url" id="event_location_logo" name="event_location_logo" value="<?php echo esc_attr( $location_logo ); ?>" placeholder="https://...">
				<button type="button" class="button" id="se-logo-upload"><?php esc_html_e( 'Choose', 'simply-events' ); ?></button>
			</div>
		</div>

		<hr class="se-meta-divider">

		<div class="se-meta-field">
			<label for="event_pdf"><?php esc_html_e( 'PDF', 'simply-events' ); ?> <em style="font-weight:400;color:#888">(optional)</em></label>
			<div class="se-meta-upload-row">
				<input type="url" id="event_pdf" name="event_pdf" value="<?php echo esc_attr( $pdf ); ?>" placeholder="https://...">
				<button type="button" class="button" id="se-pdf-upload"><?php esc_html_e( 'Choose File', 'simply-events' ); ?></button>
			</div>
		</div>
		<div class="se-meta-field">
			<label for="event_pdf_label"><?php esc_html_e( 'PDF Label', 'simply-events' ); ?></label>
			<span class="se-meta-note"><?php esc_html_e( 'Button text', 'simply-events' ); ?></span>
			<input type="text" id="event_pdf_label" name="event_pdf_label" value="<?php echo esc_attr( $pdf_label ); ?>" placeholder="e.g. Download Schedule">
		</div>
		<div class="se-meta-field">
			<label for="event_cta_text"><?php esc_html_e( 'CTA Text', 'simply-events' ); ?></label>
			<span class="se-meta-note"><?php esc_html_e( 'Button label', 'simply-events' ); ?></span>
			<input type="text" id="event_cta_text" name="event_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" placeholder="e.g. Register Now">
		</div>
		<div class="se-meta-field">
			<label for="event_cta_url"><?php esc_html_e( 'CTA URL', 'simply-events' ); ?></label>
			<span class="se-meta-note"><?php esc_html_e( 'Link to PDF or website', 'simply-events' ); ?></span>
			<input type="url" id="event_cta_url" name="event_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" placeholder="https://...">
		</div>

		<hr class="se-meta-divider">
		<div class="se-meta-full" style="font-weight:600;font-size:13px;margin-bottom:4px;"><?php esc_html_e( 'Photo Credits', 'simply-events' ); ?> <em style="font-weight:400;color:#888"><?php esc_html_e( '(optional — shown on single event page)', 'simply-events' ); ?></em></div>

		<div class="se-meta-field">
			<label for="event_athlete"><?php esc_html_e( 'Athlete', 'simply-events' ); ?></label>
			<input type="text" id="event_athlete" name="event_athlete" value="<?php echo esc_attr( $athlete ); ?>" placeholder="e.g. Jaxson Smith">
		</div>
		<div class="se-meta-field">
			<label for="event_photographer"><?php esc_html_e( 'Photographer', 'simply-events' ); ?></label>
			<input type="text" id="event_photographer" name="event_photographer" value="<?php echo esc_attr( $photographer ); ?>" placeholder="e.g. Stacey Zav">
		</div>
		<div class="se-meta-field se-meta-full">
			<label for="event_competition"><?php esc_html_e( 'Competition', 'simply-events' ); ?></label>
			<input type="text" id="event_competition" name="event_competition" value="<?php echo esc_attr( $competition ); ?>" placeholder="e.g. IMF Snowbird 2026">
		</div>

	</div>
	<script>
	jQuery(function($){
		$('#se-pdf-upload').on('click', function(e){
			e.preventDefault();
			var frame = wp.media({ title: 'Select PDF', button: { text: 'Use this file' }, multiple: false });
			frame.on('select', function(){
				$('#event_pdf').val( frame.state().get('selection').first().toJSON().url );
			});
			frame.open();
		});
		$('#se-logo-upload').on('click', function(e){
			e.preventDefault();
			var frame = wp.media({ title: 'Select Location Logo', button: { text: 'Use this image' }, multiple: false, library: { type: 'image' } });
			frame.on('select', function(){
				$('#event_location_logo').val( frame.state().get('selection').first().toJSON().url );
			});
			frame.open();
		});
	});
	</script>
	<?php
}

add_action( 'save_post_simply_event', 'simply_events_save_meta' );

function simply_events_save_meta( $post_id ) {
	if (
		! isset( $_POST['simply_events_nonce'] ) ||
		! wp_verify_nonce( $_POST['simply_events_nonce'], 'simply_events_save_meta' ) ||
		defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$fields = array(
		'event_start_date'    => array( 'key' => '_event_start_date',    'fn' => 'sanitize_text_field' ),
		'event_end_date'      => array( 'key' => '_event_end_date',      'fn' => 'sanitize_text_field' ),
		'event_location'      => array( 'key' => '_event_location',      'fn' => 'sanitize_text_field' ),
		'event_location_url'  => array( 'key' => '_event_location_url',  'fn' => 'esc_url_raw' ),
		'event_location_logo' => array( 'key' => '_event_location_logo', 'fn' => 'esc_url_raw' ),
		'event_pdf'           => array( 'key' => '_event_pdf',           'fn' => 'esc_url_raw' ),
		'event_pdf_label'     => array( 'key' => '_event_pdf_label',     'fn' => 'sanitize_text_field' ),
		'event_cta_url'       => array( 'key' => '_event_cta_url',       'fn' => 'esc_url_raw' ),
		'event_cta_text'      => array( 'key' => '_event_cta_text',      'fn' => 'sanitize_text_field' ),
		'event_athlete'       => array( 'key' => '_event_athlete',       'fn' => 'sanitize_text_field' ),
		'event_photographer'  => array( 'key' => '_event_photographer',  'fn' => 'sanitize_text_field' ),
		'event_competition'   => array( 'key' => '_event_competition',   'fn' => 'sanitize_text_field' ),
	);

	foreach ( $fields as $post_key => $field ) {
		if ( isset( $_POST[ $post_key ] ) ) {
			update_post_meta( $post_id, $field['key'], call_user_func( $field['fn'], $_POST[ $post_key ] ) );
		}
	}
}


// ==========================================================================
// META BOX — Event Header toggle (sidebar)
// Checkbox: remove the auto-generated header (field data + image).
// When checked, the single-event template skips the header so the user
// can build the page entirely from post content / blocks.
// ==========================================================================

add_action( 'add_meta_boxes', 'simply_events_add_header_box' );

function simply_events_add_header_box() {
	add_meta_box(
		'simply_event_header_toggle',
		__( 'Event Header', 'simply-events' ),
		'simply_events_header_box_cb',
		'simply_event',
		'side',
		'default'
	);
}

function simply_events_header_box_cb( $post ) {
	wp_nonce_field( 'simply_events_header_toggle', 'simply_events_header_nonce' );
	$remove = get_post_meta( $post->ID, '_event_remove_header', true );
	?>
	<label style="display:flex;gap:8px;align-items:flex-start;cursor:pointer;line-height:1.4;">
		<input type="checkbox" name="event_remove_header" value="1"
			<?php checked( $remove, '1' ); ?>
			style="margin-top:3px;flex-shrink:0;">
		<span>
			<strong><?php esc_html_e( 'Remove event header', 'simply-events' ); ?></strong><br>
			<span style="font-size:11px;color:#666;">
				<?php esc_html_e( 'This contains field data and image. Remove to build the page from scratch.', 'simply-events' ); ?>
			</span>
		</span>
	</label>
	<?php
}

add_action( 'save_post_simply_event', 'simply_events_save_header_toggle', 10, 1 );

function simply_events_save_header_toggle( $post_id ) {
	if (
		! isset( $_POST['simply_events_header_nonce'] ) ||
		! wp_verify_nonce( $_POST['simply_events_header_nonce'], 'simply_events_header_toggle' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	$remove = isset( $_POST['event_remove_header'] ) ? '1' : '';
	update_post_meta( $post_id, '_event_remove_header', $remove );
}


// ==========================================================================
// ADMIN COLUMNS — show start date and location in the events list table
// ==========================================================================

add_filter( 'manage_simply_event_posts_columns', 'simply_events_admin_columns' );

function simply_events_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( $key === 'title' ) {
			$new['event_start_date'] = __( 'Date', 'simply-events' );
			$new['event_location']   = __( 'Location', 'simply-events' );
		}
	}
	return $new;
}

add_action( 'manage_simply_event_posts_custom_column', 'simply_events_admin_column_content', 10, 2 );

function simply_events_admin_column_content( $column, $post_id ) {
	if ( $column === 'event_start_date' ) {
		$start = get_post_meta( $post_id, '_event_start_date', true );
		$end   = get_post_meta( $post_id, '_event_end_date', true );
		if ( $start ) {
			echo esc_html( date( 'M j, Y', strtotime( $start ) ) );
			if ( $end && $end !== $start ) {
				echo ' &ndash; ' . esc_html( date( 'M j, Y', strtotime( $end ) ) );
			}
		}
	}
	if ( $column === 'event_location' ) {
		echo esc_html( get_post_meta( $post_id, '_event_location', true ) );
	}
}

add_filter( 'manage_edit-simply_event_sortable_columns', 'simply_events_sortable_columns' );

function simply_events_sortable_columns( $columns ) {
	$columns['event_start_date'] = 'event_start_date';
	return $columns;
}

add_action( 'pre_get_posts', 'simply_events_sort_by_date' );

function simply_events_sort_by_date( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) return;
	if ( $query->get( 'orderby' ) === 'event_start_date' ) {
		$query->set( 'meta_key', '_event_start_date' );
		$query->set( 'orderby', 'meta_value' );
	}
}


// ==========================================================================
// ADMIN — rename "Meta boxes" panel label to "Event Info" in block editor
// The wrapper label is hardcoded in WP core JS — we swap it via MutationObserver
// scoped only to the simply_event edit screen.
// ==========================================================================

add_action( 'enqueue_block_editor_assets', 'simply_events_rename_metabox_panel' );

function simply_events_rename_metabox_panel() {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'simply_event' ) return;

	wp_add_inline_script( 'wp-edit-post', "
		( function() {
			function relabelMetaBoxPanel() {
				document.querySelectorAll( '.edit-post-meta-boxes-panel h2, .components-panel__body-title' ).forEach( function( el ) {
					if ( el.textContent.trim() === 'Meta boxes' ) {
						el.textContent = 'Event Info';
					}
					var btn = el.querySelector( 'button' );
					if ( btn && btn.textContent.trim() === 'Meta boxes' ) {
						btn.childNodes.forEach( function( node ) {
							if ( node.nodeType === 3 && node.textContent.trim() === 'Meta boxes' ) {
								node.textContent = 'Event Info';
							}
						} );
						var span = btn.querySelector( 'span:not(.components-panel__arrow-icon)' );
						if ( span && span.textContent.trim() === 'Meta boxes' ) {
							span.textContent = 'Event Info';
						}
					}
				} );
			}
			var observer = new MutationObserver( relabelMetaBoxPanel );
			observer.observe( document.body, { childList: true, subtree: true } );
			setTimeout( relabelMetaBoxPanel, 1000 );
			setTimeout( relabelMetaBoxPanel, 3000 );
		} )();
	" );
}


// ==========================================================================
// FLUSH REWRITE RULES on activation
// ==========================================================================

register_activation_hook( SIMPLY_EVENTS_PATH . '../simply-events.php', 'simply_events_activate' );

function simply_events_activate() {
	simply_events_register_cpt();
	simply_events_register_taxonomy();
	flush_rewrite_rules();
}
