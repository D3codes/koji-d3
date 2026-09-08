# Koji D3

The custom WordPress child theme used by [d3.codes](https://d3.codes/). It
extends Koji with site-specific styles, templates, and infinite-scroll
behavior while keeping the upstream theme as the parent.

## Origin and attribution

Koji D3 is built on [Koji](https://github.com/andersnoren/koji), a WordPress
theme created by [Anders Norén](https://andersnoren.se/). Koji is also
available from the [WordPress.org theme directory](https://wordpress.org/themes/koji/).

This repository contains only the d3.codes child-theme customizations. Koji
must be installed separately for the theme to work.

## What this child theme changes

- Site-specific layout, typography, icons, and responsive styles.
- Custom footer, search form, pagination, and 404 templates.
- Infinite scrolling that waits for genuine scroll intent, including in browser
  windows taller than the initial page.
- A stable homepage URL while additional posts are appended; direct paginated
  archive URLs retain Koji's standard behavior.

## Repository structure

```text
theme/                         WordPress child theme
  assets/js/                   Front-end behavior
  functions.php                Styles and scripts registered with WordPress
  style.css                    Theme metadata and custom styles
.github/workflows/
  pr-preview.yml               WordPress Playground previews for pull requests
  deploy-production.yml        Production deployment over FTPS
```

## Local installation

1. Install the [Koji parent theme](https://wordpress.org/themes/koji/).
2. Copy the `theme` directory to `wp-content/themes/koji-d3` in a WordPress
   installation.
3. Activate **Koji D3** from **Appearance → Themes**.
4. Set Koji's pagination type to infinite scroll if you want to exercise the
   custom pagination behavior.

The child theme uses plain PHP, CSS, and JavaScript and has no separate build
step.
## Links and feed tabs

Go to **Links → Add New Link**, enter a title and **External URL**, optionally
write commentary, and publish. Open Graph title, description, site name and
image URL are cached when the URL changes. Use **Refresh cached preview on
save** to retry. A featured image takes priority. Failed refreshes keep the
existing cache; changing the destination clears metadata from the old URL.
Images remain hosted by the source site; missing metadata renders a text card.
Sites that block requests or require JavaScript may need a manual featured image.

After deployment, save **Settings → Permalinks** once to register the new routes.
No rewrite flush runs during normal requests. This assumes `/links/` and `/all/`
are unused; resolve any existing page or plugin route conflicts before deployment.
With **Settings → Reading → Your latest posts**, `/` remains Posts, `/links/`
is Links, and `/all/` mixes both by publication date. A configured static home
page is preserved, with tabs on the configured posts page instead. Plain
permalinks use WordPress query-string fallbacks.

Posts retain Koji's existing previews and infinite scrolling. Links and All use
numbered pagination because Koji's AJAX handler rejects mixed post types. The
primary RSS feed includes both types, including commentary and the external
preview in full-content and summary feeds. Existing search results exclude Links;
explicit Link REST requests remain available. Existing posts are never converted.

### Files added

- `theme/inc/links.php`: content type, URL editor/meta, safe cached preview fetching,
  scoped queries, routing, and RSS rendering.
- `theme/home.php`: shared feed loop using Koji's existing preview dispatch.
- `theme/archive-link.php`: Links archive using the shared feed layout.
- `theme/preview-link.php`: distinct Link card within Koji's grid.
- `theme/single-link.php`: local commentary page without external redirection.
- `theme/template-parts/feed-tabs.php`: accessible navigable Posts / Links / All tabs.
- `theme/template-parts/content-link.php`: shared commentary and external preview.
- `theme/assets/js/link-previews.js`: hides remote images that fail to load.
- `tests/links.php`: disposable WordPress integration assertions.
- `tests/run.php`: integration runner and syntax parsing for all child-theme PHP.

### Files modified

- `theme/functions.php`: loads the feature module and image fallback script.
- `theme/style.css`: scoped tabs, cards, images, commentary and pagination styles.
- `README.md`: workflow, deployment requirements, assumptions and validation.

### Validation

Mount `theme` at `/wordpress/wp-content/themes/koji-d3`, the Koji parent at
`/wordpress/wp-content/themes/koji`, and `tests` at `/wordpress/d3-tests` in a
**disposable** WordPress Playground instance. Activate the child theme, use pretty
permalinks, and request `/d3-tests/run.php`. The runner parses every theme PHP file
with `TOKEN_PARSE` and checks query isolation, combined pagination, primary RSS,
metadata sanitization/caching, and static-homepage compatibility. Tests create
and delete fixtures; never expose this runner on a production installation.
