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

## 新規記事の自動投稿の仕組み

`content/articles/*.json` に記事データを追加してpushすると、
`.github/workflows/publish-articles.yml` が WordPress REST API 経由で
**下書き（draft）として**自動投稿します（いきなり公開はされません。内容確認後、
wp-adminから手動で公開してください）。認証はアカウントの本パスワードではなく
「アプリケーションパスワード」を使い、GitHub Secretsに保存するのでClaudeは値を扱いません。

記事データの形式（例: `content/articles/taipa.json`）:
```json
{
  "title": "タイパ",
  "ruby": "タイムパフォーマンス",
  "genre": ["ネット用語"],
  "work": [],
  "etc": ["コスパ", "Z世代", "流行語大賞"],
  "mean": "<p>「の意味」セクションのHTML</p>",
  "content": "<p>「の元ネタ」セクションのHTML</p>"
}
```
`title` はテンプレートが自動で「〇〇の元ネタって？」と補うため、**用語そのものだけ**を入れます。

### 自動投稿を有効にするための事前準備（wp-admin側、1回だけ）

1. **アプリケーションパスワードを発行**
   ユーザー（自分のアカウント）のプロフィール画面 → 一番下の「アプリケーションパスワード」欄で
   新規発行（名前は「GitHub Actions」など任意）。表示されたパスワードをこの場ではなく、
   GitHub Secretsに直接登録してください。
2. **カスタム分類（genre/work/etc）をREST APIに公開**
   プラグイン「Custom Post Type UI」→ タクソノミーの編集 →
   `genre`・`work`・`etc` それぞれで「REST APIに表示」を有効化して保存。
3. **ACFカスタムフィールド（ruby/mean）をREST APIに公開**
   「カスタムフィールド」→ 該当フィールドグループを開き、「設定」タブで
   「Show in REST API」を有効化して保存。

### 追加で必要なGitHub Secrets
| Secret名 | 値 |
|---|---|
| `WP_API_BASE` | `https://motonator.com/wp-json` |
| `WP_USERNAME` | wp-adminのユーザー名 |
| `WP_APP_PASSWORD` | 上記1で発行したアプリケーションパスワード |

準備ができたら、Actionsタブから「Publish new articles to WordPress」を手動実行するか、
`content/articles/`に新しいjsonをpushすれば自動実行されます。

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
第一弾5記事を `content/articles/` に用意済み（タイパ／ぴえん／うっせぇわ／領域展開／全集中の呼吸）。
上記「事前準備」完了後、自動投稿ワークフローで下書き投稿 → 内容確認のうえ公開。
