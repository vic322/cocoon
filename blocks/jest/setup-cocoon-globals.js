/**
 * Cocoon ブロックのテスト用グローバル定義。
 *
 * Cocoon のブロックソースは、WordPress 管理画面から `wp_localize_script` 等で
 * 注入されるグローバル変数（`wp`, `gbSettings`, `gbSpeechBalloons` など）に
 * モジュール読み込み時点で依存している。jest（jsdom）にはこれらが存在しないため、
 * ここで最小限かつ決定的なスタブを定義する。
 *
 * これは実行前セットアップ（jest の setupFiles）であり、テスト対象モジュールが
 * import される前に評価される必要がある。golden（フィクスチャ）を安定させるため、
 * すべてのスタブは固定値にする（乱数・時刻・環境依存の値を含めない）。
 *
 * 対象ブロック（src/block, src/block-universal, src/micro）がモジュールロード時に
 * 参照するグローバルの和集合をカバーする。
 */

// --- wp.* 名前空間 ---------------------------------------------------------
// Cocoon のブロックは一部の依存を ESM import ではなく `wp.*` グローバル経由で
// 参照する。これらは deprecated.js / save.js / edit.js のモジュールトップレベルで
// 分割代入されるため、未定義だと import 時点で ReferenceError になる。
const blocks = require( '@wordpress/blocks' );
const components = require( '@wordpress/components' );
const element = require( '@wordpress/element' );

global.wp = global.wp || {};
// transforms.js が `const { createBlock } = wp.blocks;` をトップレベルで実行する。
global.wp.blocks = blocks;
// icon-box/info-box/blogcard/sticky-box の deprecated.js が
// `const { PanelBody, SelectControl } = wp.components;` をトップレベルで実行する。
global.wp.components = components;
// tab/save.js の `const { RawHTML } = wp.element;` と
// timeline-item/deprecated.js の `const { Fragment } = wp.element;` 用。
global.wp.element = element;

// radar/edit.js はモジュールトップレベルで `wp.data.subscribe(...)` を呼ぶ。
// 実ストアの副作用（registerCoreBlocks のディスパッチで購読コールバックが発火し、
// 未登録の core/block-editor ストアに触れて例外になる）を避けるため、
// wp.data は決定的なノーオペスタブにする。edit のレンダリングは行わないため、
// select は空のブロック一覧を返すだけでよい。
global.wp.data = global.wp.data || {
	subscribe: () => () => {},
	select: () => ( {
		getBlocks: () => [],
		getBlockRootClientId: () => null,
		getCurrentPostType: () => null,
	} ),
	dispatch: () => ( {} ),
};

// --- gb* ローカライズ変数 --------------------------------------------------
// ほとんどは edit 内で `typeof gbXxx !== 'undefined'` ガード済みだが、
// 一部（balloon/edit.js の gbSettings / gbSpeechBalloons）はトップレベルで
// 参照される。決定性のためすべて固定の空値で定義する。
global.gbSettings = global.gbSettings || {};
global.gbSpeechBalloons = global.gbSpeechBalloons || [];
global.gbColors = global.gbColors || { keyColor: '#19448e' };
global.gbCodeLanguages = global.gbCodeLanguages || [];
global.gbTemplates = global.gbTemplates || [];
global.gbItemRankings = global.gbItemRankings || [];
global.gbNavMenus = global.gbNavMenus || [];
global.gbUsers = global.gbUsers || [];

// amazon-product-link / rakuten-product-link は window 経由で参照する。
global.window = global.window || global;
global.window.gbAmazonBlockDefaults =
	global.window.gbAmazonBlockDefaults || {};
global.window.gbRakutenBlockDefaults =
	global.window.gbRakutenBlockDefaults || {};
