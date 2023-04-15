<?php
/**
 * databases.php
 * データベースに関する設定ファイル
 * 
 * @category  Configure
 * @package   Database
 * @author    Takahiro Kotake <tkotake@teleios.com>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */


/**
 * 一般と管理者ページで共通に使用するデータベースを設定する
 */
$databases = [ 
    'default' => [
        'status' => true,                   // 設定の有効性 : 使用する(true)or使用しない(false) [defaultに設定されている場合は無視されて、trueとして扱います]
        'type' => 'mysql',                  // データベースの種類
        'host' => '127.0.0.1',              // DBサーバのアドレス
        'port' => '3306',                   // ポート番号
        'charset' => 'utf8',                // キャラクタセット
        'database' => 'podium1',            // データベース名
        'user' => 'hoge',                   // データベースユーザID
        'password' => 'fuga!fuga!99',     // パスワード
        'options' => [                      // PDOオプション
            \PDO::ATTR_PERSISTENT => true,  // コネクションプール実施
        ]
    ],
    'podium2' => [
        'status' => true,                   // 設定の有効性 : 使用する(true)or使用しない(false) [defaultに設定されている場合は無視されて、trueとして扱います]
        'type' => 'mysql',                  // データベースの種類
        'host' => '127.0.0.1',              // DBサーバのアドレス
        'port' => '3306',                   // ポート番号
        'charset' => 'utf8',                // キャラクタセット
        'database' => 'podium2',            // データベース名
        'user' => 'hoge',                   // データベースユーザID
        'password' => 'fuga!fuga!11',     // パスワード
        'options' => [                      // PDOオプション
            \PDO::ATTR_PERSISTENT => true,  // コネクションプール実施
        ]
    ],
    'podium3' => [
        'status' => true,                   // 設定の有効性 : 使用する(true)or使用しない(false) [defaultに設定されている場合は無視されて、trueとして扱います]
        'type' => 'mysql',                  // データベースの種類
        'host' => '127.0.0.1',              // DBサーバのアドレス
        'port' => '3306',                   // ポート番号
        'charset' => 'utf8',                // キャラクタセット
        'database' => 'podium3',            // データベース名
        'user' => 'hoge',                   // データベースユーザID
        'password' => 'fuga!fuga!22?',     // パスワード
        'options' => [                      // PDOオプション
            \PDO::ATTR_PERSISTENT => true,  // コネクションプール実施
        ]
    ],
    'sqlite' => [
        'status' => true,
        'type' => 'sqlite',
        'filename' => ROOT_PATH . 'strage/ausers.db'
    ],
];
