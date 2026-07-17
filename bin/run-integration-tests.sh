#!/usr/bin/env bash
#
# WordPress 統合テスト（integration レーン）をローカルの Docker で実行する。
#
# 概要:
#   ユニットレーン（PHPUnit 11 / Brain\Monkey）とは分離した統合レーンを、
#   実 WordPress 環境で実行する。統合レーンは WordPress 7.0 テストスイートが
#   要求する PHPUnit 9.6 + yoast/phpunit-polyfills ^1.1（tests/integration-tooling）
#   を使い、bootstrap は WP_UnitTestCase をロードする tests/Integration/bootstrap.php。
#
#   実行経路は .github/workflows/phpunit.yml の integration-tests ジョブと同一:
#     1. WordPress コア + wordpress-develop テストスイートを取得
#     2. wp-tests-config.php を生成（テスト用 DB を指す）
#     3. tests/integration-tooling で composer install（PHPUnit 9.6 + polyfills）
#     4. phpunit-integration.xml.dist で integration testsuite を実行
#
# 前提:
#   - Docker / Docker Compose が利用可能なこと
#   - テスト用 MySQL は docker/docker-compose.wp7.0-php8.4.yml で起動する
#     （初期化 SQL で DB "wordpress_test" を作成済み）
#
# 環境変数（すべて任意・上書き可能）:
#   WP_VERSION            テスト対象の WordPress バージョン       (default: 7.0.1)
#   COCOON_PHP_IMAGE      PHP ランタイムとして使う Docker イメージ (default: wordpress:7.0-php8.4-apache)
#   COCOON_COMPOSE_FILE   MySQL を起動する compose ファイル
#   COCOON_MYSQL_SERVICE  compose 内の MySQL サービス名
#   COCOON_DOCKER_NETWORK テスト実行コンテナを接続する Docker ネットワーク
#   COCOON_DB_HOST/NAME/USER/PASSWORD  テスト用 DB 接続情報
#   COCOON_WORK_VOLUME    WP コア/テストスイートを保持する Docker ボリューム名
#   http_proxy/https_proxy  設定されていればコンテナへ引き継ぐ（企業プロキシ等）
#
# 追加引数はそのまま phpunit に渡される。例:
#   bin/run-integration-tests.sh --filter NoticeTest
#   UPDATE_GOLDEN=1 bin/run-integration-tests.sh --filter NewListBlockRenderTest
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"

# --- 設定 ---
WP_VERSION="${WP_VERSION:-7.0.1}"
PHP_IMAGE="${COCOON_PHP_IMAGE:-wordpress:7.0-php8.4-apache}"
COMPOSE_FILE="${COCOON_COMPOSE_FILE:-$REPO_DIR/docker/docker-compose.wp7.0-php8.4.yml}"
MYSQL_SERVICE="${COCOON_MYSQL_SERVICE:-mysql-7-0-php84}"
DOCKER_NETWORK="${COCOON_DOCKER_NETWORK:-docker_cocoon-70-php84-network}"
DB_HOST="${COCOON_DB_HOST:-$MYSQL_SERVICE}"
DB_NAME="${COCOON_DB_NAME:-wordpress_test}"
DB_USER="${COCOON_DB_USER:-root}"
DB_PASSWORD="${COCOON_DB_PASSWORD:-rootpassword}"
WORK_VOLUME="${COCOON_WORK_VOLUME:-cocoon_integration_work}"

COMPOSE="docker compose -f $COMPOSE_FILE"

echo "== [1/4] テスト用 MySQL を起動 ($MYSQL_SERVICE) =="
$COMPOSE up -d "$MYSQL_SERVICE"

echo "== MySQL の healthy を待機 =="
for _ in $(seq 1 30); do
  status="$(docker inspect -f '{{.State.Health.Status}}' "$($COMPOSE ps -q "$MYSQL_SERVICE")" 2>/dev/null || echo starting)"
  [ "$status" = "healthy" ] && break
  sleep 2
done
echo "   MySQL status: ${status:-unknown}"

# --- コンテナへ渡すプロキシ設定（任意） ---
PROXY_ARGS=()
if [ -n "${http_proxy:-}" ]; then PROXY_ARGS+=(-e "http_proxy=${http_proxy}"); fi
if [ -n "${https_proxy:-}" ]; then PROXY_ARGS+=(-e "https_proxy=${https_proxy}"); fi
PROXY_ARGS+=(-e "no_proxy=${no_proxy:-$DB_HOST,localhost,127.0.0.1}")

