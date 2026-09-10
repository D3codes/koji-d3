# Koji D3

The custom WordPress child theme used by [d3.codes](https://d3.codes/), built on
[Koji](https://github.com/andersnoren/koji) by [Anders Norén](https://andersnoren.se/).
The Koji parent theme must be installed separately.

Some elements of Koji D3 are inspired by the aqua interface from OSX. Specifically thatdhruv's implementation in [Aqua 2](https://github.com/thatdhruv/aqua2).

## Features and changes

- Custom layout, typography, icons, responsive styles, footer, search form, and
  404 page.
- Consistent rounded corners on images, videos, and embedded frames.
- Homepage and search posts without featured images display text previews
  instead of placeholder images.
- Configurable homepage tabs show selected categories, all categories, or all
  except selected categories.
  - Add up to 20 tabs, reorder them, and choose a default.
  - Readable tab URLs use the tab name, such as `/?tab=Reading%20List`. Names must
  be non-empty and unique, ignoring letter case.
  - The default tab uses the homepage URL. Unknown or deleted tab links fall back
  to the default. Sites with a separate posts page use that page for tabs.
  - Tab filters and search queries carry through pagination and infinite scrolling.
- Infinite scrolling waits until the visitor scrolls and keeps the homepage URL
  stable.
- Optional light/dark mode toggle with a saved visitor preference, a separate dark
  mode logo, and configurable dark background color.
- Aqua-inspired UI elements
  - Graphite action buttons and a sticky segmented tab bar with a
  freely draggable selection and horizontal scrolling on narrow screens.
  - Matching glossy previous/next post arrows, search controls, mobile menu button, and social buttons
  with visible keyboard focus. Search fields use translucent graphite pill styling.

## Homepage tabs

Open **Appearance → Customize → Homepage Tabs** to add, name, reorder, or remove
tabs. Choose **Specific categories**, **All categories**, or **All except** for
each tab, select its categories, and check **Default tab** on the one to show
first. Choose **Publish** to save.

Selecting multiple categories shows posts in any of them. Excluding a category
hides posts assigned to it, even if they also belong to another category. Select
child categories explicitly when needed.

Disable **Show homepage tab bar** or remove all tabs to restore the normal feed.
If the default tab is removed, the first remaining tab becomes the default.

## Installation

1. Install the [Koji parent theme](https://wordpress.org/themes/koji/).
2. Copy this repository's `theme` directory to `wp-content/themes/koji-d3`.
3. Activate **Koji D3** under **Appearance → Themes**.
4. Choose Koji's infinite-scroll pagination option to enable automatic loading.

No separate build step is required.

## Dark mode

Upload an optional **Dark mode logo** under **Appearance → Customize → Site
Identity**, alongside the regular **Logo**. The regular logo is used in light
mode and as the fallback when no dark logo is selected. Set a regular logo first.
Both variants follow Koji's retina logo setting and switch with the color mode.

Enable **Appearance → Customize → Toggles → Show light/dark mode toggle**
to show a draggable sun/moon switch beside search, above the social icons in the desktop sidebar and
mobile menu. It is hidden by default. Visitors initially follow their system
color preference; choosing a mode saves it in their browser and synchronizes
both switches and other tabs. If browser storage is unavailable, the choice
still works for the current page. Hiding the toggle restores the existing light
appearance. Without JavaScript the site stays light and the switch stays hidden.

Dark mode uses charcoal surfaces, light text, contrasting links and focus rings,
and matching graphite controls without altering photos or video. Author-selected
block colors and third-party embedded content retain their own styling.

Set **Appearance → Customize → Colors → Dark mode background color** to change
the dark page, sidebar, and mobile menu background. The default is `#171a20`.
The existing background selector continues to control light mode. Dark tabs and
the search field share the same translucent surface.
