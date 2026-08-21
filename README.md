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