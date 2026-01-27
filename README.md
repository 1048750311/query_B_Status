# 📊 ステータスBユーザー検索システム

## 1. プロジェクト概要

本プロジェクトは **PHP + MySQL** を用いて開発した簡易的な管理画面システムです。  
指定した日付区間内において、**ステータスが「B」になっているユーザー一覧**を検索・表示することができます。  

システムは **宝塔（BT）パネル** 上にデプロイされております。

---

## 2. 使用技術

- PHP
- MySQL
- HTML / CSS
- JavaScript（jQuery）
- Nginx（宝塔パネル）
- Linux サーバー環境

---

## 3. プロジェクト構成

```text
Bt_task/
├── index.php     # フロントエンド画面（日付選択・検索結果表示）
├── api.php       # バックエンドAPI（検索ロジック）
├── db.php        # データベース接続
└── config.php    # データベース設定ファイル
```

---

## 4. データベース設計

### テーブル1：main_list（ユーザー主テーブル）

| カラム名 | 型 | 説明 |
|--------|----|----|
| id | INT | ユーザーID（主キー） |
| user_name | VARCHAR | ユーザー名 |
| status | CHAR | 現在のステータス |

### テーブル2：status_track（ステータス履歴テーブル）

| カラム名 | 型 | 説明 |
|--------|----|----|
| id | INT | ユーザーID（main_list.id と紐付け） |
| track_no | INT | ステータス履歴番号 |
| status | CHAR | ステータス値 |
| status_date | DATE | ステータス更新日 |
<img width="1072" height="693" alt="4802b9a2ca006985d2788993bd2ffc0c" src="https://github.com/user-attachments/assets/fd4e3323-1416-44d1-90b9-e66a9230c33d" />
<img width="1114" height="902" alt="5c9a38d816f4d496d9deac2d589285e4" src="https://github.com/user-attachments/assets/8f89190c-e614-493f-8400-043e55bf1fcb" />

---

## 5. 検索ロジック

### 検索条件

- `status_track.status = 'B'`
- 指定した開始日と終了日の範囲内
- `id` をキーとして `main_list` と結合し、ユーザー名を取得

### SQL例

```sql
SELECT DISTINCT m.user_name
FROM status_track s
JOIN main_list m ON m.id = s.id
WHERE s.status = 'B'
  AND s.status_date BETWEEN :start AND :end
ORDER BY m.user_name;
```

---

## 6. 機能一覧

- 日付区間を指定して検索
- ステータスBのユーザー抽出
- 検索結果を一覧表示
- PDOプリペアドステートメントによるSQLインジェクション対策
- フロントエンドとバックエンドの分離設計
- JSON形式でのデータ通信

---

## 7. セキュリティ・設計方針

- PDOのプリペアドステートメントを使用しSQLインジェクションを防止
- 日付パラメータの形式チェックを実装
- データベース設定を外部ファイルで管理
- APIは必要最小限のデータのみ返却

---

## 8. デプロイ方法（宝塔パネル）

1. 宝塔パネル上にてWebサイトを構築し、本プロジェクトをデプロイしました。
2. MySQLデータベースを作成し、サンプルデータを登録しました。
3. プロジェクトファイル一式をサイトのルートディレクトリへ配置しました。
4. ブラウザから「http://echoxiaoliu.top:20261/」　へアクセスすることで、動作を確認できます。

---
