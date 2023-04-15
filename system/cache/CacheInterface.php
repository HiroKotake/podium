<?php
/**
 * CacheInterface.php
 * 
 * @category  Cache
 * @package   Interface
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\cache;

/**
 * CacheInterface interface
 * 
 * @category Cache
 * @package  Interface
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
interface CacheInterface
{
    const CACHE_CONNECTION_ON = true;
    const CACHE_CONNECTION_OFF = false;

    /**
     * コンストラクタ
     *
     * @param array $redisInfo キャッシュ接続情報を含む配列
     */
    function __construct(array $cacheInfo = []);
    /**
     * デストラクタ
     */
    function __destruct();
    /**
     * 指定されたキー名のもつ値を取得する
     *
     * @param string $name キー名
     * @return void 値
     */
    function __get(string $name);
    /**
     * 値を設定する
     *
     * @param string $name キー名
     * @param mix $value 値
     */
    function __set(string $name, $value);
    /**
     * キャッシュ情報を設定する
     *
     * @param array $cacheInfo
     * @return void
     */
    public function setCacheInfo(array $cacheInfo) : void;
    /**
     * キャッシュオブジェクトを返す
     *
     * @return object Redisオブジェクト
     */
    public function getConnection() : object;
    /**
     * 接続を実施する
     *
     * @param boolean $cont 持続的接続にする場合はtrueを、一時的接続の場合はfalseを設定する (省略時:true)
     * @return boolean 接続に成功したらtrueを、失敗したらfalseを返す
     */
    /**
     * 接続を実施する
     *
     * @param boolean $cont 持続的接続にする場合はtrueを、一時的接続の場合はfalseを設定する (省略時:true)
     * @return boolean 接続に成功したらtrueを、失敗したらfalseを返す
     */
    public function connect(bool $cont = true) : bool;
    /**
     * 接続を切る
     *
     * @return boolean 接続を切れたらtureを、切れない場合はfalseを返す
     */
    public function close() : bool;
}
