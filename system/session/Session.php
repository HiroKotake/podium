<?php
/**
 * Session.php
 * 
 * @category  Configure
 * @package   Session 
 * @author    Takahiro Kotake <tkotake@teleios.com>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 */
namespace system\session;

use Exception;
use SessionHandlerInterface;
use system\session\DBSessionHandler;

/**
 * Session Class
 * 
 * @category Configure
 * @package  Session 
 * @author   Takahiro Kotake <tkotake@teleios.com>
 */
class Session
{
    /**
     * CLASS CONST
     */
    const SESSION_FILE = "FILE";
    const SESSION_DATABASE = "DATABASE";
    const SESSION_CACHE = "CACHE";
    const SESSION_ON = true;
    const SESSION_OFF = false;

    /**
     * セッション状態
     *
     * @var boolean
     */
    private static bool $_SessionFlag = self::SESSION_OFF;
    /**
     * セッションの設定
     *
     * @var array セッションの設定を含む配列
     */
    private static array $_SessionInfo = [];
    /**
     * エラーメッセージ
     *
     * @var string
     */
    private static string $_ErrorMessage = '';
    /**
     * インスタンス格納先
     *
     * @var [type]
     */
    private static $_instance = null;

    /**
     * インスタンスを取得する
     *
     * @return void
     */
    public static function &getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * セッションに値を設定する
     *
     * @param string $name キー名
     * @param [type] $value 値
     * @return void
     */
    public static function set(string $name, $value) : void
    {
        if (self::_CheckOpened()) {
            $_SESSION[$name] = $value;
        }
    }

    /**
     * __set
     * (getInstance()でインスタンスを取得した後で利用可能)
     *
     * @param string $key 変数名
     * @param [type] $value 値
     */
    public function __set(string $key, $value)
    {
        self::set($key, $value);
    }

    /**
     * セッションから値を取得する
     *
     * @param string $name キー名
     * @return void
     * @throws Exception キー名が存在しない場合は例外を発生させる
     */
    public static function get(string $name)
    {
        if (self::_CheckOpened()) {
            if (array_key_exists($name, $_SESSION)) {
                return $_SESSION[$name];
            }
        }
        return null;
    }

    /**
     * __get
     * (getInstance()でインスタンスを取得した後で利用可能)
     *
     * @param string $key 変数名
     * @return void
     */
    public function __get(string $key)
    {
        return self::get($key);
    }

    /**
     * 指定した変数が設定されているか確認する
     *
     * @param string $name 確認する変数名
     * @return boolean 設定されている場合はtrueを、されていない場合はfalseを返す
     */
    public static function isset($name) : bool
    {
        if (self::_CheckOpened()) {
            return isset($_SESSION[$name]);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @return boolean
     */
    public function __isset(string $name)
    {
        return self::isset($name);
    }

    /**
     * 指定した変数を解放する
     *
     * @param string $name 解放する変数名
     * @return void
     */
    public static function unset(string $name): void
    {
        if (self::_CheckOpened()) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $name
     */
    public function __unset(string $name)
    {
        self::unset($name);
    }

    /**
     * セッションが開いているか確認
     *
     * @return boolean true: 開いている, false:閉じている
     */
    public static function isInitialized() : bool
    {
        return self::$_SessionFlag;
    }

    /**
     * セッションを確認し、開いていなければセッションを開く
     *
     * @return void
     */
    private static function _CheckOpened()
    {
        if (!self::$_SessionFlag) {
            return self::open();
        }
        return true;
    }

    /**
     * 設定ファイルを読み込む
     *
     * @return void
     * @throws Exception 設定ファイルが存在しない場合に例外を発生させる
     */
    private static function _LoadConfigure()
    {
        $file = ROOT_PATH . 'config' . CONFIG_DIR[ENVEROMENT] . '/session.php';
        if (file_exists($file)) {
            require $file;
            self::$_SessionInfo = $session;
            return true;
        }
        throw new Exception('Session Configure File is not Found !!');
    }

    /**
     * セッションを利用可能にする
     *
     * @return void
     */
    public static function open()
    {
        if (self::$_SessionFlag) {
            return true;
        }
        try {
            // 設定を読み込む
            self::_LoadConfigure();
            // セッションの格納先に応じて設定を変更
            if (!preg_match('/file/i', self::$_SessionInfo['type'])) {
                // 設定を変更
                $sessionHandler = self::_SetSessionStrage(self::$_SessionInfo['type']);
                if ($sessionHandler !== self::SESSION_FILE) {
                    session_set_save_handler($sessionHandler, true);
                }
            }
            // セッションを開始する
            if (!self::$_SessionFlag) {
                self::$_SessionFlag = session_start();
                // セッション開始確認
                if (!self::$_SessionFlag) {
                    self::$_ErrorMessage = 'Failed Session Start!!';
                }
            }
            return self::$_SessionFlag;

        } catch (Exception $e) {
            self::$_ErrorMessage = $e->getMessage();
            return false;
        }
    }

    /**
     * 指定した引数に対応したセッションハンドラーインターフェイスを返す
     *
     * @param string $storeSype 格納先タイプ
     * @return \SessionHandlerInterface
     */
    private static function _SetSessionStrage(string $storeType) : \SessionHandlerInterface
    {
        $cfgStoreType = strtoupper($storeType);
        switch ($cfgStoreType) 
        {
        case self::SESSION_DATABASE:
            // データベースに設定
            return new DBSessionHandler(self::$_SessionInfo['database'], self::$_SessionInfo['expire']);
            break;
        case self::SESSION_CACHE:
            // キャッシュに設定
            break;
        default:
            return self::SESSION_FILE;
        }
    }

    /**
     * セッションを閉じる
     *
     * @return boolean 正常に閉じた場合もしはすでに閉じている場合はtrueを、失敗した場合はfalseを返す
     */
    public static function close() : bool
    {
        if (self::$_SessionFlag) {
            self::$_SessionFlag = self::SESSION_OFF;
            unset($_SESSION);
            return true;
        }
        return true; 
    }

    /**
     * セッションの格納先タイプを取得する
     *
     * @return string
     */
    public static function getStorageType() : string
    {
        $strageType = self::SESSION_FILE;
        if (!empty(self::$_SessionInfo)) {
            if (array_key_exists('type', self::$_SessionInfo)) {
                $strageType = self::$_SessionInfo['type'];
            }
        }
        return $strageType;
    }

    /**
     * セッションの有効時間
     *
     * @return integer セッションの有効時間(秒)
     */
    public static function getExpireTime() : int
    {
        $expire = 3600;
        if (!empty(self::$_SessionInfo)) {
            if (array_key_exists('expire', self::$_SessionInfo)) {
                $expire = self::$_SessionInfo['expire'];
            }
        }
        return $expire;
    }

    /**
     * 例外が発生した時点のエラーメッセージを取得する
     *
     * @return string エラーメッセージ
     */
    public static function getMessage() : string
    {
        return self::$_ErrorMessage;
    }
}