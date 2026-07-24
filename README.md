# ABC Enlarge (WordPress plugin)

> Inline image zoom for WordPress, powered by the [abc-enlarge](https://github.com/kyocom/abc-enlarge) jQuery plugin. Enlarges images **in place** without covering the page, so the surrounding text stays readable.
> ページを覆い隠さず、文章を読める状態のまま画像を拡大する WordPress プラグイン。

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

🇺🇸 [English](#english) ・ 🇯🇵 [日本語](#日本語)

---

## English

### Features

- 🏷️ **Auto class** — automatically adds the `abc-enlarge` class to linked images in post content.
- 🖼️ **Inline zoom** — no overlay; enlarges the clicked image in place while surrounding text stays readable.
- 🔍 **High-res swap** — swaps to the large image from the link's `href`, restores the small one on collapse.
- 📱 **Touch-friendly** — on portrait phones the image expands into a horizontally scrollable, auto-centered view.
- 🧩 **Gallery support** — auto-targets images inside WordPress `.gallery-columns-0` galleries.
- 🎛️ **Per-post toggle** — disable enlargement on any individual post/page. **Enabled by default.**

### How it targets images

Only `<img>` elements wrapped in an `<a>` whose `href` points to an image file
(`.jpg`, `.png`, `.gif`, `.webp`, `.avif`, `.bmp`, `.svg`) receive the class.
That is exactly what WordPress outputs when an image is set to link to its
**Media File**, and it keeps unlinked images from breaking on click.

```html
<a href="large.jpg"><img class="abc-enlarge" src="small.jpg" width="400" height="300"></a>
```

### Install

1. Copy this folder to `wp-content/plugins/abc-enlarge/` (or upload the ZIP via **Plugins → Add New → Upload Plugin**).
2. Activate **ABC Enlarge**.
3. Set your content images to link to the **Media File**. They are enlarged automatically.

### Per-post option

Enlargement is on by default. To turn it off for one post, open the editor and
check **"Disable image enlargement for this post"** in the **ABC Enlarge** box
(bottom of the block editor, or the sidebar in the classic editor).

### Developer hooks

```php
// Change which post types get the toggle (default: post, page).
add_filter( 'abc_enlarge_post_types', function ( $types ) {
    $types[] = 'product';
    return $types;
} );

// Programmatically force enable/disable for a post.
add_filter( 'abc_enlarge_is_enabled_for_post', function ( $enabled, $post ) {
    return $enabled;
}, 10, 2 );
```

Define `SCRIPT_DEBUG` as `true` to load the unminified script.

### Requirements

- WordPress 5.0+
- PHP 7.0+
- jQuery (bundled with WordPress)

### License

[MIT](LICENSE) © kyocom (Kyo Ichida)

---

## 日本語

### 特徴

- 🏷️ **クラス自動付与** — post 本文中の「画像リンク付き img」に `abc-enlarge` クラスを自動で付与。
- 🖼️ **インライン拡大** — オーバーレイなし。クリックした画像をその場で拡大し、周囲の文章は読めるまま。
- 🔍 **高解像度差し替え** — リンクの `href` に指定した大きい画像へ差し替え、縮小時に元へ復元。
- 📱 **タッチ端末対応** — スマホ縦画面では横スクロール可能なビューへ拡大し、中央へ自動スクロール。
- 🧩 **ギャラリー対応** — WordPress の `.gallery-columns-0` 内の画像を自動対象化。
- 🎛️ **post 単位のオプション** — 各投稿・固定ページで拡大を無効化できます。**デフォルトは有効**。

### 付与対象について

画像ファイル（`.jpg` / `.png` / `.gif` / `.webp` / `.avif` / `.bmp` / `.svg`）への
リンク `<a href>` で囲まれた `<img>` だけにクラスを付与します。これは WordPress で
画像のリンク先を **「メディアファイル」** にしたときの出力そのものです。リンクの
ない画像は対象外なので、クリックで画像が壊れることはありません。

### インストール

1. このフォルダを `wp-content/plugins/abc-enlarge/` に配置（または ZIP を **プラグイン → 新規追加 → プラグインのアップロード** から）。
2. **ABC Enlarge** を有効化。
3. 本文画像のリンク先を **「メディアファイル」** にすると、自動的に拡大対象になります。

### post 単位のオプション

拡大はデフォルトで有効です。無効にしたい投稿では、編集画面の **ABC Enlarge**
ボックスで **「この投稿で画像拡大を無効にする」** にチェックを入れてください
（ブロックエディターでは画面下部、クラシックエディターではサイドバー）。

### 開発者向けフック

```php
// オプションを表示する投稿タイプを変更（デフォルト: post, page）
add_filter( 'abc_enlarge_post_types', function ( $types ) {
    $types[] = 'product';
    return $types;
} );

// 投稿ごとに有効/無効をプログラムで制御
add_filter( 'abc_enlarge_is_enabled_for_post', function ( $enabled, $post ) {
    return $enabled;
}, 10, 2 );
```

`SCRIPT_DEBUG` を `true` にすると非圧縮版スクリプトを読み込みます。

### ライセンス

[MIT](LICENSE) © kyocom (Kyo Ichida)
