<?php
/**
 * CacheStrage.php
 * 
 * @category  Cache
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\cache;

use system\supports\dynamicStruct;
use system\cache\CacheInfomation;

/**
 * CacheStrage Class
 * 
 * @category Cache
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
 class CacheStrage
 {
    private dynamicStruct $_strage;
    private array $_cacheInfos;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        $this->_strage = new dynamicStruct();
        //
        $caches = CacheInfomation::getAllCacheInfos();
        $this->_setChamber($caches);
    }

    /**
     * デストラクタ
     */
    function __destruct()
    {
        foreach ($this->_strage as $key => $obj) {
            $obj->close();
            unset($this->_strage[$key]);
        }
    }

    /**
     * 値もしくはキャッシュオブジェクトを取得する
     * 引数で指定したものがキャッシュオブジェクトであるか確認し、そうである場合キャッシュオブジェクトを返し、
     * そうでない場合は、キャッシュに格納されている値を返す
     * 注意）　上記の仕様になっているので、キャッシュサーバ名とキー名に同じものを使用しないで下さい
     *
     * @param string $name キー名
     * @return void
     */
    function __get(string $name)
    {
        if ($this->_strage->exists($name)) {
            return $this->_strage->$name;
        } 
        return $this->_strage->default->$name; 
    }

    /**
     * 値を設定する
     * defaultに指定されているキャッシュサーバに値を設定する
     *
     * @param string $name
     * @param [type] $value
     */
    function __set(string $name, $value)
    {
        $this->_strage->default->$name = $value;
    }

    /**
     * キャッシュオブジェクトを格納する
     *
     * @param array $caches キャッシュオブジェクトを含む配列
     * @return void
     */
    private function _setChamber(array &$caches) : void
    {
        if (empty($caches) || !is_array($caches)) {
            return;
        }
        foreach ($caches as $key => $value) {
            if ($value['status']) {
                $className = 'system\cache\Cache' . ucfirst($value['type']);
                $this->_strage->$key = new $className($value);
                $this->_cacheInfos[$key] = $value;
            }
        }
    }

    /**
     * 指定したキャッシュサーバから値を取得する
     *
     * @param string $name キー名
     * @param string $server キャッシュサーバ名(省略時：default)
     * @return mix 指定したキャッシュサーバが存在し、かつキー名が存在した場合は対応する値を返す。それ以外はfalseを返す
     *             値にfalseを設定している場合に注意が必要 
     */
    public function get(string $name, string $server = 'default')
    {
        if (!$this->_strage->exists($name)) {
            return false;
        }
        return $this->_strage->$server->$name;
    }

    /**
     * 指定したキャッシュサーバに値を設定する
     *
     * @param string $name キー名
     * @param mix $value 設定する値
     * @param string $server キャッシュサーバ名(省略時：default)
     * @return void
     */
    public function set(string $name, $value, $server = 'default') : void
    {
        $this->_strage->$server->$name = $value;
    }

    /**
     * キャッシュコネクションを取得する
     *
     * @param string $name キャッシュサーバ名
     * @return Object|false 指定したキャッシュサーバ名が存在しているならばコネクションを返し、存在していないならばfalseを返す
     */
    public function getConnection($name = 'default') {
        if (!$this->_strage->exists($name)) {
            return false;
        }
        return $this->_strage->$name->getConnection();
    }

    /**
     * 設定されているキャッシュサーバの接続情報を取得する
     *
     * @return array キャッシュサーバの接続情報を含む配列
     */
    public function getCacheInfos() : array
    {
        return $this->_cacheInfos;
    }
 }