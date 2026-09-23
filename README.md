# motonator.com 改善作業リポジトリ

## リポジトリの構成

このリポジトリは motonator.com の**全ファイルではなく**、実際に編集する必要がある部分だけを管理しています。

```
public_html/
  ads.txt                          AdSense収益化に必須
  .htaccess                        参考用（WordPressが自動生成する部分あり）
  wp-content/themes/motonator/     カスタムテーマ本体（ここを編集する）
```

WordPress本体（wp-admin, wp-includes）、標準プラグイン、メディア（wp-content/uploads）、
`wp-config.php`（DB接続情報などの機密情報を含む）は**意図的に含めていません**。
理由：
- 本体・プラグインはWordPress管理画面から直接更新するもので、バージョン管理の対象にする必要がない
- メディアは巨大でGit向きではない
- `wp-config.php` にはデータベースのパスワード等が平文で入っており、絶対にコミットしてはいけない

`.gitignore` でもこれらを除外するようにしていますが、今後サイト全体のバックアップZIPを
このフォルダに展開する場合は、コミット前に `git status` で意図しないファイルが
含まれていないか必ず確認してください。

## デプロイの仕組み

`.github/workflows/deploy-site.yml` が、`public_html/ads.txt` または
`public_html/wp-content/themes/motonator/` 以下に変更をpushすると、
GitHub Actions経由でXserverへFTPSアップロードします。
サーバーのパスワードはGitHub Secretsに保存されており、Claude（AI）は一切参照できません。

### 必要なGitHub Secrets（設定済み）
| Secret名 | 値 |
|---|---|
| `FTP_SERVER` | `sv2232.xserver.jp` |
| `FTP_USERNAME` | Xserverのサーバー(FTP)アカウント |
| `FTP_PASSWORD` | Xserverのサーバー(FTP)パスワード |
| `FTP_REMOTE_DIR` | `/motonator.com/public_html/` |

## 今後の運用

- テーマファイル（`header.php`、`single.php`など）の修正は、このリポジトリ内で
  直接編集してpushすれば自動でサーバーに反映されます（wp-adminでの直接編集は不要になります）
- 新規記事の自動投稿については別途、WordPress REST API + アプリケーションパスワードを
  使った仕組みを検討中（進捗はこのREADMEに追記していきます）

---

## ① 技術的な優先修正（完了済み）

- ✅ ads.txt 設置・デプロイ（AdSenseパブリッシャーID: `pub-4449490024488807`）
- ✅ `header.php` 内の古いUniversal Analyticsタグ（`UA-120539212-1`）を削除、GA4のみに整理
- ✅ WordPress本体・プラグインを最新版に更新
- ✅ Google Search Console登録・`sitemap_index.xml` 送信

## ② AdSense再申請（保留中）

サイトに埋め込まれていたAdSenseアカウント自体が却下・利用不可だったため、
新規申請の前にまずサイトのコンテンツを充実させる方針に変更。

## ③ コンテンツ拡充（進行中）

2019年以降のネットスラング・人気作品の名言記事を新規追加中。
