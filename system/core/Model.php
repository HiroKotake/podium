<?php
/**
 * Model.php
 * 
 * @category  Core
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\core;

use system\database\dbStrage;
use system\database\dbCommon;
use system\exception\DBException;

/**
 * Model class
 * 
 * @category Core
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class Model
{
    /**
     * データベース格納オブジェクト
     *
     * @var dbCommon
     */
    protected dbStrage $dbStrage;
    /**
     * データベースコネクション
     *
     * @var dbCommon
     */
    protected dbCommon $db;

    /**
     * コンストラクタ
     *
     * @param string $dbName 接続するデータベース名 (省略時"default")
     * @throws DBException データベースが存在しない場合は例外を発生させる
     */
    function __construct(string $dbName = 'default')
    {
        $this->dbStrage = new dbStrage();
        if (!$this->dbStrage->exists($dbName)) {
            throw new DBException('Database not found !!');
        }
        $this->db = $this->dbStrage->$dbName;
    }

    /**
     * コンストラクタで指定した以外のデータベースのDBコネクションを取得する
     *
     * @param string $name データベース名
     * @return dbCommon|false 指定したデータベースが存在する場合はdbCommonインスタンスを返し、存在しない場合はfalseを返す
     */
    public function getConnection(string $name)
    {
        if ($this->dbStrage->exists($name)) {
            return $this->dbStrage->$name;
        }
        return false;
    }
}