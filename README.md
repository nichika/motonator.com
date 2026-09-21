# motonator.com 改善作業リポジトリ

## ① 技術的な優先修正

### 1. ads.txt（作成済み・要デプロイ設定）
`public/ads.txt` に AdSense のパブリッシャーID（`pub-4449490024488807`）を記載済みです。
`.github/workflows/deploy-ads-txt.yml` が `main` ブランチに push されると
Xserver へ ads.txt だけを自動アップロードします（Claudeはサーバーのパスワードを
一切扱わないよう、GitHub Actions 経由のデプロイにしています）。

#### GitHub側の設定（ユーザー作業）
リポジトリの Settings → Secrets and variables → Actions → New repository secret で、
以下4つを**ご自身で**登録してください（このチャットには貼らないでください）。

| Secret名 | 値 |
|---|---|
| `FTP_SERVER` | `sv2232.xserver.jp` |
| `FTP_USERNAME` | Xserverのサーバー(FTP)アカウント |
| `FTP_PASSWORD` | Xserverのサーバー(FTP)パスワード |
| `FTP_REMOTE_DIR` | `/motonator.com/public_html/` |

上記4つのSecretsは登録済みです。

登録後、このリポジトリに push するか、Actionsタブから
「Deploy ads.txt to Xserver」を手動実行（workflow_dispatch）すればデプロイされます。

デプロイ後、`https://motonator.com/ads.txt` にアクセスして中身が表示されるか確認し、
AdSense管理画面の「サイトの収益化」→「ads.txt」ステータスが「見つかりました」になっているか確認してください。

### 2. AdSenseの配信状況確認（要ユーザー作業）
- AdSense管理画面 → 「サイトの状態」でポリシー違反や制限がないか確認
- 現在のコードは `enable_page_level_ads: true` の旧式スニペットのみで、
  ページ内には広告ユニットが実質1枠しかなく `unfilled` でした。
  ads.txt反映後も改善しない場合は、AdSense管理画面で最新の自動広告(Auto ads)
  スニペットを再取得し、WordPressのテーマ（header.php など）や
  「Ad Inserter」「Site Kit」等のプラグインで設置し直すことを推奨します。

### 3. アクセス解析の整理（要ユーザー作業）
現在、GA4（`G-WEVBH926YX`）と2023年7月に計測停止した旧Universal Analytics
（`UA-120539212-1`）のタグが両方読み込まれています。
WordPressのテーマ/プラグイン側でUAのトラッキングコードを削除し、GA4のみに統一してください。
（旧UAのビューでアクセス数を見ていた場合、実際のアクセスは反映されていません。GA4側を確認してください）

### 4. Search Console確認（要ユーザー作業）
- Google Search Console にプロパティ登録済みか確認
- 未登録なら登録し、`https://motonator.com/sitemap_index.xml` を送信
- 「ページ」レポートでインデックス除外の理由がないか確認（7年更新なしのため落ちている可能性あり）
