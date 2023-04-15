<?php
/**
 * CacheRedis.php
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

use system\cache\CacheInterface;

/**
 * CacheRedisBase Class
 * 
 * @category Cache
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class CacheRedis implements CacheInterface
{
    /**
     * キャッシュ接続設定情報
     *
     * @var array
     */
    private array $_cacheInfo;
    /**
     * Redisインスタンス
     *
     * @var \Redis
     */
    private \Redis $_redis;
    /**
     * 現在の接続DB番号
     * 
     * @var integer DB番号
     */
    private $_dbNumber = 0;
    /**
     * 接続状況
     *
     * @var boolean 接続していればtrueを、していなければfalseを設定
     */
    private bool $_fConnection = self::CACHE_CONNECTION_OFF;

    /**
     * コンストラクタ
     *
     * @param array $redisInfo キャッシュ接続情報を含む配列
     */
    function __construct(array $cacheInfo = [])
    {
        $this->_cacheInfo = $cacheInfo;   
        $this->_redis = new \Redis();
    }

    /**
     * デストラクタ
     */
    function __destruct()
    {
        $this->close();
    }

    /**
     * 指定されたキー名のもつ値を取得する
     *
     * @param string $name キー名
     * @return void 値
     */
    function __get(string $name)
    {
        if (!$this->_fConnection) {
            if (!$this->connect()) {
               throw new \Exception('Cache Server cant\'t connect !!'); 
            };
        }
        if (!$this->isExist($name)) {
            throw new \Exception($name . ' is not exist in Cache !!');
        }
        return unserialize($this->_redis->get($name));
    }

    /**
     * 値を設定する
     *
     * @param string $name キー名
     * @param mix $value 値
     */
    function __set(string $name, $value)
    {
        if (!$this->_fConnection) {
            if (!$this->connect()) {
               throw new \Exception('Cache Server cant\'t connect !!'); 
            };
        }
        $this->_redis->set($name, serialize($value));    
    }

    /**
     * キャッシュ情報を設定する
     *
     * @param array $cacheInfo
     * @return void
     */
    public function setCacheInfo(array $cacheInfo) : void
    {
        $this->_cacheInfo = $cacheInfo;
    }

    /**
     * キャッシュオブジェクトを返す
     *
     * @return object Redisオブジェクト
     */
    public function getConnection() : object
    {
        if (!$this->_fConnection) {
            $this->connect();
        }
        return $this->_redis;
    }

    /**
     * 接続を実施する
     *
     * @param boolean $cont 持続的接続にする場合はtrueを、一時的接続の場合はfalseを設定する (省略時:true)
     * @return boolean 接続に成功したらtrueを、失敗したらfalseを返す
     */
    public function connect(bool $cont = true) : bool
    {
        if ($this->_fConnection) {
            return $this->_fConnection;
        }
        if ($cont) {
            $result = $this->_redis->pconnect(
                $this->_cacheInfo['host'], 
                $this->_cacheInfo['port'], 
                $this->_cacheInfo['timeout']
            );
        } else {
            $result = $this->_redis->connect(
                $this->_cacheInfo['host'], 
                $this->_cacheInfo['port'], 
                $this->_cacheInfo['timeout']
            );
        }
        $this->_fConnection
            = $result ? self::CACHE_CONNECTION_ON : self::CACHE_CONNECTION_OFF;
        if ($this->_fConnection && $this->_cacheInfo['user']) {
            // 認証
            $this->_redis->auth(
                $this->_cacheInfo['user'],
                $this->_cacheInfo['password']
            );
        }
        return $result;
    }

    /**
     * 接続を切る
     *
     * @return boolean 接続を切れたらtureを、切れない場合はfalseを返す
     */
    public function close() : bool
    {
        if (!$this->_fConnection) {
            return true;
        }
        $result = $this->_redis->close();
        $this->_fConnection
            = $result ? self::CACHE_CONNECTION_ON : self::CACHE_CONNECTION_OFF;
        return $result; 
    }
    
    /**
     * キーが存在するか確認する
     *
     * @param string $name キー名
     * @return boolean キーが存在する場合はtrueを、存在しない場合はfalseを返す
     */
    public function isExist(string $name) : bool
    {
        if (!$this->_fConnection) {
            return false;
        }
        return $this->_redis->exists($name) ? true : false;
    }

    /**
     * Redisのデータベースを変更する
     *
     * @param integer $db データベース番号
     * @return boolean 変更に成功したらtrueを、失敗したらfalseを返す
     */
    public function change(int $db) : bool
    {
        if (!$this->_fConnection) {
            return false;
        }
        $result = $this->_redis->select($db);
        $this->_dbNumber = $result ? $db : $this->_dbNumber;
        return $result;
    }

    /**
     * Redisの情報を取得する
     *
     * @param string|null $name 取得したい情報名を指定する。指定しない場合は全情報を取得する
     * server: General information about the Redis server
     * clients: Client connections section
     * memory: Memory consumption related information
     * persistence: RDB and AOF related information
     * stats: General statistics
     * replication: Master/replica replication information
     * cpu: CPU consumption statistics
     * commandstats: Redis command statistics
     * latencystats: Redis command latency percentile distribution statistics
     * cluster: Redis Cluster section
     * modules: Modules section
     * keyspace: Database related statistics
     * modules: Module related sections
     * errorstats: Redis error statistics
     * @return array|false 情報をとれた場合は情報を含む配列を、失敗した場合はfalseを返す
     */
    public function info(string $name = null)
    {
        if (!$this->_fConnection) {
            return false;
        }
        return $name ? $this->_redis->info($name) : $this->_redis->info();
    }
}