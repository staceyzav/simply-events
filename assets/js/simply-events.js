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

	// =========================================================================
	// Calendar week strip
	// =========================================================================

	var DAY_NAMES   = [ 'SUN', 'MON', 'TUES', 'WED', 'THUR', 'FRI', 'SAT' ];
	var MONTH_NAMES = [ 'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
	                    'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER' ];

	function seCalInit( cal ) {
		var events = JSON.parse( cal.dataset.events || '[]' );

		// Build date → [{title, url}] lookup
		var lookup = {};
		events.forEach( function ( e ) {
			if ( ! lookup[ e.date ] ) lookup[ e.date ] = [];
			lookup[ e.date ].push( { title: e.title, full_title: e.full_title, url: e.url, photo: e.photo } );
		} );

		// Anchor everything to the Sunday of the current week
		var today = new Date();
		today.setHours( 0, 0, 0, 0 );
		var weekStart = new Date( today );
		weekStart.setDate( today.getDate() - today.getDay() );

		var offset       = 0; // days from weekStart, always a multiple of 7
		var direction    = 0; // -1 prev, 1 next, 0 initial
		var displayMonth = today.getMonth();
		var displayYear  = today.getFullYear();

		// Returns the date that is n days after weekStart
		function dayFromOffset( n ) {
			var d = new Date( weekStart );
			d.setDate( weekStart.getDate() + n );
			return d;
		}

		// Build DOM skeleton
		var header = document.createElement( 'div' );
		header.className = 'se-cal__header';

		// Header: « NOVEMBER 2026 »
		var prevMonthBtn = document.createElement( 'button' );
		prevMonthBtn.className = 'se-cal__nav se-cal__nav--prev-month';
		prevMonthBtn.setAttribute( 'aria-label', 'Previous month' );
		prevMonthBtn.innerHTML = '&#171;';

		var monthLabel = document.createElement( 'span' );
		monthLabel.className = 'se-cal__month-label';

		var nextMonthBtn = document.createElement( 'button' );
		nextMonthBtn.className = 'se-cal__nav se-cal__nav--next-month';
		nextMonthBtn.setAttribute( 'aria-label', 'Next month' );
		nextMonthBtn.innerHTML = '&#187;';

		var monthNav = document.createElement( 'div' );
		monthNav.className = 'se-cal__month-nav';
		monthNav.appendChild( prevMonthBtn );
		monthNav.appendChild( monthLabel );
		monthNav.appendChild( nextMonthBtn );
		header.appendChild( monthNav );

		// Subscribe button + dropdown
		var icalUrl  = cal.dataset.icalUrl || '';
		if ( icalUrl ) {
			var webcalUrl  = icalUrl.replace( /^https?:\/\//, 'webcal://' );
			var googleUrl  = 'https://calendar.google.com/calendar/r?cid=' + encodeURIComponent( webcalUrl );
			var outlookUrl = 'https://outlook.live.com/calendar/0/addcalendar?url=' + encodeURIComponent( webcalUrl );

			var subscribeWrap = document.createElement( 'div' );
			subscribeWrap.className = 'se-cal__subscribe';

			var subscribeBtn = document.createElement( 'button' );
			subscribeBtn.className = 'se-cal__subscribe-btn';
			subscribeBtn.setAttribute( 'aria-expanded', 'false' );
			subscribeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> Subscribe';

			var dropdown = document.createElement( 'div' );
			dropdown.className = 'se-cal__dropdown';
			dropdown.setAttribute( 'role', 'menu' );

			var items = [
				{ label: 'Apple Calendar',  href: webcalUrl,  note: 'Opens in default app' },
				{ label: 'Google Calendar', href: googleUrl,  note: 'Opens in browser' },
				{ label: 'Outlook.com',     href: outlookUrl, note: 'Opens in browser' },
				{ label: 'Download .ics',   href: icalUrl,    note: 'Manual import',  download: true },
			];

			items.forEach( function ( item ) {
				var a = document.createElement( 'a' );
				a.href      = item.href;
				a.className = 'se-cal__dropdown-item';
				a.setAttribute( 'role', 'menuitem' );
				if ( item.download ) {
					a.setAttribute( 'download', 'events.ics' );
				} else {
					a.target = '_blank';
					a.rel    = 'noopener';
				}
				a.innerHTML = '<span class="se-cal__dropdown-label">' + item.label + '</span>' +
				              '<span class="se-cal__dropdown-note">'  + item.note  + '</span>';
				dropdown.appendChild( a );
			} );

			subscribeWrap.appendChild( subscribeBtn );
			subscribeWrap.appendChild( dropdown );
			header.appendChild( subscribeWrap );

			// Toggle dropdown
			subscribeBtn.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				var open = dropdown.classList.toggle( 'is-open' );
				subscribeBtn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );

			// Close on outside click
			document.addEventListener( 'click', function () {
				dropdown.classList.remove( 'is-open' );
				subscribeBtn.setAttribute( 'aria-expanded', 'false' );
			} );
		}

		cal.appendChild( header );

		// Strip: ‹ [8 days] ›
		var strip = document.createElement( 'div' );
		strip.className = 'se-cal__strip';

		var prevBtn = document.createElement( 'button' );
		prevBtn.className = 'se-cal__nav se-cal__nav--prev se-cal__strip-nav';
		prevBtn.setAttribute( 'aria-label', 'Previous week' );
		prevBtn.innerHTML = '&#8249;';

		var nextBtn = document.createElement( 'button' );
		nextBtn.className = 'se-cal__nav se-cal__nav--next se-cal__strip-nav';
		nextBtn.setAttribute( 'aria-label', 'Next week' );
		nextBtn.innerHTML = '&#8250;';

		strip.appendChild( prevBtn );
		// Day columns inserted by render()
		strip.appendChild( nextBtn );
		cal.appendChild( strip );

		function formatDate( d ) {
			var m = String( d.getMonth() + 1 ).padStart( 2, '0' );
			var day = String( d.getDate() ).padStart( 2, '0' );
			return d.getFullYear() + '-' + m + '-' + day;
		}

		function truncate( str, max ) {
			return str.length <= max ? str : str.slice( 0, max ).trimEnd() + '…';
		}

		// Returns the offset (days from weekStart) for the Sunday of the week
		// containing the 1st of the target month. Never goes before weekStart.
		function offsetForMonthStart( year, month ) {
			var first  = new Date( year, month, 1 );
			var sunday = new Date( first );
			sunday.setDate( first.getDate() - first.getDay() );
			var diff = Math.round( ( sunday - weekStart ) / 86400000 );
			return Math.max( 0, diff );
		}

		function render() {
			prevBtn.disabled      = offset === 0;
			prevMonthBtn.disabled = offset === 0;

			// Direction class for slide animation
			strip.classList.remove( 'se-going-next', 'se-going-prev' );
			if ( direction > 0 ) strip.classList.add( 'se-going-next' );
			if ( direction < 0 ) strip.classList.add( 'se-going-prev' );

			// Remove existing day columns (keep prevBtn and nextBtn)
			strip.querySelectorAll( '.se-cal__day' ).forEach( function ( el ) { el.remove(); } );

			for ( var i = 0; i < 7; i++ ) {
				var d       = dayFromOffset( offset + i );
				var dateStr = formatDate( d );
				var isToday      = d.getTime() === today.getTime();
				var isPast       = d < today;
				var isOtherMonth = d.getMonth() !== displayMonth;

				var col = document.createElement( 'div' );
				col.className = 'se-cal__day' +
					( isToday      ? ' se-cal__day--today'       : '' ) +
					( isPast       ? ' se-cal__day--past'        : '' ) +
					( isOtherMonth ? ' se-cal__day--other-month' : '' );

				var nameEl = document.createElement( 'div' );
				nameEl.className   = 'se-cal__day-name';
				nameEl.textContent = DAY_NAMES[ d.getDay() ];

				var numEl = document.createElement( 'div' );
				numEl.className   = 'se-cal__day-num';
				numEl.textContent = d.getDate();

				var eventsEl = document.createElement( 'div' );
				eventsEl.className = 'se-cal__day-events';

				if ( lookup[ dateStr ] ) {
					lookup[ dateStr ].forEach( function ( ev ) {
						var pill = document.createElement( 'a' );
						pill.className = 'se-cal__pill' + ( isOtherMonth ? ' se-cal__pill--other-month' : '' );
						pill.href      = ev.url;
						pill.textContent = truncate( ev.title, 18 );

						pill.addEventListener( 'mouseenter', function ( e ) {
							var t = seTooltip();
							t.innerHTML = '';

							if ( ev.photo ) {
								var img = document.createElement( 'img' );
								img.src       = ev.photo;
								img.className = 'se-cal__tooltip-photo';
								img.alt       = '';
								t.appendChild( img );
							}

							var titleEl = document.createElement( 'span' );
							titleEl.className   = 'se-cal__tooltip-title';
							titleEl.textContent = ev.full_title || ev.title;
							t.appendChild( titleEl );

							t.style.display = 'flex';
							sePositionTooltip( e );
						} );
						pill.addEventListener( 'mousemove',  sePositionTooltip );
						pill.addEventListener( 'mouseleave', function () {
							seTooltip().style.display = 'none';
						} );

						eventsEl.appendChild( pill );
					} );
				}

				col.style.animationDelay = ( i * 0.025 ) + 's';
				col.appendChild( nameEl );
				col.appendChild( numEl );
				col.appendChild( eventsEl );
				// Insert day columns before the nextBtn
				strip.insertBefore( col, nextBtn );
			}

			monthLabel.textContent = MONTH_NAMES[ displayMonth ] + ' ' + displayYear;
		}

		prevMonthBtn.addEventListener( 'click', function () {
			var m = displayMonth - 1, y = displayYear;
			if ( m < 0 ) { m = 11; y--; }
			direction      = -1;
			displayMonth   = m;
			displayYear    = y;
			offset         = offsetForMonthStart( y, m );
			render();
		} );

		prevBtn.addEventListener( 'click', function () {
			direction = -1;
			offset    = Math.max( 0, offset - 7 );
			var d     = dayFromOffset( offset + 3 ); // mid-week day avoids month boundary flicker
			displayMonth = d.getMonth();
			displayYear  = d.getFullYear();
			render();
		} );

		nextBtn.addEventListener( 'click', function () {
			direction = 1;
			offset   += 7;
			var d     = dayFromOffset( offset + 3 );
			displayMonth = d.getMonth();
			displayYear  = d.getFullYear();
			render();
		} );

		nextMonthBtn.addEventListener( 'click', function () {
			var m = displayMonth + 1, y = displayYear;
			if ( m > 11 ) { m = 0; y++; }
			direction    = 1;
			displayMonth = m;
			displayYear  = y;
			offset       = offsetForMonthStart( y, m );
			render();
		} );

		render();
	}

	// Shared floating tooltip for pill hover — created once, reused
	var seTooltipEl = null;

	function seTooltip() {
		if ( ! seTooltipEl ) {
			seTooltipEl = document.createElement( 'div' );
			seTooltipEl.className = 'se-cal__tooltip';
			document.body.appendChild( seTooltipEl );
		}
		return seTooltipEl;
	}

	function sePositionTooltip( e ) {
		var t   = seTooltip();
		var gap = 14;
		var tw  = t.offsetWidth  || 180;
		var th  = t.offsetHeight || 28;
		var vw  = window.innerWidth;
		var vh  = window.innerHeight;

		var left = ( e.clientX + gap + tw > vw )
			? e.clientX - tw - gap        // flip left
			: e.clientX + gap;

		var top = e.clientY - th / 2;
		if ( top < 6 )            top = 6;
		if ( top + th > vh - 6 )  top = vh - th - 6;

		t.style.left = left + 'px';
		t.style.top  = top  + 'px';
	}

	// =========================================================================
	// Category filtering
	// =========================================================================

	document.addEventListener( 'DOMContentLoaded', function () {

		document.querySelectorAll( '.se-cal' ).forEach( seCalInit );

		document.querySelectorAll( '.se-events-block' ).forEach( function ( block ) {

			var filterBtns = block.querySelectorAll( '.se-filter-btn' );
			var cards      = Array.prototype.slice.call( block.querySelectorAll( '.se-event-card' ) );
			var limit      = parseInt( block.dataset.limit, 10 ) || 0;

			// Apply limit on initial load so only the first N cards are visible.
			if ( limit ) {
				cards.forEach( function ( card, i ) {
					if ( i >= limit ) card.classList.add( 'is-hidden' );
				} );
			}

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

					// Step 2: apply filter + limit instantly (no transition)
					// All events are rendered; we show only the first `limit` that match.
					var visibleCount = 0;
					cards.forEach( function ( card ) {
						card.style.transition = 'none';
						card.style.transform  = '';
						card.style.opacity    = '';
						var cardCats   = card.dataset.cats || '';
						var matchesCat = ( cat === 'all' || cardCats.split( ' ' ).indexOf( cat ) !== -1 );
						var withinLimit = ( ! limit || visibleCount < limit );
						if ( matchesCat && withinLimit ) {
							card.classList.remove( 'is-hidden' );
							visibleCount++;
						} else {
							card.classList.add( 'is-hidden' );
						}
					} );

					// Step 3: FLIP-animate cards that were already visible and moved.
					// Newly-revealed cards (wasVisible=false) just appear — no fade
					// needed, and opacity manipulation on display:none→flex is unreliable.
					requestAnimationFrame( function () {
						requestAnimationFrame( function () {
							cards.forEach( function ( card, i ) {
								if ( card.classList.contains( 'is-hidden' ) ) return;
								if ( ! wasVisible[ i ] ) return;

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
							} );
						} );
					} );

				} );
			} );

		} );

	} );

} )();
