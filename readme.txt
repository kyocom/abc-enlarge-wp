=== ABC Enlarge ===
Contributors: kyocom
Tags: image, zoom, enlarge, lightbox, gallery
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 1.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Inline image zoom for WordPress. Enlarges images in place without covering the page, so the surrounding text stays readable.

== Description ==

ABC Enlarge brings the [abc-enlarge](https://github.com/kyocom/abc-enlarge) jQuery plugin to WordPress. Unlike typical lightbox plugins that dim the whole screen, it enlarges the clicked image **in place** — the article text around it stays readable. Ideal for web magazines and long-form articles.

**What it does**

* Automatically adds the `abc-enlarge` class to linked images in your post content (only images wrapped in a link to an image file, so nothing breaks).
* Swaps in the high-resolution image from the link's `href` on click, and restores the small one on collapse.
* On portrait phones, expands the image into a horizontally scrollable, auto-centered view.
* Auto-targets images inside WordPress `.gallery-columns-0` galleries.
* Lets you disable enlargement per post — enabled by default.

== Installation ==

1. Upload the `abc-enlarge` folder to `/wp-content/plugins/`, or install the ZIP via Plugins → Add New → Upload Plugin.
2. Activate the plugin through the Plugins menu in WordPress.
3. Make sure your images link to the media file (in the block/classic editor, set the image link to "Media File"). Those images are enlarged automatically.

== Usage ==

Enlargement is on by default for every post and page. To turn it off for a single post, open the editor and uncheck it in the **ABC Enlarge** box:

* Block editor: the box appears at the bottom of the editor.
* Classic editor: the box appears in the right-hand sidebar.

Check "Disable image enlargement for this post" to opt that post out.

== Frequently Asked Questions ==

= Which images get enlarged? =

Only images wrapped in a link that points to an image file (`.jpg`, `.png`, `.gif`, `.webp`, `.avif`, `.bmp`, `.svg`). This is what WordPress produces when an image links to its Media File. Images without such a link are left untouched so they never break.

= Does it work with the block editor? =

Yes. The per-post toggle works in both the block editor and the classic editor.

= How do I load the unminified script for debugging? =

Define `SCRIPT_DEBUG` as `true` in `wp-config.php` and the plugin loads the non-minified build.

== Changelog ==

= 1.0.0 =
* Initial release. Auto-adds the `abc-enlarge` class to linked content images and adds a per-post enable/disable toggle.
