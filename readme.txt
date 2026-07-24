=== ABC Enlarge ===
Contributors: ABC Japon
Tags: image, zoom, enlarge, lightbox, gallery
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 1.1.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Inline image zoom for WordPress. Enlarges images in place without covering the page, so the surrounding text stays readable.

== Description ==

ABC Enlarge brings the [abc-enlarge](https://github.com/kyocom/abc-enlarge) jQuery plugin to WordPress. Unlike typical lightbox plugins that dim the whole screen, it enlarges the clicked image **in place** — the article text around it stays readable. Ideal for web magazines and long-form articles.

**What it does**

* Automatically adds the `abc-enlarge` class to linked images in your post content (only images wrapped in a link to an image file, so nothing breaks).
* Swaps in the high-resolution image from the link's `href` on click, and restores the small one on collapse.
* On portrait phones, expands the image into a horizontally scrollable, auto-centered view.
* Applies to WordPress galleries (classic and block) — images become enlargeable regardless of their link setting. On by default, toggleable per post.
* Lets you disable enlargement per post — enabled by default.

== Installation ==

1. Upload the `abc-enlarge` folder to `/wp-content/plugins/`, or install the ZIP via Plugins → Add New → Upload Plugin.
2. Activate the plugin through the Plugins menu in WordPress.
3. Make sure your images link to the media file (in the block/classic editor, set the image link to "Media File"). Those images are enlarged automatically.

== Usage ==

Enlargement is on by default for every post and page. The **ABC Enlarge** box holds the controls:

* Block editor: the box appears at the bottom of the editor.
* Classic editor: the box appears in the right-hand sidebar.

The "Enable image enlargement for this post" checkbox is checked by default; uncheck it to opt that post out. The **Apply to WordPress galleries** checkbox (on by default) controls whether images in classic and block galleries are enlargeable too.

== Frequently Asked Questions ==

= Which images get enlarged? =

Outside galleries: only images wrapped in a link that points to an image file (`.jpg`, `.png`, `.gif`, `.webp`, `.avif`, `.bmp`, `.svg`). This is what WordPress produces when an image links to its Media File. Images without such a link are left untouched so they never break.

Inside WordPress galleries: with the gallery option on (default), gallery images are made enlargeable regardless of their link setting. The plugin resolves a full-size image URL (from the image's attachment ID, or by dropping the resize suffix from the filename) and never swaps in a non-image, so images don't break.

= Does it work with the block editor? =

Yes. The per-post toggles work in both the block editor and the classic editor.

= Does it work with custom post types? =

Yes. The script and auto-class run on every singular view, and the per-post toggles are shown for posts, pages, and public custom post types that support the editor.

= How do I load the unminified script for debugging? =

Define `SCRIPT_DEBUG` as `true` in `wp-config.php` and the plugin loads the non-minified build.

== Changelog ==

= 1.1.2 =
* Reword the per-post control to "Enable image enlargement for this post", checked by default. Unchecking it is evaluated first, before any class is added, so opted-out posts get no `abc-enlarge` markup at all. No change to existing posts (default stays enabled).

= 1.1.1 =
* Update the bundled abc-enlarge script to v1.0.3, adding a gallery CSS rule so an enlarged image fills its `.gallery-item` cell.

= 1.1.0 =
* Add a per-post "Apply to WordPress galleries" option (on by default). Classic and block gallery images become enlargeable regardless of their link setting.
* Extend the per-post toggle to public custom post types that support the editor.

= 1.0.0 =
* Initial release. Auto-adds the `abc-enlarge` class to linked content images and adds a per-post enable/disable toggle.
