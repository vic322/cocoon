/**
 * jest（wp-scripts test-unit-js）用の設定。
 *
 * @wordpress/scripts の既定プリセット（@wordpress/jest-preset-default）を土台に、
 * Cocoon 固有のグローバルスタブ（setup-cocoon-globals.js）を setupFiles に追加し、
 * 本番ビルドで外部化される `@wordpress/editor` をテスト用スタブに解決する。
 */
const path = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/jest-unit.config.js' );
const presetSetupFiles =
	require( '@wordpress/jest-preset-default/jest-preset.js' ).setupFiles || [];

module.exports = {
	...defaultConfig,
	setupFiles: [
		...presetSetupFiles,
		require.resolve( './jest/setup-cocoon-globals.js' ),
	],
	moduleNameMapper: {
		...( defaultConfig.moduleNameMapper || {} ),
		// 本番では wp.editor グローバルに外部化される（devDep に無い）。
		'^@wordpress/editor$': path.resolve(
			__dirname,
			'jest/stubs/wordpress-editor.js'
		),
	},
};
