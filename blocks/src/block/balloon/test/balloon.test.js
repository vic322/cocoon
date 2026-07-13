/**
 * balloon ブロックのフィクスチャ回帰テスト。
 *
 * WordPress コアの "full-content" フィクスチャ方式に倣い、シリアライズ済みの
 * ブロック HTML（fixtures/<name>__<variant>.html）を検証する:
 *   1. parse() でブロックにパースできる
 *   2. isValid === true（save 出力と保存済み HTML が一致する = デシリアライズ健全性）
 *   3. validationIssues が空
 *   4. serialize() で元の HTML にラウンドトリップする（再シリアライズ安定性）
 *
 * これにより save.js / block.json / deprecated.js の変更が保存形を壊した場合に
 * 検知できる、JS 側回帰安全網の骨格となる。
 */
import { readFileSync } from 'fs';
import path from 'path';

import {
	registerBlockType,
	parse,
	serialize,
	setCategories,
	getCategories,
} from '@wordpress/blocks';
import { RichText } from '@wordpress/block-editor';

import { metadata, name, settings } from '../index';

const FIXTURES_DIR = path.join( __dirname, 'fixtures' );

const readFixture = ( variant ) =>
	readFileSync(
		path.join( FIXTURES_DIR, `balloon__${ variant }.html` ),
		'utf8'
	).trim();

beforeAll( () => {
	// block.json の category "cocoon-block" はテスト環境では未登録なので追加する。
	setCategories( [
		...getCategories(),
		{ slug: 'cocoon-block', title: 'Cocoon' },
	] );

	// innerBlocks に使う core/paragraph の最小スタブ。
	// フィクスチャ内の <p> の保存形はこのスタブが基準となる。
	registerBlockType( 'core/paragraph', {
		title: 'Paragraph',
		edit: () => null,
		category: 'text',
		attributes: {
			content: {
				type: 'string',
				source: 'html',
				selector: 'p',
				default: '',
			},
		},
		save: ( { attributes } ) => (
			<RichText.Content tagName="p" value={ attributes.content } />
		),
	} );

	// テスト対象。不安定 API（blocks.js の bootstrap）を避け、
	// index.js の export を使って直接登録する。
	registerBlockType( metadata, settings );
} );

describe( 'balloon block fixtures', () => {
	it.each( [ 'default', 'with-inner-blocks' ] )(
		'%s: パースすると単一の有効な balloon ブロックになる',
		( variant ) => {
			const html = readFixture( variant );
			const blocks = parse( html );

			expect( blocks ).toHaveLength( 1 );
			expect( blocks[ 0 ].name ).toBe( name );
			expect( blocks[ 0 ].isValid ).toBe( true );
			expect( blocks[ 0 ].validationIssues ).toEqual( [] );
		}
	);

	it.each( [ 'default', 'with-inner-blocks' ] )(
		'%s: 再シリアライズで元の HTML にラウンドトリップする',
		( variant ) => {
			const html = readFixture( variant );
			const blocks = parse( html );

			expect( serialize( blocks ) ).toBe( html );
		}
	);
} );
