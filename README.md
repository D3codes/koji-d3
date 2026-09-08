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
- Homepage and search posts without featured images show their title and excerpt without a
  placeholder image, including posts appended by infinite scrolling.
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
## Homepage category tabs

Open **Appearance → Customize → Homepage Tabs**, leave **Show homepage tab bar**
checked, and choose **Add tab**. Edit each name, choose **Specific categories**,
**All categories**, or **All except**, and check the categories to include or
exclude. Check **Default tab** on the tab that should open at the homepage URL. Only one
tab can be selected. Use **Move up / Move down** to order tabs independently.
Choose **Publish** to save. Remove deletes a tab from the configuration.

There are no predefined tabs. Until at least one named tab is saved, the homepage
is unchanged. Disabling the feature also restores the normal feed. The editor
supports 20 tabs; ordering uses buttons rather than dragging, and each tab has a default checkbox. If the default is removed, the first
remaining tab becomes the default. Category selection is hidden for All categories.
The editor requires JavaScript; public tab navigation and pagination do not.

Each new tab gets an immutable generated identifier, displayed in the editor,
such as `tab-a1b2c3d4`. Its URL is `/?tab=tab-a1b2c3d4`; renaming it preserves
bookmarks. The default tab links to `/`. Page links retain the identifier, e.g.
`/page/2/?tab=tab-a1b2c3d4` (or `?paged=2&tab=...` with plain permalinks).
Invalid/deleted tab identifiers fall back to the selected default. On sites with a
static front page, tabs appear on the configured posts page and use its URL.
No permalink flush is needed.

Filters use category IDs with exact selected-category matching (select child
categories explicitly). Inclusion uses OR. Exclusion removes posts assigned to
any checked category, even if they also belong to another category. Deleted
categories are ignored; inclusion with no remaining categories shows Koji's
normal empty grid. Filtered views disable sticky promotion to prevent unrelated
posts leaking into results; All categories retains normal sticky behavior.
RSS, REST, admin, search, archives, individual posts and secondary queries remain
unchanged. No custom post type is registered.

Koji's AJAX endpoint does not accept the needed category query arguments. For
the homepage and search results, infinite scrolling retrieves the next normal page
HTML and extracts its post previews, retaining Koji's append/layout/focus code.
This costs a full page render per loaded page, but uses the same main query as
ordinary pagination and requires no additional endpoint or query per tab.

Files added:

- `theme/inc/home-tabs.php`: configuration sanitization, main-query filtering,
  semantic navigation, pagination URLs and script registration.
- `theme/inc/customizer-home-tabs.php`: settings and repeater control registration.
- `theme/assets/js/customizer-home-tabs.js`: add/edit/remove/reorder editor.
- `theme/assets/css/customizer-home-tabs.css`: scoped editor styles.
- `theme/assets/js/home-tabs.js`: next-page HTML adapter for Koji's loader.
- `theme/home.php`: preserves Koji's homepage grid and adds navigation above it.
- `tests/home-tabs.php`: disposable WordPress integration and PHP syntax checks.
- `tests/blueprint.json`: Playground setup and test runner.

Files modified:

- `theme/functions.php`: loads the two feature modules.
- `theme/style.css`: centered sticky navigation, oval active/hover styling matching the sidebar,
  and keyboard focus states.
- `theme/pagination.php`: retains a previous-page link on the last filtered page.
- `README.md`: configuration, architecture, file inventory and verification notes.

### Verification

Run only against a **disposable** WordPress installation: the fixture creates
posts/categories/pages and changes theme settings. With the Koji parent downloaded
to `/tmp/koji-parent/koji`, run from the repository root:

```sh
npx @wp-playground/cli run-blueprint \
  --mount=/tmp/koji-parent/koji:/wordpress/wp-content/themes/koji \
  --mount="$PWD/theme:/wordpress/wp-content/themes/koji-d3" \
  --mount="$PWD/tests:/wordpress/d3-tests" \
  --blueprint=tests/blueprint.json
node --check theme/assets/js/home-tabs.js
node --check theme/assets/js/customizer-home-tabs.js
git diff --check
```

The fixture checks filtering, OR/exclusion behavior, sticky isolation, page-two
results/URLs, invalid IDs, empty/disabled settings, deleted categories,
sanitization, static-front-page compatibility, query isolation and PHP parsing.
For browser QA, use the same mounts with `server --port=9411` instead of
`run-blueprint`; test Customizer edits and publication, tab navigation, browser
history, infinite scrolling, ordinary page links and a narrow viewport.
