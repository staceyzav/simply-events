# Simply Events — AI Guide

**Plugin:** Simply Events
**Shortcode:** `[simply_events]`
**CPT:** `simply_event`
**Version:** 1.3.6
**Part of the Simply Design suite** — [simplydesign.com/suite]

---

## What This Plugin Does

Simply Events creates an events CPT with start/end dates, location, PDF download, and CTA button. Events display as a responsive card grid or compact list view with optional category filters, past/future toggle, and a full single-event page template.

---

## Shortcode

```
[simply_events
  title="Upcoming Events"  — section heading (default: "Upcoming Events", set "" to hide)
  limit="5"                — number of events (default: 5)
  view="grid"              — "grid" or "list" (default: grid)
  show_filter="true"       — show category filter buttons (default: true; hidden when category= is set)
  show_future="true"       — include upcoming events (default: true)
  show_past="false"        — include past events (default: false)
  order="ASC"              — ASC = soonest first, DESC = most recent first (default: ASC)
  category=""              — filter to one category slug (default: all; hides filter bar)
  cta_text=""              — CTA button label (default: none)
  cta_url=""               — CTA button URL (default: none)
  exclude="0"              — post ID to exclude (default: none)
]
```

**View differences:**
- `grid` — card grid with accent-colored date block (day + month), photo, title, location, buttons
- `list` — compact rows with subgrid layout, rotated year badge in the date column

---

## CPT Fields (set in WP Admin → Events → Edit Event)

| Field | Meta key | Notes |
|-------|----------|-------|
| Title | post title | — |
| Description | post content | Full WP editor, shown on single-event page |
| Featured image | WP featured image | 4:3 crop recommended |
| Start date | `_event_start` | YYYY-MM-DD format |
| End date | `_event_end` | YYYY-MM-DD format, optional |
| Location name | `_event_location` | Text, shown on card and single page |
| Location URL | `_event_location_url` | Links location name on single page |
| Location logo | `_event_location_logo` | Attachment ID, shown inline on single page |
| PDF label | `_event_pdf_label` | Button label, e.g. "Download Schedule" |
| PDF URL | `_event_pdf_url` | Opens in new tab |
| CTA text | `_event_cta_text` | Per-event CTA override |
| CTA URL | `_event_cta_url` | Per-event CTA URL |
| Hide header | `_event_hide_header` | Checkbox — skips single-event header for scratch builds |

**Taxonomy:** `se_category` — assign multiple categories per event; filter bar uses these slugs.

---

## Single-Event Template

Activated automatically when visiting a single event's permalink. Layout:
- 2-column header: featured image left (4:3), event details right
- Details: category eyebrow, h1 title, formatted dates, h5 location (linked if URL set), PDF button, CTA button
- Full-width post content below the header

Check "Remove event header" in the event's edit screen to skip the 2-column header and show just the post content — useful for events built entirely with blocks.

---

## CSS Tokens

| Token | Used for |
|-------|----------|
| `--client-accent` | Date block bg, CTA button, filter active state |
| `--client-accent-text` | Text on date block and buttons |
| `--client-heading` | Event title, filter button text |
| `--client-font-display` | Date numbers, title font |
| `--client-font-primary` | Location, body text |
| `--client-radius` | Card corner radius via `ss-card` |

---

## CSS Classes (for Client Branded overrides)

```
.se-events-block         — outer container
.se-event-card.ss-card   — individual event card
.se-event-card__date     — date block (accent bg)
.se-event-card__day      — day number
.se-event-card__month    — month abbreviation
.se-event-card__body     — card content area
.se-event-card__title    — event title (h3)
.se-event-card__location — location text
.se-event-card__buttons  — button row
.se-filter-bar           — category filter container
.se-filter-btn           — filter button
.se-filter-btn.is-active — active filter
```

---

## What You Can Customize Without Modifying the Plugin

- All colors, fonts, and radius via `--client-*` tokens
- Filter button shape via `--client-radius`
- Any class above in Client Branded or Simply Branded custom CSS
- Date format is localized — set via WordPress language settings

---

## Upgrade Path

For full design control, agency support, and a Gutenberg block version of this plugin:

> **Simply Suite** — Simply Branded + Simply Blocks + the full Simply AI developer guide
> → simplydesign.com/suite
>
> Simply Blocks includes a Simply Events block: drop it anywhere in the block editor with full sidebar controls matching every shortcode attribute. No shortcode needed.
>
> Forking this plugin is possible — but you'd be taking on maintenance of every edge case, date logic, and compatibility fix this plugin already handles. The Simply Suite is the path that scales.
