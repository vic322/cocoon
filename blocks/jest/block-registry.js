/**
 * Cocoon ブロックの自動発見・一括登録ハーネス。
 *
 * `blocks.js`（本番の登録経路）は `unstable__bootstrapServerSideBlockDefinitions`
 * や大量の `wp.*` グローバル副作用に依存しており、テストには不向き。そこで本
 * モジュールは、各ブロックの `index.js` が公開する `{ metadata, name, settings }`
 * を直接 import して `registerBlockType` する、テスト専用の登録経路を提供する。
 *
 * 発見方法（block.json 自動発見型）:
 *   src/{block,block-universal,micro}/<slug>/ に block.json と index.js が
 *   両方存在するディレクトリを構造化ブロックとみなす。
 *
 * innerBlocks（core/paragraph, core/list など）の忠実なシリアライズのため、
 * @wordpress/block-library の registerCoreBlocks() でコアブロックも登録する。
 */
const fs = require( 'fs' );
const path = require( 'path' );

const {
	registerBlockType,
	getBlockType,
	setCategories,
	getCategories,
} = require( '@wordpress/blocks' );
const { registerCoreBlocks } = require( '@wordpress/block-library' );

const SRC_DIR = path.join( __dirname, '..', 'src' );
const TARGET_DIRS = [ 'block', 'block-universal', 'micro' ];

// block.json の category は既定カテゴリーに含まれないため補う。
const COCOON_CATEGORIES = [
	{ slug: 'cocoon-block', title: 'Cocoon' },
	{ slug: 'cocoon-universal-block', title: 'Cocoon Universal' },
	{ slug: 'cocoon-micro', title: 'Cocoon Micro' },
];

/**
 * ディスク上に存在するが本ハーネスの対象外とするブロックと、その理由。
 * blocks.js の登録配列に含まれない（＝本番でも未登録の）ブロックはここで除外する。
 */
const EXCLUDED = {
	'comparison-box':
		'blocks.js の登録配列に含まれず本番でも未登録。加えて、blocks.js から import されない子ブロック cocoon-blocks/comparison-left・comparison-right（items/block.js）に依存する。',
};

/**
 * 対象ディレクトリを走査し、block.json と index.js を持つブロックを列挙する。
 *
 * @return {{dir:string, slug:string, indexPath:string, blockJsonPath:string}[]}
 */
function discoverBlockDirs() {
	const found = [];
	for ( const dir of TARGET_DIRS ) {
		const base = path.join( SRC_DIR, dir );
		if ( ! fs.existsSync( base ) ) {
			continue;
		}
		for ( const slug of fs.readdirSync( base ).sort() ) {
			const dirPath = path.join( base, slug );
			if ( ! fs.statSync( dirPath ).isDirectory() ) {
				continue;
			}
			const indexPath = path.join( dirPath, 'index.js' );
			const blockJsonPath = path.join( dirPath, 'block.json' );
			if (
				fs.existsSync( indexPath ) &&
				fs.existsSync( blockJsonPath )
			) {
				found.push( { dir, slug, indexPath, blockJsonPath } );
			}
		}
	}
	return found;
}

/**
 * 各ブロックの index.js を読み込む。ロード時例外（重い外部依存など）は
 * 失敗として捕捉し、除外理由付きで返す。
 *
 * @return {{loaded:Object[], excluded:Object[]}}
 */
function loadCocoonBlocks() {
	const loaded = [];
	const excluded = [];
	for ( const info of discoverBlockDirs() ) {
		if ( EXCLUDED[ info.slug ] ) {
			excluded.push( { ...info, reason: EXCLUDED[ info.slug ] } );
			continue;
		}
		try {
			// eslint-disable-next-line import/no-dynamic-require
			const mod = require( info.indexPath );
			const { metadata, name, settings } = mod;
			if ( ! metadata || ! name || ! settings ) {
				throw new Error(
					'index.js が { metadata, name, settings } を公開していない'
				);
			}
			loaded.push( { ...info, metadata, name, settings } );
		} catch ( e ) {
			excluded.push( {
				...info,
				reason: `index.js のロードに失敗: ${ e.message }`,
			} );
		}
	}
	return { loaded, excluded };
}

let registrationResult = null;

/**
 * コアブロック＋発見した全 Cocoon ブロックを登録する（冪等）。
 *
 * @return {{registered:Object[], excluded:Object[]}}
 *   registered: 登録に成功したブロック（{slug,name,blockJsonPath,...}）
 *   excluded:   対象外・ロード失敗・登録失敗のブロックと理由
 */
function registerAllBlocks() {
	if ( registrationResult ) {
		return registrationResult;
	}

	// カテゴリー補完（既存を保持しつつ不足分を追加）。
	const existing = getCategories();
	const existingSlugs = new Set( existing.map( ( c ) => c.slug ) );
	setCategories( [
		...existing,
		...COCOON_CATEGORIES.filter( ( c ) => ! existingSlugs.has( c.slug ) ),
	] );

	// innerBlocks の忠実化のためコアブロックを登録。
	registerCoreBlocks();

	const { loaded, excluded } = loadCocoonBlocks();
	const registered = [];
	const registerExcluded = [ ...excluded ];

	for ( const b of loaded ) {
		try {
			if ( ! getBlockType( b.name ) ) {
				// balloon の PoC と同様、metadata を第1引数に渡して登録する。
				registerBlockType( b.metadata, b.settings );
			}
			registered.push( b );
		} catch ( e ) {
			registerExcluded.push( {
				...b,
				reason: `registerBlockType に失敗: ${ e.message }`,
			} );
		}
	}

	registrationResult = { registered, excluded: registerExcluded };
	return registrationResult;
}

module.exports = {
	SRC_DIR,
	TARGET_DIRS,
	discoverBlockDirs,
	loadCocoonBlocks,
	registerAllBlocks,
};
