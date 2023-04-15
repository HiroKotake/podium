<?php
/**
 * DBSessionHandler.php
 * 
 * @category  Cache
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\session;

use PDO;
use system\exception\DBException;
use system\database\DBConnector;
use system\supports\DBHelper;

/**
 * DBSessionHandler Class
 * 
 * @category Cache
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
 class DBSessionHandler implements \SessionHandlerInterface
 {
    const DB_SESSION_ONLY = true;
    const DB_SESSION_COMMON = false;
    /**
     * データベース接続情報
     *
     * @var array
     */
    private array $_dbInfo;
    /**
     * セッション有効時間
     *
     * @var integer
     */
    private int $_expire;
    /**
     * データベースコネクション
     *
     * @var [type]
     */
    private \PDO $_conn;
    /**
     * 接続するデータベースはセッションだけで使用しているかを設定
     *
     * @var bool
     */
    private bool $_dbSessionOnly = self::DB_SESSION_ONLY;

    /**
     * コンストラクタ
     *
     * @param array $connectionInfo データベースへの接続情報
     * @param integer $expire セッション有効時間 (デフォルト値：3600秒)
     */
    function __construct(array $connectionInfo, $expire = 3600)
    {
        $this->_dbInfo = $connectionInfo;
        $this->_expire = $expire;
    }

    /**
     * データベースとの接続を開く
     *
     * @param string $path
     * @param string $name
     * @return boolean
     */
    public function open($path, $name): bool
    {
        // すでに接続済みか
        if (!empty($this->_conn)) {
            return true;
        }
        // すでに起動時に接続除法が設定済みか確認し、
        // 接続済みなら、そのコネクションを利用する
        $dbInfos = DBConnector::getAllDbInfos();
        foreach ($dbInfos as $key => $value) {
            if ($value['host'] == $this->_dbInfo['host'] && $value['database'] == $this->_dbInfo['database']) {
                // 接続済みのコネクションを設定する
                $this->_conn = DBConnector::getDBConnection($key);
                $this->_dbSessionOnly = self::DB_SESSION_COMMON;
                return true;
            }
        }
        // 起動時に設定されているDB以外に指定されている場合、
        // 新しく接続を実施する
        try {
            // 接続を実施
            $dsn = DBHelper::makeDsn($this->_dbInfo);
            $this->_conn = new PDO(
                $dsn,
                $this->_dbInfo['user'],
                $this->_dbInfo['password'],
                $this->_dbInfo['options'],
            );
            $this->_dbSessionOnly = self::DB_SESSION_ONLY;
            return true;
        } catch (\PDOException $e) {
            $msg = $e->getMessage();
            throw new DBException($msg);
        }
    }

    /**
     * セッションを閉じる
     *
     * @return boolean
     */
    public function close(): bool
    {
        if ($this->_dbSessionOnly == self::DB_SESSION_ONLY) {
            unset($this->_conn);
        }
        return true;
    }

    /**
     * セッションを破棄する
     *
     * @param string $id セッションID
     * @return boolean
     */
    public function destroy($id): bool
    {
        $sql = "DELETE FROM `pSession` WHERE `Id` = ?";
        $pdoStatement = $this->_conn->prepare($sql);
        if ($pdoStatement->execute(array($id))) {
            return true;
        }
        return false;
    }

    /**
     * 古いセッションを破棄する
     * Expireが現在時刻より前のレコードを一括削除する
     *
     * @param integer $max_lifetime 更新されていない間隔 （使用しません）
     * @return void
     */
    public function gc($max_lifetime)
    {
        $now = date('Y-m-d H:i:s');
        $sql = 'DELETE FROM `pSession` WHERE `Expire` < ?';
        $pdoStatement = $this->_conn->prepare($sql);
        if ($pdoStatement->execute(array($now))) {
            return true;
        }
        return false;
    }

    /**
     * セッションのデータを読み込む
     *
     * @param string $id セッションID
     * @return void
     */
    public function read($id)
    {
        $sql = "SELECT `Data` FROM `pSession` WHERE `Id` = ?";
        $pdoStatement = $this->_conn->prepare($sql);
        if ($pdoStatement->execute(array($id))) {
            if ($lines = $pdoStatement->fetch(\PDO::FETCH_ASSOC)) {
                return $lines['Data'];
            }
        }
        return '';
    }

    /**
     * セッションのデータを書き込む
     *
     * @param string $id セッションID
     * @param string $data セッションに書き込むデータ
     * @return boolean
     */
    public function write($id, $data): bool
    {
        $dateTime = date('Y-m-d H:i:s');
        $now = strtotime($dateTime);
        $expiretTime = date('Y-m-d H:i:s', $now + $this->_expire);
        // DBでのSQLの違いを吸収するため、一般的なSQL文で記述する
        // 既存レコード確認
        $recode = $this->read($id);
        $sql = '';
        $sqlData = null;
        if (!empty($recode)) {
            // 更新
            $sql  = "UPDATE `pSession` ";
            $sql .= "SET `IpAddress`=?, `Timestamp`=?, `Expire`=?, `Data`=?";
            $sql .=  "WHERE `Id`=?";
            $sqlData = array(
                $_SERVER['REMOTE_ADDR'],
                $dateTime,
                $expiretTime,
                $data,
                $id
            );
        } else {
            // 挿入
            $sql  = "INSERT INTO `pSession`";
            $sql .= "(`Id`,`IpAddress`,`Timestamp`,`Expire`,`Data`) ";
            $sql .= "VALUE (?, ?, ?, ?, ?)";
            $sqlData = array(
                $id,
                $_SERVER['REMOTE_ADDR'],
                $dateTime,
                $expiretTime,
                $data
            );
        }
        // SQL実行
        if ($pdoStatement = $this->_conn->prepare($sql)) {
            if ($pdoStatement->execute($sqlData)) {
                return true;
            }
        }
        return false;
    }
}