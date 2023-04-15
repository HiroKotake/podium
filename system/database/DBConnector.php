<?php
/**
 * DBConnector.php
 * 
 * @category  Database
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\database;

use Exception;
use \PDO;
use \PDOException;
use system\exception\DBException;
use system\supports\DBHelper;

/**
 * DBConnector Class
 * 
 * @category Database
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class DBConnector
{
    /**
     * DBコネクション
     *
     * @var array
     */
    static private array $_dbStrage = [];
    /**
     * データベース情報
     *
     * @var array
     */
    static private array $_dbInfos = []; 
    /**
     * 初期化フラグ
     *
     * @var boolean true: 初期化済み、false: 未初期化
     */
    static private bool $_initialized = false;
    /**
     * エラーメッセージ
     *
     * @var string
     */
    static private string $_message = '';
    /**
     * インスタンス格納先
     *
     * @var [type]
     */
    protected static $instance = null;

    /**
     * インスタンスを取得する
     *
     * @return void
     */
    public static function &getInstance()
    {
        if (!self::$_initialized) {
            self::initialize();
        }
        if (empty(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * データベースの情報を設定する
     *
     * @return void
     * @throws Exception 対象の設定ファイルが存在しない場合に例外を発生
     */
    public static function initialize() : void
    {
        if (self::$_initialized) {
            return;
        }
        /**
         * 共通データベース設定読み込み
         */
        $file = ROOT_PATH . "/config" . CONFIG_DIR[ENVEROMENT] . "/databases.php";
        if (!file_exists($file)) {
            throw new Exception('No Exist Common Database Configure File !');
        }
        require_once $file; 
        foreach ($databases as $key => $value) {
            if (!empty($value) && ($key == 'default' || $value['status'])) {
                $value['category'] = DB_COMMON;
                self::$_dbInfos[$key] = $value;
            }
        }
        $databases = [];

        /**
         * 一般用データベース設定読み込み
         */
        $file = APP_PATH . "/config" . CONFIG_DIR[ENVEROMENT] . "/databases.php";
        if (!file_exists($file)) {
            throw new Exception('No Exist General Database Configure File !');
        }
        require_once $file; 
        foreach ($databases as $key => $value) {
            if (!empty($value) && $key != 'default' && $value['status']) {
                $value['category'] = DB_GENERAL;
                self::$_dbInfos[$key] = $value;
            }
        }
        $databases = [];

        /**
         * 管理者用データベース設定読み込み
         */
        if (ADMIN_MODE) {
            $file = PWF_ADMIN_PATH . "/config" . CONFIG_DIR[ENVEROMENT] . "/databases.php";
            if (!file_exists($file)) {
                throw new Exception('No Exist Admin Database Configure File !');
            }
            require_once $file; 
            foreach ($databases as $key => $value) {
                if (!empty($value) && $key != 'default' && $value['status']) {
                    $value['category'] = DB_ADMIN;
                    self::$_dbInfos[$key] = $value;
                }
            }
        }

        self::$_initialized = true;
    }

    /**
     * クラス内部発の初期化
     *
     * @return void
     */
    private static function doInitilize() : void 
    {
        if (!self::$_initialized) {
            self::initialize();
        }
    }

    /**
     * 指定したデータベースの情報を取得する
     *
     * @param string $dbName データベース名
     * @return array 成功した場合は指定したデータベースの情報を返す。 存在しないデータベースの場合は空配列を返す
     */
    public static function getDbInfo($dbName = 'default') : array
    {
        self::doInitilize();
        if (array_key_exists($dbName, self::$_dbInfos)) {
            return self::$_dbInfos[$dbName];
        }
        return [];
    }

    /**
     * 指定したデータベースのタイプを取得する
     *
     * @param  string $dbName データベース名
     * @return string データベースのタイプを返す。指定したデータベースが存在しない場合は空を返す
     */
    public static function getDBType(string $dbName = 'default') : string
    {
        self::doInitilize();
        if (array_key_exists($dbName, self::$_dbInfos)) {
            return self::$_dbInfos[$dbName]['type'];
        }
        return '';
    }

    /**
     * データベース名を取得する
     *
     * @param  string $dbName データベース名
     * @return string データベース名を返す。指定したデータベースが存在しない場合は空を返す
     */
    public static function getDBSchemaName(string $dbName = 'default') : string
    {
        self::doInitilize();
        if (array_key_exists($dbName, self::$_dbInfos)) {
            return self::$_dbInfos[$dbName]['database'];
        }
        return '';
    }

    /**
     * 全てのデータベースの情報を取得する
     *
     * @return array
     */
    public static function getAllDbInfos() : array
    {
        self::doInitilize();
        return self::$_dbInfos;
    }

    /**
     * データベースのコネクションを取得する
     *
     * @param string $dbName
     * @return PDO 接続に成功した場合はPDOインスタンスを返す。失敗した場合は null を返す 
     */
    public static function &getDBConnection(string $dbName = 'default') : PDO
    {
        self::doInitilize();
        if (!array_key_exists($dbName, self::$_dbInfos)) {
            self::$_message = 'DB(' . $dbName . ') do not fount in DB Infomation !!';
            return null;
        }
        // すでにコネクションが貼られている場合は、それを返す
        if (array_key_exists($dbName, self::$_dbStrage) && !empty(self::$_dbStrage[$dbName])) {
            return self::$_dbStrage[$dbName];
        }
        // DBの接続を実施
        try {
            // SQLIte対応
            if (self::$_dbInfos[$dbName]['type'] == 'sqlite') {
                self::$_dbStrage[$dbName] = new PDO(
                    'sqlite:' . self::$_dbInfos[$dbName]['filename']
                );
                return self::$_dbStrage[$dbName];
            }
            // それ以外の一般的なRDB
            $dsn = DBHelper::makeDsn(self::$_dbInfos[$dbName]);
            self::$_dbStrage[$dbName] = new PDO(
                $dsn,
                self::$_dbInfos[$dbName]['user'],
                self::$_dbInfos[$dbName]['password'],
                self::$_dbInfos[$dbName]['options'],
            );
            return self::$_dbStrage[$dbName];

        } catch (\PDOException $e) {
            self::$_message = $e->getMessage();
            self::$_dbStrage[$dbName] = null;
            return null;
        }
    }

    /**
     * データベースのコネクションを取得する
     *
     * @param array $dbInfo
     * @return PDO
     */
    public static function getDBConnectByInfo(array $dbInfo) : PDO
    {
        $targetDbName = '';
        if (array_key_exists('host', $dbInfo)) {
            foreach (self::$_dbInfos as $dbName => $innerDbInfo) {
                if (strcmp($dbInfo['host'], $innerDbInfo['host']) === 0 && strcmp($dbInfo['database'], $innerDbInfo['database']) === 0) {
                        $targetDbName = $dbName;
                    break;
                }
            }
        }
        // 対象のDBあり
        if (!empty($targetDbName)) {
            return self::getDBConnection($targetDbName);
        }
        // 対象のDBなし
        // SQLite
        if ($dbInfo['type'] == 'sqlite' || $dbInfo['type'] == STRAGE_TYPE_SQLITE) {
            return new PDO('sqlite:' . $dbInfo['filename']);
        }
        // MySQL, PGSql
        $dsn = DBHelper::makeDsn($dbInfo);
        return new PDO(
            $dsn,
            $dbInfo['user'],
            $dbInfo['password'],
            $dbInfo['options']
        );
    }

    /**
     * エラーメッセージを取得する
     *
     * @return string
     */
    public static function getErrorMessage() : string
    {
        return self::$_message;
    }
}