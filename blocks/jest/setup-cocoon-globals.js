/**
 * Cocoon ブロックのテスト用グローバル定義。
 *
 * Cocoon のブロックソースは、WordPress 管理画面から `wp_localize_script` 等で
 * 注入されるグローバル変数（`wp`, `gbSettings`, `gbSpeechBalloons` など）に
 * モジュール読み込み時点で依存している。jest（jsdom）にはこれらが存在しないため、
 * ここで最小限のスタブを定義する。
 *
 * これは実行前セットアップ（jest の setupFiles）であり、テスト対象モジュールが
 * import される前に評価される必要がある。
 */

// transforms.js が `const { createBlock } = wp.blocks;` をトップレベルで実行するため、
// 実物の @wordpress/blocks を wp.blocks として供給する。
const blocks = require( '@wordpress/blocks' );
global.wp = global.wp || {};
global.wp.blocks = blocks;

// edit.js がトップレベルで参照するグローバル（未定義だと ReferenceError）。
global.gbSettings = global.gbSettings || {};
global.gbSpeechBalloons = global.gbSpeechBalloons || [];

// helpers.js は typeof ガード済みだが、明示しておく。
global.gbColors = global.gbColors || { keyColor: '#19448e' };
global.gbCodeLanguages = global.gbCodeLanguages || [];
