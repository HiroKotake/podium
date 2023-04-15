<?php
/**
 * consts.php
 * 
 * @category  Configure
 * @package   Basic
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */

/**
 * ディレクトリ環境に対する定数
 */
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', $_SERVER['DOCUMENT_ROOT']);
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH',  str_replace('public', '', $_SERVER['DOCUMENT_ROOT']));
}
define('APP_DIR', 'app');       // 一般向けページ関連のディレクトリ名
define('PWF_ADMIN_DIR', 'pwfadmin');   // 管理者向けページ関連のディレクトリ名
define('APP_PATH', ROOT_PATH . APP_DIR . DIRECTORY_SEPARATOR);
define('CTRL_PATH', APP_PATH . 'controllers' . DIRECTORY_SEPARATOR);
define('FACADE_PATH', APP_PATH . 'facades' . DIRECTORY_SEPARATOR);
define('MODEL_PATH', APP_PATH . 'models' . DIRECTORY_SEPARATOR);
define('VIEW_PATH', APP_PATH . 'views' . DIRECTORY_SEPARATOR);
define('PWF_ADMIN_PATH', ROOT_PATH . PWF_ADMIN_DIR . DIRECTORY_SEPARATOR);
define('PWF_ADMIN_CTRL_PATH', PWF_ADMIN_PATH . 'controllers' . DIRECTORY_SEPARATOR);
define('PWF_ADMIN_FACADE_PATH', PWF_ADMIN_PATH . 'facades' . DIRECTORY_SEPARATOR);
define('PWF_ADMIN_MODEL_PATH', PWF_ADMIN_PATH . 'models' . DIRECTORY_SEPARATOR);
define('PWF_ADMIN_VIEW_PATH', PWF_ADMIN_PATH . 'views' . DIRECTORY_SEPARATOR);
define('RESOURCE_PATH', ROOT_PATH . 'resources' . DIRECTORY_SEPARATOR);

/**
 * システム関連定数
 */
define('COMPOSER_USE', 'composer');
define('DEFAULT_PAGE', 'defaultPage');
define('PWF_ADMIN_PAGE', 'adminPageInfo');
define('SYSTEM_BASE_LIBS', 'baseLibs');
define('SYSTEM_EXTRA_LIBS', 'libs');
define('SYSTEM_VIEW_EXTENTION', 'viewExtention');
define('SYSTEM_ERROR_PAGES', 'systemErrorPages');

/**
 * 実行環境に関する定数
 */
define('ENV_PERSONAL',   0);   // 個人開発環境
define('ENV_DEVELOP',    1);   // 個別開発環境
define('ENV_SUNDBOX',    2);   // サンドボックス環境
define('ENV_TEST',       3);   // 統合テスト環境
define('ENV_COMMERCIAL', 4);   // 本番環境
define('CONFIG_DIR', [
    '',
    '/develop',
    '/sundbox',
    '/test',
    '/commercial',
]);

/**
 * 自動実行関連
 */
define('AUTO_EXEC_INITIAL', 'initial');     // 開始直後に実行
define('AUTO_EXEC_PRESHOW', 'preShow');     // リクエスト情報処理前に実行
define('AUTO_EXEC_SHOW', 'show');           // コントローラー呼び出し前に実行
define('AUTO_EXEC_POSTSHOW', 'postShow');   // コントローラー実行後に実行
define('AUTO_EXEC_FINAL', 'final');         // 終了処理前に実行
define('AUTO_ACTION_EXEC', 'EXEC');         // (呼び出し方法) 直接実行 - staticファンクションの実行を想定
define('AUTO_ACTION_NEW',  'NEW');          // (呼び出し方法)　　クラスをインスタンス化したのち、指定したメソッドを実行することを想定
define('AUTO_INDEX_ACTION', 'action');      // (設定項目) 実行方法(EXEC or NEW)
define('AUTO_INDEX_CLASS',  'class');       // (設定項目) クラス名 (actionがNEWの場合に有効)
define('AUTO_INDEX_METHOD', 'method');      // (設定項目) メソッド名 (EXECの場合は　class名::メソッド名で指定。（）は不要)
define('AUTO_INDEX_PARAMS', 'params');      // (設定項目) 実行時パラメータ（連想配列にて指定) 
define('AUTO_INDEX_FLAG',   'flag');        // (設定項目) 実行の可否(true: 実行、false:実行しない)

/**
 * データベース関連
 */
define('DB_COMMON', 'common');
define('DB_GENERAL', 'general');
define('DB_ADMIN', 'admin');
define('DB_ADMIN_AUTH', 'adminAuth');
define('STRAGE_TYPE_FILE',   0);
define('STRAGE_TYPE_SQLITE', 1);
define('STRAGE_TYPE_MYSQL',  2);
define('STRAGE_TYPE_PGSQL',  3);


/**
 * セッション関連
 */
define('SESSION_TYPE_FILE', 'FILE');
define('SESSION_TYPE_DB', 'DATABASE');
define('SESSION_TYPE_CACHE', 'CACHE');

/**
 * キャッシュ関連
 */
define('CACHE_TYPE_REDIS', 'redis');
define('CACHE_COMMON', 'common');
define('CACHE_GENERAL', 'general');
define('CACHE_ADMIN', 'admin');
