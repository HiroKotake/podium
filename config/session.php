<?php
/**
 * session.php
 * 
 * @category  Configure
 * @package   Session 
 * @author    Takahiro Kotake <tkotake@teleios.com>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 */

/**
 * セッション管理
 */
$session = [
    //'type' => SESSION_TYPE_FILE,
    'type' => SESSION_TYPE_DB,
    'expire' => 3600,
    'database' => [
        'status' => true,                   // 設定の有効性 : defaultに設定されている場合は無視されて、trueとして扱います
        'type' => 'mysql',                  // データベースの種類
        'host' => '127.0.0.1',              // DBサーバのアドレス
        'port' => '3306',                   // ポート番号
        'charset' => 'utf8',                // キャラクタセット
        'database' => 'podium1',            // データベース名
        'user' => 'hoge',                   // データベースユーザID
        'password' => 'Tiger!Tank!88?',     // パスワード
        'options' => [                      // PDOオプション
            \PDO::ATTR_PERSISTENT => true,  // コネクションプール実施
        ]
    ],
];