<?php
/**
 * CacheInformation.php
 * 
 * @category  Configure
 * @package   Cache
 * @author    Takahiro Kotake <tkotake@teleios.com>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\cache;

use \Exception;

/**
 * CacheInfomation Class
 * 
 * @category Configure
 * @package  Cache
 * @author   Takahiro Kotake <tkotake@teleios.com>
 * 
 */
class CacheInfomation
{

    /**
     * キャッシュ情報
     *
     * @var array
     */
    static private array $_cacheInfos = [];
    /**
     * 初期化済みフラグ
     *
     * @var boolean
     */
    static private bool $_initialized = false;
    /**
     * エラー情報
     *
     * @var string
     */
    static private string $_message = '';

    /**
     * キャッシュ情報初期化（設定読み込み)
     *
     * @return void
     */
    public static function initialize() : void
    {
        /**
         * 初期化済みか確認し、初期化済みなら何もしない
         */
        if (self::$_initialized) {
            return;
        }
        /**
         * 共通キャッシュ読み込み
         */
        $file = ROOT_PATH . 'config' . CONFIG_DIR[ENVEROMENT] . '/caches.php';
        if (!file_exists($file)) {
            throw new Exception('No Exist Common Cache Configure File !');
        }
        require $file;  // ToDo:なぜか require_once で動かん？
        foreach ($caches as $key => $value) {
            if (!empty($value) && ($key == 'default' || $value['status'])) {
                $value['category'] = CACHE_COMMON;
                self::$_cacheInfos[$key] = $value;
            }
        }
        $caches = [];
        /**
         * 一般向けキャッシュ読み込み
         */
        $file = APP_PATH . 'config' . CONFIG_DIR[ENVEROMENT] . '/caches.php';
        if (!file_exists($file)) {
            throw new Exception('No Exist Common Cache Configure File !');
        }
        require $file;  // ToDo:なぜか require_once で動かん？
        foreach ($caches as $key => $value) {
            if (!empty($value) && $key != 'default' && $value['status']) {
                $value['category'] = CACHE_GENERAL;
                self::$_cacheInfos[$key] = $value;
            }
        }
        $caches = [];
        /**
         * 管理者向けキャッシュ読み込み
         */
        if (ADMIN_MODE) {
            $file = PWF_ADMIN_PATH . 'config' . CONFIG_DIR[ENVEROMENT] . '/caches.php';
            if (!file_exists($file)) {
                throw new Exception('No Exist Common Cache Configure File !');
            }
            require $file;  // ToDo:なぜか require_once で動かん？
            foreach ($caches as $key => $value) {
                if (!empty($value) && $key != 'default' && $value['status']) {
                    $value['category'] = CACHE_ADMIN;
                    self::$_cacheInfos[$key] = $value;
                }
            }
            $caches = [];
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
     * 指定したキャッシュの情報を取得する
     *
     * @param string $cacheName キャッシュ名
     * @return array
     */
    public static function getCacheInfo($cacheName = 'default') : array
    {
        self::doInitilize();
        return self::$_cacheInfos[$cacheName];
    }

    /**
     * 全てのキャッシュの情報を取得する
     *
     * @return array
     */
    public static function getAllCacheInfos() : array
    {
        self::doInitilize();
        return self::$_cacheInfos;
    }
}