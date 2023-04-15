<?php
/**
 * ModelMigrate.php
 * 
 * @category  Model 
 * @package   Migrate
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\models;

use system\core\Model;
use system\exception\DBException;

/**
 * ModelMigrate Class
 * 
 * @category Model 
 * @package  Migrate
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class ModelMigrate extends Model
{
    /**
     * 接続するデータベース名
     * config/database.phpで設定しているデータベース名
     */
    protected string $dbName = 'default';

    protected string $errorMessage = '';

    /**
     * Constructer
     */
    function __construct(string $dbName = '')
    {
        if (!empty($dbName)) {
            $this->dbName = $dbName;
        }
        parent::__construct($this->dbName);
    }

    /**
     * SQLクエリーを実行する
     * SQLクエリーは実行結果を望まないものを実行する。
     *
     * @param  string $query SQLクエリー文
     * @return boolean 作成に成功したらtrueを、失敗したらfalseを返す
     */
    public function execQuery(string $query) : bool
    {
        $result = true;
        try {
            $conn = $this->db->getConnection();
            $result = $conn->exec($query);
            if ($result === false) {
                $errorInfo = $conn->errorInfo();
                $this->errorMessage = $errorInfo[2];
            } else {
                $result = true;
            }
        } catch (DBException $e) {
            $this->errorMessage = $e->getMessage();
        }
        return $result;
    }

    /**
     * エラーが発生した時のエラーメッセージを取得する
     *
     * @return string エラーメッセージ
     */
    public function getErrMessage() : string
    {
        return $this->errorMessage;
    }

}