/**
 * Simply Events — simply-events.js
 * Version: 1.0.5
 *
 * Category filtering with FLIP animation (First, Last, Invert, Play).
 * Cards smoothly slide to their new grid positions when filtered.
 * No jQuery. Vanilla JS only.
 */

( function () {

	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		document.querySelectorAll( '.se-events-block' ).forEach( function ( block ) {

			var filterBtns = block.querySelectorAll( '.se-filter-btn' );
			var cards      = Array.prototype.slice.call( block.querySelectorAll( '.se-event-card' ) );

			if ( ! filterBtns.length ) return;

			filterBtns.forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {

					// Update active tab
					filterBtns.forEach( function ( b ) {
						b.classList.remove( 'is-active' );
						b.removeAttribute( 'aria-current' );
					} );
					btn.classList.add( 'is-active' );
					btn.setAttribute( 'aria-current', 'true' );

					var cat = btn.dataset.cat;

					// Step 1: record positions + visibility BEFORE filter
					var rects      = cards.map( function ( c ) { return c.getBoundingClientRect(); } );
					var wasVisible = cards.map( function ( c ) { return ! c.classList.contains( 'is-hidden' ); } );

					// Step 2: apply filter instantly (no transition)
					cards.forEach( function ( card ) {
						card.style.transition = 'none';
						card.style.transform  = '';
						card.style.opacity    = '';
						var cardCats = card.dataset.cats || '';
						var visible  = ( cat === 'all' || cardCats.split( ' ' ).indexOf( cat ) !== -1 );
						if ( visible ) {
							card.classList.remove( 'is-hidden' );
						} else {
							card.classList.add( 'is-hidden' );
						}
					} );

					// Step 3: animate each now-visible card
					requestAnimationFrame( function () {
						requestAnimationFrame( function () {
							cards.forEach( function ( card, i ) {
								if ( card.classList.contains( 'is-hidden' ) ) return;

								if ( ! wasVisible[ i ] ) {
									// Was hidden — simple fade in, no movement
									card.style.opacity    = '0';
									card.style.transition = 'opacity 0.3s ease';
									requestAnimationFrame( function () {
										card.style.opacity = '1';
									} );
								} else {
									// Was visible — FLIP to new position
									var newRect = card.getBoundingClientRect();
									var dx = rects[ i ].left - newRect.left;
									var dy = rects[ i ].top  - newRect.top;

									if ( Math.abs( dx ) > 0.5 || Math.abs( dy ) > 0.5 ) {
										card.style.transition = 'none';
										card.style.transform  = 'translate(' + dx + 'px, ' + dy + 'px)';
										requestAnimationFrame( function () {
											card.style.transition = 'transform 0.35s ease';
											card.style.transform  = '';
										} );
									}
								}
							} );
						} );
					} );

				} );
			} );

		} );

	} );

} )();
