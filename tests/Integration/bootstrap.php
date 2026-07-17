<?php
/**
 * WordPress 統合テスト ブートストラップ
 *
 * WordPress のテストスイートを使用して、実際の WordPress 環境で
 * テーマ機能をテストします。
 *
 * 使い方:
 * 1. WP_TESTS_DIR 環境変数に WordPress テストスイートのパスを設定
 *    例: export WP_TESTS_DIR=/path/to/wordpress-develop/tests/phpunit
 *
 * 2. wp-tests-config.php を設定（テスト用DB接続情報）
 *
 * 3. テスト実行:
 *    vendor/bin/phpunit --testsuite integration
 *
 * ローカル環境でのセットアップ:
 * - Docker を使用する場合は docker/docker-compose.wp7.0-php8.4.yml を参照
 * - GitHub Actions では自動的にセットアップされます
 */

// WordPress テストスイートのパスを環境変数から取得
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    // 一般的なパスをフォールバックとして試行
    $possible_paths = [
        dirname(__DIR__, 6) . '/tests/phpunit',                    // WordPress develop ルート
        '/tmp/wordpress-tests-lib',                                 // 標準的な CI パス
        getenv('HOME') . '/wordpress-tests-lib',                   // ホームディレクトリ
    ];

    foreach ($possible_paths as $path) {
        if (file_exists($path . '/includes/functions.php')) {
            $_tests_dir = $path;
            break;
        }
    }
}

if (!$_tests_dir) {
    echo "WordPress テストスイートが見つかりません。\n";
    echo "WP_TESTS_DIR 環境変数を設定するか、WordPress テストスイートをインストールしてください。\n\n";
    echo "セットアップ方法:\n";
    echo "  1. GitHub Actions: .github/workflows/phpunit.yml に設定済み\n";
    echo "  2. ローカル: docker/docker-compose.wp7.0-php8.4.yml を使用\n";
    echo "  3. 手動: export WP_TESTS_DIR=/path/to/wordpress-develop/tests/phpunit\n";
    exit(1);
}

// Composer オートローダー（統合レーン専用ツールチェーン: PHPUnit 9.6 + polyfills）
//
// ユニットレーンのテーマ vendor は PHPUnit 11 だが、WordPress 7.0 テストスイートは
// PHPUnit 10 で削除された parseTestMethodAnnotations() を使うため 9.x が必須。
// ここでテーマ vendor（PHPUnit 11）を読み込むと 9.6 実行系とクラスが混在して fatal に
// なるため、統合レーンは tests/integration-tooling/vendor（PHPUnit 9.6 + polyfills）を用いる。
$_tooling_autoload = __DIR__ . '/../integration-tooling/vendor/autoload.php';
if (file_exists($_tooling_autoload)) {
    require_once $_tooling_autoload;
}

// テスト用クラス（Cocoon\Tests\ => tests/）の PSR-4 オートローダー。
// テーマ本体の関数は WordPress がテーマ切り替え時に読み込むため、ここでは扱わない。
spl_autoload_register(function ($class) {
    $prefix = 'Cocoon\\Tests\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $rel  = substr($class, strlen($prefix));
    $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $rel) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// WordPress テストスイートの関数群を読み込み（tests_add_filter 等を定義）
// これを先に読み込まないと、下の tests_add_filter() が未定義になる。
require_once $_tests_dir . '/includes/functions.php';

// テーマ読み込み関数を WordPress のテストブートストラップ前に登録
$_theme_dir = dirname(__DIR__, 2);
tests_add_filter('setup_theme', function() use ($_theme_dir) {
    // テーマを切り替え
    switch_theme('cocoon-master');
});

// WordPress テストスイートを読み込み
require $_tests_dir . '/includes/bootstrap.php';

// 統合テスト用基底クラス
require_once __DIR__ . '/IntegrationTestCase.php';
