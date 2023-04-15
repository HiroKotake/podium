<?php
/**
 * controlFlag.php
 * システムの制御関連のフラグを設定するファイル
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
 * 管理者ページを表示可能状態にするか
 *      true: 開示する
 *      false: 開示しない
 */
define('ADMIN_MODE', true);

/**
 * 実行時間計測　(BCMathライブラリ必須)
 *      true: 計測した実行時間を表示する
 *      false: 計測した実行時間を表示しない
 */
define('EXECTIME_CHECK', true);

/**
 * 環境定義 - 設定ファイルの読み込み先指定
 *      ENV_PERSONAL    個人開発環境    (config直下)
 *      ENV_DEVELOP     個別開発環境    (config/develop配下)
 *      ENV_SUNDBOX     サンドボックス環境  (config/sundbox配下)
 *      ENV_TEST        統合テスト環境    (config/test配下)
 *      ENV_BUSINESS    本番環境        (config/business配下)
 */
define('ENVEROMENT', ENV_PERSONAL);

/**
 * 言語設定
 */
define('LANGUAGE', 'jp');

/**
 * タイムゾーン設定
 */
define('TIMEZONE', 'Asia/Tokyo');

/**
 * 文字エンコード
 */
define('CHAR_SET', 'utf-8');


/**
 * DBテーブルのプライマリーキー
 * プライマリーキーとして使用するキー名は、全システムで同じ名称を使用すること！
 */
define('DB_PRIMARY_KEY', 'Id');

/**
 * ログ関連設定
 */
/**
 * ログ書き込み先
 */
define('LOG_DIR', ROOT_PATH . 'log/');

/**
 * アクセスログのON/OFF
 */
define('ACCESS_LOG_ON', true);
/**
 * アクセスログの遅延書き込み
 */
define('ACCESS_LOG_DELAY', true);

/**
 * SQLログのON/OFF
 */
define('SQL_LOG_ON', true);
/**
 * SQLログの遅延書き込み
 */
define('SQL_LOG_DELAY', true);
