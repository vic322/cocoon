/**
 * `@wordpress/editor` のテスト用スタブ。
 *
 * 本番ビルドでは wp-scripts が `@wordpress/editor` を外部化し、実行時に
 * グローバル `wp.editor` へ解決する（依存 package.json には含まれない）。
 * テストでは実体が無いため、ブロックがモジュールロード時に import する
 * 名前付きエクスポートのみを最小スタブで供給する。
 *
 * 利用箇所: 8 ブロック（cta/info-list/navicard/new-list/popular-list/
 * profile/ranking/template）が `ServerSideRender` を edit プレビューで使う。
 * save/serialize には一切関与しないため、フィクスチャの決定性に影響しない。
 */
const ServerSideRender = () => null;

module.exports = {
	ServerSideRender,
	default: ServerSideRender,
};
