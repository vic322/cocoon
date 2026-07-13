# GitHub Actions ワークフロー

本リポジトリ（xserver-inc/cocoon およびそのフォーク vic322/cocoon）で使用する
GitHub Actions ワークフローの解説である。開発関連（PHPUnit 等）のワークフローは
別途セクションを追加して記載する。

| ワークフロー | 管理場所 | 用途 |
|---|---|---|
| `sync-upstream.yml` | **フォークのみ**（`automation` ブランチ） | upstream 同期 + POT 再生成（l10n関連） |
| `l10n-build-pr.yml` | **フォークのみ**（`automation` ブランチ） | 翻訳コンパイル + 還元PR（l10n関連） |
| `pot_generator.yml` | 本体 | POT 再生成（本体 PR #371 で push トリガー化） |
| `phpunit.yml` | 本体 | ユニットテスト |

旧・手動翻訳コンパイルの `po_to_mo.yml` / `po_to_json.yml` は l10n-build-pr に統合されたため
削除した（本体は PR #373）。

## l10n関連（翻訳自動化パイプライン）

> **このセクションはフォーク（vic322/cocoon）側の情報である。**
> フォークは xserver-inc/cocoon と Crowdin を接続するブリッジであり、
> 本体リポジトリは組織の制約により Crowdin 連携用トークンを保持できないため、
> Crowdin GitHub インテグレーションと自動化ワークフローはすべてフォーク側に置く。
> ワークフロー本体はフォークのデフォルトブランチ `automation` でのみ管理する。

### 全体図

```
┌─ xserver-inc/cocoon（本体）───────────────────────────────┐
│  pot_generator.yml: ソース変更の push で cocoon.pot を再生成 │
└──────────────┬────────────────────────────────────────────┘
               │ sync-upstream.yml（毎日 05:00 JST）
               ▼
┌─ vic322/cocoon（フォーク）────────────────────────────────┐
│  master              = upstream の純粋ミラー（ff-only）     │
│  translation_master  = upstream ミラー + POT再生成（force） │
│         │  ← Crowdin GitHub インテグレーションが監視        │
│         ▼                                                  │
│  [Crowdin] 翻訳作業 → l10n_translation_master に .po を push│
│         ▼                                                  │
│  l10n-build-pr.yml（6時間ごと）                             │
│    upstream/master 起点の l10n-release ブランチに .po を取込 │
│    → compile-all（.mo / .l10n.php / blocks-js.json）        │
│    → 差分があれば本体への還元PRを作成・更新                  │
└──────────────┬────────────────────────────────────────────┘
               │ 還元PR（人間がレビュー & マージ）
               ▼
        xserver-inc/cocoon master（次回同期で全ブランチが収束）
```

### ブランチ規約（フォーク側）

| ブランチ | 役割 | 直接コミット |
|---|---|---|
| `automation` | デフォルトブランチ。自動化ワークフローの実行元 | 可（ワークフロー変更のみ） |
| `master` | upstream の純粋ミラー | 禁止（ff できなくなり同期が停止する） |
| `translation_master` | Crowdin が監視するソースブランチ。毎日 force 更新される | 禁止（次回同期で消える） |
| `l10n_translation_master` | Crowdin が翻訳を push するサービスブランチ | 禁止（Crowdin 管理） |
| `l10n-release` | 還元PR用。ワークフローが force push で再生成する | 禁止（自動生成） |

### ワークフローの動作

#### sync-upstream.yml（毎日 05:00 JST / 手動実行可）

1. upstream/master をフォークの `master` に ff-only で反映する。ff 不可の場合は失敗する（手動解決が必要）
2. `translation_master` を upstream/master に force で揃え、その上で POT を再生成する。
   本体側の POT が最新であれば差分ゼロとなりコミットは発生しない

#### l10n-build-pr.yml（6時間ごと / 手動実行可）

1. `l10n_translation_master` の存在を確認する（なければスキップ）
2. upstream/master を起点に `l10n-release` を作り直し、Crowdin の `languages/*.po` のみを上書きする
3. `npm run compile-all` で .mo / .l10n.php / blocks-js.json を生成する
4. `languages/` に差分がある場合のみコミットし、本体への還元PRを作成する（既存PRがあれば force push で自動更新）

### 必要な設定

| 項目 | 場所 | 内容 |
|---|---|---|
| `UPSTREAM_PR_TOKEN` | フォークの Actions Secrets | 還元PR作成用 PAT（classic / `public_repo` スコープ）。未設定の場合、PR作成のみ警告スキップされる |
| Crowdin 自動同期 | Crowdin プロジェクト設定 | GitHub インテグレーションの対象ブランチ `translation_master`、ソース検知・翻訳 push の自動同期を有効化 |

### Crowdin が自動作成する「New Crowdin updates」PR について

Crowdin GitHub インテグレーションは翻訳 push 時にフォーク内 PR
（`l10n_translation_master` → `translation_master`）を自動作成する。この動作は無効化できない。

**この PR はマージせず、開いたまま放置する。**

- 本パイプラインは `l10n_translation_master` を直接読むため、この PR を経由せずに翻訳が本体へ還元される
- マージしても `translation_master` は毎日 upstream に force リセットされるため翌日消える。
  Crowdin のソース再スキャンを誘発するだけで意味がない
- クローズしても次回の翻訳同期で再作成される
- 未還元の翻訳差分を確認できるダッシュボードとしては有用である

### 手動運用として残る工程

1. Crowdin 上での翻訳作業
2. 還元PRのレビューとマージ（品質ゲート）

### 関連ドキュメント

- `scripts/i18n-translation-guide.md`（upstream）: 辞書ベースのローカル翻訳・コンパイル手順書。
  翻訳の SSoT は Crowdin であり、辞書スクリプトは AI 一括翻訳の作業ツールとして使用する。
  成果を .po として本体にコミットすれば、同期を経て Crowdin にインポートされる
