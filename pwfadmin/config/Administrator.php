<?php
/**
 * Administrator.php
 */

/**
 * 管理者権限関連固定値
 */
define('PWF_AUTH_CATEGORY_BOTH', 0);   // 管理者カテゴリ - 開発・運営兼用
define('PWF_AUTH_CATEGORY_DEVEL', 1);   // 管理者カテゴリ - 開発者
define('PWF_AUTH_CATEGORY_MANAGE', 2);  // 管理者カテゴリ - 運営者
define('PWF_AUTH_CATEGORY_NAME', ['開発/運営', '開発', '運営',]);
define('PWF_AUTH_LEVEL_MASTER', 5);     // (High)
define('PWF_AUTH_LEVEL_TOP',    4);     //
define('PWF_AUTH_LEVEL_HIGH',   3);     //
define('PWF_AUTH_LEVEL_MIDDLE', 2);     //
define('PWF_AUTH_LEVEL_LOW',    1);     //
define('PWF_AUTH_LEVEL_BOTTOM', 0);     // (Low)
define('PWF_AUTH_LEVEL_NAME', ['最低', '底', '中', '高', '最高', 'マスター']);
define('PWF_ADMIN_PROFILE_LID', 'Lid');     //　ログインユーザID
define('PWF_ADMIN_PROFILE_NAME', 'Name');   // ユーザの名前
define('PWF_ADMIN_PROFILE_MAIL', 'Mail');   // ユーザのメールアドレス
define('PWF_ADMIN_LOGIN_OK', 0);
define('PWF_ADMIN_LOGIN_NG', 1);
define('PWF_MAKE_TARGET_APP', 0);   // 作成するクラスの格納先(アプリケーション用)
define('PWF_MAKE_TARGET_ADM', 1);   // 作成するクラスの格納先(管理者用)
define('PWF_MAKE_TARGET_NAME', ['Application', 'Admin']);
define('PWF_MAKE_CLASS_CONTROLLER', 0); // 作成するクラス(コントローラー)
define('PWF_MAKE_CLASS_MODEL', 1);      // 作成するクラス(モデル)
define('PWF_MAKE_CLASS_LOGIC', 2);      // 作成するクラス(ロジック)
define('PWF_MAKE_CLASS_FACADE', 3);     // 作成するクラス(ファサード)
define('PWF_MAKE_CLASS_NAME', ['Controller', 'Model', 'Logic', 'Facade']);
/**
 * アクセスログのON/OFF
 */
define('PWF_ADMIN_LOG_ON', true);
/**
 * アクセスログの遅延書き込み
 */
define('PWF_ADMIN_LOG_DELAY', true);
define('PWF_ADMIN_AUTH_SWITCH', true);    // 認証を実施するか（true:実施する、false:実施しない）　開発中は実施しないもアリかも
//define('PWF_ADMIN_AUTH_SWITH', false;);    // 認証を実施するか（true:実施する、false:実施しない）　開発中は実施しないもアリかも

/**
 * 管理者の認証関連設定
 */
$administrator = [
    // 'Authenticate' => false,                // 認証を実施するか（true:実施する、false:実施しない）　開発中は実施しないもアリかも
    'Authenticate' => true,                // 認証を実施するか（true:実施する、false:実施しない）　開発中は実施しないもアリかも
    'InitialAdmin' => 'admin',
    'InitialAdminPwd' => 'password',
    'Strage' => [
        // type = file
        'type' => STRAGE_TYPE_FILE,
        'directory' => ROOT_PATH . 'strage',
        // type => sqlite
        /*
        'type' => STRAGE_TYPE_SQLITE,
        'filename' => ROOT_PATH . 'strage/ausers.db',
        */
        // type => database [mysql, pgsql]
        /*
        'type' => STRAGE_TYPE_MYSQL,
        'host' => '127.0.0.1',              // DBサーバのアドレス
        'port' => '3306',                   // ポート番号
        'charset' => 'utf8',                // キャラクタセット
        'database' => 'podium1',            // データベース名
        'user' => 'hoge',                   // データベースユーザID
        'password' => 'Tiger!Tank!88?',     // パスワード
        'options' => [                      // PDOオプション
            \PDO::ATTR_PERSISTENT => true,  // コネクションプール実施
        ]
        */
    ],
    'LoginExpire' => (60 * 60 * 24),        // ログインの有効期間(現在時からの経過秒数)
];