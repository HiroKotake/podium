<?php
/**
 * dbStarge.php
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
use system\database\dbCommon;
use system\database\DBConnector;
use system\supports\dynamicStruct;
use system\exception\DBException;

/**
 * dbStrage
 * データベース関連のクラスを格納するクラス
 * 
 * DBの設定に基づき複数のDBへの接続を行い、各種の処理を行う。
 * 設定に default と指定している場合は直接このクラス内で定義されているメソッドを実行することができる。
 * それ以外の DB　接続の場合は、db名を指定することでメソッド実行ができる。
 * @example dbStrag->search(...)　　<- default指定されているデータベースに対する処理
 * @example dbStrage->subdb->search(...) <- subdbデータベースに対する処理 
 * 
 * @category Database
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class dbStrage extends dbCommon
{
    private dynamicStruct $_dbChamber;
    private array $_dbInfos = [];

    /**
     * コンストラクタ
     */
    function __construct()
    {
        parent::__construct('default');
        $this->_dbChamber = new dynamicStruct();
        $this->init();
    }

    /**
     * デストラクタ
     */
    function __destruct()
    {
        $this->close(); // DBとの接続を終了する
        // _dbChamberに格納されているDBの接続を終了させる
        foreach ($this->_dbChamber as $key => $value) {
            $value->close();
            unset($this->_dbChamber[$key]);
        }
    }

    /**
     * データベースクラスを返す
     *
     * @param string $name データベース名
     * @return dbCommon 対象が定義されている場合はdbCommonクラスを返す
     * @throws DBException
     */
    function __get(string $name) : dbCommon
    {
        if ($name == 'default') {
            return $this;
        }
        if (!$this->_dbChamber->exists($name)) {
            throw new DBException('No Define Database!!');
        }
        return $this->_dbChamber->$name;
    }
    
    /**
     * データベースの設定を反映させ、利用可能状態にする
     *
     * @return void 戻り値無し
     */
    public function init()
    {
        $this->_dbInfos = DBConnector::getAllDbInfos();
        foreach (array_keys($this->_dbInfos) as $key) {
            if ($key == 'default') {
                continue;
            }
            $this->_dbChamber->$key = new dbCommon($key);
        }
    }

    /**
     * DBが存在するか確認する
     *
     * @param string $name
     * @return boolean 存在する場合はtrueを、存在しない場合はfalseを返す
     */
    public function exists(string $name) : bool
    {
        if ($name == 'default') {
            return true;
        }
        return $this->_dbChamber->exists($name);
    }

    /**
     * 設定されているデータベースの接続情報を取得する
     *
     * @return array データベースの接続情報を含む配列
     */
    public function getDbInfos() : array
    {
        return $this->_dbInfos;
    }
}