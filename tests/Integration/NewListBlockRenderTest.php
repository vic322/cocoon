<?php
/**
 * 動的ブロック cocoon-blocks/new-list のレンダリング golden master テスト。
 *
 * WordPress コア流の経路（テストファクトリで決定的な投稿を seed し、
 * do_blocks() でサーバーサイドレンダリング）で新着リストブロックを描画し、
 * 揮発要素（投稿ID・日付表示・URL・バージョンクエリ等）を正規化したうえで
 * golden master ファイルと比較する。
 *
 * golden の生成/更新: 環境変数 UPDATE_GOLDEN=1 を付けて実行すると、
 * 比較の代わりに golden ファイルを書き出す。
 *
 *   UPDATE_GOLDEN=1 vendor/bin/phpunit --testsuite integration \
 *     --filter NewListBlockRenderTest
 */

namespace Cocoon\Tests\Integration;

class NewListBlockRenderTest extends IntegrationTestCase
{
    /** golden ファイル格納ディレクトリ */
    private const FIXTURES_DIR = __DIR__ . '/fixtures';

    /** seed した投稿ID（作成順） */
    private array $post_ids = [];

    public function set_up(): void
    {
        parent::set_up();

        // 決定的なカテゴリー（固定名・固定slug）を用意し、全 seed 投稿に割り当てる。
        // これにより get_post_class() が出力する category-* クラスが
        // 既定カテゴリーの slug に依存せず安定する。
        $cat_id = self::factory()->category->create([
            'name' => 'ゴールデンカテゴリー',
            'slug' => 'golden-category',
        ]);

        // 決定的な投稿を seed（固定タイトル・固定日付・固定slug・固定抜粋）。
        // 実DBの既存投稿に依存しないよう、必要な投稿だけをこのテスト内で生成する。
        // 日付降順（order=desc）で新しい順に並ぶ: C > B > A。
        $seeds = [
            [
                'post_title'   => 'ゴールデンマスター記事A',
                'post_name'    => 'golden-post-a',
                'post_date'    => '2021-01-01 09:00:00',
                'post_content' => 'これは記事Aの本文です。',
                'post_excerpt' => '記事Aの抜粋テキストです。',
            ],
            [
                'post_title'   => 'ゴールデンマスター記事B',
                'post_name'    => 'golden-post-b',
                'post_date'    => '2021-02-02 09:00:00',
                'post_content' => 'これは記事Bの本文です。',
                'post_excerpt' => '記事Bの抜粋テキストです。',
            ],
            [
                'post_title'   => 'ゴールデンマスター記事C',
                'post_name'    => 'golden-post-c',
                'post_date'    => '2021-03-03 09:00:00',
                'post_content' => 'これは記事Cの本文です。',
                'post_excerpt' => '記事Cの抜粋テキストです。',
            ],
        ];

        foreach ($seeds as $seed) {
            $post_id = self::factory()->post->create(array_merge($seed, [
                'post_status'   => 'publish',
                'post_type'     => 'post',
                'post_category' => [$cat_id],
            ]));
            $this->post_ids[] = $post_id;
        }
    }

    /**
     * 既定属性（count=3, 日付/抜粋なし）の新着リストが golden と一致すること。
     */
    public function test_新着リストブロックの既定レンダリングが_golden_と一致する(): void
    {
        $attrs = [
            'count'       => 3,
            'sticky'      => false,
            'showAllCats' => true,
        ];
        $this->assertRenderMatchesGolden('new-list-default', $attrs);
    }

    /**
     * 日付・抜粋を有効化した新着リストが golden と一致すること。
     */
    public function test_新着リストブロックの日付抜粋つきレンダリングが_golden_と一致する(): void
    {
        $attrs = [
            'count'       => 3,
            'sticky'      => false,
            'showAllCats' => true,
            'date'        => true,
            'snippet'     => true,
        ];
        $this->assertRenderMatchesGolden('new-list-date-snippet', $attrs);
    }

    /**
     * 指定属性でブロックをレンダリングし、正規化後 HTML を golden と比較する。
     * UPDATE_GOLDEN=1 のときは golden を書き出す。
     */
    private function assertRenderMatchesGolden(string $name, array $attrs): void
    {
        $rendered  = $this->render_new_list_block($attrs);
        $actual    = $this->normalize($rendered);
        $golden_file = self::FIXTURES_DIR . '/' . $name . '.html';

        if (getenv('UPDATE_GOLDEN')) {
            if (!is_dir(self::FIXTURES_DIR)) {
                mkdir(self::FIXTURES_DIR, 0777, true);
            }
            file_put_contents($golden_file, $actual);
            // 生成モードでは書き出しの成否のみ確認する。
            $this->assertFileExists($golden_file);
            return;
        }

        $this->assertFileExists(
            $golden_file,
            "golden ファイルがありません: {$golden_file}\n" .
            'UPDATE_GOLDEN=1 を付けて実行すると生成できます。'
        );
        $expected = file_get_contents($golden_file);
        $this->assertSame($expected, $actual, "レンダリング結果が golden ({$name}) と一致しません。");
    }

    /**
     * new-list ブロックを do_blocks() でサーバーサイドレンダリングする。
     */
    private function render_new_list_block(array $attrs): string
    {
        $json  = wp_json_encode($attrs);
        $block = '<!-- wp:cocoon-blocks/new-list ' . $json . ' /-->';
        return do_blocks($block);
    }

    /**
     * 揮発要素を正規化する。
     *
     * 正規化ルール:
     *  - 行末の空白を除去（末尾空白の揺れを吸収）
     *  - パーマリンク等の投稿ID/タームID (?p=123 / ?cat=1 等) → 0
     *  - get_post_class の post-123 クラス                   → post-0
     *  - entry-date 内の日付テキスト (get_the_time 出力)      → __DATE__
     *  - アセットの ?ver=... キャッシュバスター                → ?ver=__VER__
     *
     * 固定 seed（固定日付・固定抜粋・固定カテゴリー slug）により、
     * タイトル・抜粋・カテゴリークラスは正規化不要で安定する。
     */
    private function normalize(string $html): string
    {
        // 行末の空白を整理
        $html = preg_replace('/[ \t]+(\r?\n)/', '$1', $html);

        // パーマリンク中の投稿ID/タームID
        $html = preg_replace('/([?&](?:p|page_id|post|cat|tag_id|m)=)\d+/', '${1}0', $html);

        // get_post_class の数値付き投稿クラス
        // （post-date/post-update 等は数字を含まないため影響しない）
        $html = preg_replace('/\bpost-\d+\b/', 'post-0', $html);

        // entry-date の中身（get_the_time / get_update_time の出力）
        $html = preg_replace(
            '#(<span class="entry-date">).*?(</span>)#s',
            '${1}__DATE__${2}',
            $html
        );

        // アセット URL のバージョンクエリ
        $html = preg_replace('/([?&]ver=)[^"\'&\s]+/', '${1}__VER__', $html);

        return $html;
    }
}
