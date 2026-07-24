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
- 🧩 **Gallery support** — classic `[gallery]` and block galleries become enlargeable regardless of link setting. On by default, toggleable per post.
- 🎛️ **Per-post toggles** — disable enlargement, or exclude galleries, on any individual post/page. **Both on by default.**

### How it targets images

**Outside galleries:** only `<img>` elements wrapped in an `<a>` whose `href`
points to an image file (`.jpg`, `.png`, `.gif`, `.webp`, `.avif`, `.bmp`,
`.svg`) receive the class — exactly what WordPress outputs when an image links
to its **Media File**. Unlinked images are left untouched so they never break.

```html
<a href="large.jpg"><img class="abc-enlarge" src="small.jpg" width="400" height="300"></a>
```

**Inside WordPress galleries** (option on by default): images are made
enlargeable no matter their link setting. The plugin resolves a full-size URL
from the image's attachment ID (`wp-image-{id}`) or by dropping the `-WxH`
resize suffix, and falls back to the image's own `src` — so it never swaps in
a non-image and images can't break.

### Install

1. Copy this folder to `wp-content/plugins/abc-enlarge/` (or upload the ZIP via **Plugins → Add New → Upload Plugin**).
2. Activate **ABC Enlarge**.
3. Set your content images to link to the **Media File**. They are enlarged automatically.

### Per-post option

Enlargement is on by default. In the **ABC Enlarge** box (bottom of the block
editor, or the sidebar in the classic editor) you get two controls:

- **Disable image enlargement for this post** — turns the whole feature off for that post.
- **Apply to WordPress galleries** — on by default; uncheck to exclude gallery images while keeping normal linked images enlargeable.

### Developer hooks

The script and auto-class run on every singular view, so **custom post types
are supported out of the box**. The per-post toggle is shown for `post`,
`page`, and every public custom post type that supports the editor.

```php
// Change which post types get the toggle
// (default: post, page + public custom post types that support the editor).
add_filter( 'abc_enlarge_post_types', function ( $types ) {
    $types[] = 'my_cpt';
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
- 🧩 **ギャラリー対応** — クラシック `[gallery]` とブロックギャラリーを、リンク設定に関わらず拡大可能に。デフォルト有効・post 単位で切替可。
- 🎛️ **post 単位のオプション** — 各投稿・固定ページで「拡大の無効化」と「ギャラリー除外」を切替。**どちらもデフォルト有効**。

### 付与対象について

**ギャラリー以外**：画像ファイル（`.jpg` / `.png` / `.gif` / `.webp` / `.avif` /
`.bmp` / `.svg`）へのリンク `<a href>` で囲まれた `<img>` だけにクラスを付与します。
これは画像のリンク先を **「メディアファイル」** にしたときの WordPress 出力そのもの
です。リンクのない画像は対象外なので、クリックで画像が壊れることはありません。

**WordPress ギャラリー内**（オプションはデフォルト有効）：リンク設定に関わらず
ギャラリー画像を拡大可能にします。フル画像URLを添付ファイルID（`wp-image-{id}`）
や `-幅x高さ` のリサイズ接尾辞除去から解決し、最終的には画像自身の `src` に
フォールバックするため、画像以外に差し替わることはなく、画像が壊れません。

### インストール

1. このフォルダを `wp-content/plugins/abc-enlarge/` に配置（または ZIP を **プラグイン → 新規追加 → プラグインのアップロード** から）。
2. **ABC Enlarge** を有効化。
3. 本文画像のリンク先を **「メディアファイル」** にすると、自動的に拡大対象になります。

### post 単位のオプション

拡大はデフォルトで有効です。編集画面の **ABC Enlarge** ボックス（ブロック
エディターでは画面下部、クラシックエディターではサイドバー）に2つの項目があります。

- **この投稿で画像拡大を無効にする** — その投稿の拡大機能を丸ごとオフにします。
- **WordPress ギャラリーにも適用** — デフォルト有効。オフにすると通常のリンク画像は拡大したまま、ギャラリー画像だけを対象外にできます。

### 開発者向けフック

スクリプトとクラス自動付与はすべての単一表示（`is_singular`）で動作するため、
**カスタム投稿タイプにもそのまま対応**します。post 単位の無効化オプションは、
`post` / `page` と、エディターをサポートする公開カスタム投稿タイプに表示されます。

```php
// オプションを表示する投稿タイプを変更
// （デフォルト: post, page ＋ エディター対応の公開カスタム投稿タイプ）
add_filter( 'abc_enlarge_post_types', function ( $types ) {
    $types[] = 'my_cpt';
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
