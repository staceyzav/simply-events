# Simply Events — Changelog
https://simplydesign.com/simply-events

## [1.0.2] — 2026-06-02

### Fixed
- Date text hardcoded white (#fff) — token fallback wasn't overriding theme black
- Filter tabs: plain black text, active = underline only, no pill styling
- CTA pill: outlined in --client-nav-bg (dark brand color), dark text, white fill on hover
- Admin: MutationObserver renames "Meta boxes" to "Event Info" on event edit screen

---

## [1.0.1] — 2026-06-02

### Fixed
- Date text now explicitly white (accent-text color on all date elements)
- Start month bumped to 28px; end date section stays small (12px month)
- Date block alignment changed to flex-start — end date top-aligns with start
- Card title: --se-font-primary, 24px, no text-transform, black (#000)
- Title always links to event permalink (not PDF); stretched link covers full card

---

## [1.0.0] — 2026-06-02

### Added
- Initial release
- CPT: simply_event (Events) with featured image support
- Taxonomy: simply_event_cat (Event Categories) — shared taxonomy,
  other CPTs can attach via register_taxonomy_for_object_type()
- Meta fields: start date, end date, location, PDF (media upload button)
- [simply_events] shortcode — upcoming events feed ordered by start date
  Attributes: title, limit, show_filter, cta_text, cta_url, category
- 5-col grid → 4 → 3 → 2 on mobile
- DOM-based category filter tabs (no AJAX, no jQuery)
- Neutral wireframe CSS using --client-* token system
  Override via client config for branded colors
- Admin list columns: date, location; sortable by date
- Flush rewrite rules on activation
