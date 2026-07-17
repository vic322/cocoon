/**
 * 全 Cocoon 構造化ブロックのフィクスチャ回帰テスト（汎用ハーネス）。
 *
 * WordPress コアの "full-content" フィクスチャ方式に倣い、block.json 自動発見で
 * 登録した各ブロックについて、シリアライズ済みの保存 HTML
 * （fixtures/<slug>__default.html）を検証する:
 *   1. parse() で単一ブロックにパースできる
 *   2. name が block.json の name と一致する
 *   3. isValid === true（save 出力と保存済み HTML が一致 = デシリアライズ健全性）
 *   4. validationIssues が空
 *   5. serialize() で元の HTML にラウンドトリップする（再シリアライズ安定性）
 *
 * これにより、いずれかのブロックの save.js / block.json / deprecated.js の変更が
 * 保存形を壊した場合に、個別テストを書かずとも 1 ファイルで検知できる。
 *
 * フィクスチャは手書き当て推量ではなく、createBlock / getBlockFromExample →
 * serialize で実保存形をキャプチャして生成している（jest/block-registry.js と
 * scratchpad の generate-fixtures.js を参照）。
 */
import { readFileSync } from 'fs';
import path from 'path';

import { parse, serialize } from '@wordpress/blocks';

// eslint-disable-next-line @typescript-eslint/no-var-requires
const { registerAllBlocks } = require( '../../jest/block-registry' );

const FIXTURES_DIR = path.join( __dirname, 'fixtures' );

const readFixture = ( slug ) =>
	readFileSync(
		path.join( FIXTURES_DIR, `${ slug }__default.html` ),
		'utf8'
	).trim();

// core/list など一部のコアブロック save は子要素に key を付けずにレンダリングする。
// serialize（内部で renderToString）時に React の dev 警告が出るが、保存出力の
// 正しさとは無関係で決定的。@wordpress/jest-console が console.error で失敗するのを
// 避けるため、この 1 種のみを絞って抑制する（他の console.error は従来どおり失敗）。
//
// jest-console は setupFilesAfterEnv の beforeEach で毎テスト console.error を
// 張り替えるため、こちらの beforeEach（後に登録＝後に実行）で再ラップする。
let forwardError;
beforeEach( () => {
	forwardError = console.error;
	jest.spyOn( console, 'error' ).mockImplementation( ( ...args ) => {
		const message = String( args[ 0 ] ?? '' );
		if ( message.includes( 'unique "key" prop' ) ) {
			return;
		}
		forwardError( ...args );
	} );
} );

afterEach( () => {
	if ( console.error.mockRestore ) {
		console.error.mockRestore();
	}
} );

const { registered } = registerAllBlocks();

// describe.each 用の [表示名, ブロック定義] タプル。
const cases = registered.map( ( b ) => [ b.slug, b ] );

describe( 'Cocoon ブロックのフィクスチャ回帰（全登録ブロック）', () => {
	it( '対象ブロックが 1 つ以上発見・登録されている', () => {
		expect( registered.length ).toBeGreaterThan( 0 );
	} );

	describe.each( cases )( '%s', ( slug, block ) => {
		it( 'パースすると単一の有効なブロックになる', () => {
			const html = readFixture( slug );
			const blocks = parse( html );

			expect( blocks ).toHaveLength( 1 );
			expect( blocks[ 0 ].name ).toBe( block.name );
			expect( blocks[ 0 ].isValid ).toBe( true );
			expect( blocks[ 0 ].validationIssues ).toEqual( [] );
		} );

		it( '再シリアライズで元の HTML にラウンドトリップする', () => {
			const html = readFixture( slug );
			const blocks = parse( html );

			expect( serialize( blocks ) ).toBe( html );
		} );
	} );
} );