# --- コンテナ内で実行するセットアップ + テスト ---
# 変数はホスト側で展開して埋め込む。
INNER=$(cat <<INNER_EOF
set -euo pipefail
WORK=/work
WP_CORE_DIR="\$WORK/wordpress"
WP_TESTS_DIR="\$WORK/wordpress-tests-lib"
WP_VERSION="$WP_VERSION"

mkdir -p "\$WORK"

# --- WordPress コア ---
if [ ! -f "\$WP_CORE_DIR/wp-load.php" ]; then
  echo "== WordPress コア \$WP_VERSION を取得 =="
  mkdir -p "\$WP_CORE_DIR"
  curl -sSL "https://wordpress.org/wordpress-\${WP_VERSION}.tar.gz" | tar xz --strip-components=1 -C "\$WP_CORE_DIR"
fi

# --- wordpress-develop テストスイート (includes + data) ---
if [ ! -f "\$WP_TESTS_DIR/includes/functions.php" ]; then
  echo "== wordpress-develop テストスイート \$WP_VERSION を取得 =="
  mkdir -p "\$WP_TESTS_DIR"
  tmptar="\$WORK/wp-develop.tar.gz"
  curl -sSL "https://codeload.github.com/WordPress/wordpress-develop/tar.gz/refs/tags/\${WP_VERSION}" -o "\$tmptar"
  tar xz -C "\$WORK" -f "\$tmptar" \
    "wordpress-develop-\${WP_VERSION}/tests/phpunit/includes" \
    "wordpress-develop-\${WP_VERSION}/tests/phpunit/data"
  mv "\$WORK/wordpress-develop-\${WP_VERSION}/tests/phpunit/includes" "\$WP_TESTS_DIR/includes"
  mv "\$WORK/wordpress-develop-\${WP_VERSION}/tests/phpunit/data" "\$WP_TESTS_DIR/data"
  rm -rf "\$WORK/wordpress-develop-\${WP_VERSION}" "\$tmptar"
fi

# --- wp-tests-config.php ---
cat > "\$WP_TESTS_DIR/wp-tests-config.php" <<CFG
<?php
define('ABSPATH', '\$WP_CORE_DIR/');
define('DB_NAME', '$DB_NAME');
define('DB_USER', '$DB_USER');
define('DB_PASSWORD', '$DB_PASSWORD');
define('DB_HOST', '$DB_HOST');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
\\\$table_prefix = 'wptests_';
define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');
define('WP_PHP_BINARY', 'php');
define('WPLANG', '');
CFG

# --- テーマを WP コアへシンボリックリンク（テーマ名 = cocoon-master） ---
mkdir -p "\$WP_CORE_DIR/wp-content/themes"
rm -f "\$WP_CORE_DIR/wp-content/themes/cocoon-master"
ln -s /theme "\$WP_CORE_DIR/wp-content/themes/cocoon-master"

# --- composer 取得 & 統合ツールチェーンの依存インストール ---
if [ ! -f "\$WORK/composer.phar" ]; then
  echo "== composer を取得 =="
  curl -sSL https://getcomposer.org/download/latest-stable/composer.phar -o "\$WORK/composer.phar"
fi
echo "== composer install (tests/integration-tooling) =="
php "\$WORK/composer.phar" install --working-dir=/theme/tests/integration-tooling \
  --prefer-dist --no-progress --no-interaction 2>&1 | tail -5

# --- 統合レーン実行 ---
echo "== integration テストを実行 =="
cd /theme
export WP_TESTS_DIR="\$WP_TESTS_DIR"
exec tests/integration-tooling/vendor/bin/phpunit -c phpunit-integration.xml.dist --colors=always $*
INNER_EOF
)

echo "== [2/4] PHP ランタイムコンテナ ($PHP_IMAGE) で実行 =="
docker run --rm \
  --network "$DOCKER_NETWORK" \
  "${PROXY_ARGS[@]}" \
  ${UPDATE_GOLDEN:+-e UPDATE_GOLDEN="$UPDATE_GOLDEN"} \
  -v "$REPO_DIR":/theme \
  -v "$WORK_VOLUME":/work \
  -w /theme \
  "$PHP_IMAGE" \
  bash -c "$INNER"
