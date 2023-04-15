<?php
/**
 * caches.php
 * 
 * @category  Configure
 * @package   Cache
 * @author    Takahiro Kotake <tkotake@teleios.com>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */

/**
 * 一般と管理者ページで共通に使用するキャッシュを設定する
 */
$caches = [
    'default' => [
        'status' => true,           // 設定の有効性 : 使用する(true)or使用しない() [defaultに設定されている場合は無視されて、trueとして扱います]
        'type' => CACHE_TYPE_REDIS, // キャッシュ種別
        'host' => '127.0.0.1',      // DBサーバのアドレス
        'port' => 6379,             // ポート番号
        'user' => null,             // ユーザID
        'password' => null,         // パスワード
        'timeout' => '0',           // タイムアウト時間
    ],
];
