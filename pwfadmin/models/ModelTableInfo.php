<?php
/**
 * ModelTableInfo.php
 * 
 * @category  Model 
 * @package   Table
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\models;

use system\core\Model;
use system\database\DBConnector;

/**
 * ModelTableInfo Class
 * 
 * @category Model 
 * @package  Table
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class ModelTableInfo extends Model
{
    /**
     * テーブル一覧表示クエリー
     */
    const SHOW_TABLE_MYSQL = "SHOW TABLES";
    const SHOW_TABLE_PGSQL = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ";
    const SHOW_TABLE_SQLITE = "select name from sqlite_master where type='table'";

    /**
     * テーブル構成情報取得クエリー
     */
    const QUERY_TABLE_COLUMS = "SELECT
        ordinal_position AS 'OrdinalPosition',
        column_name AS 'ColumnName', 
        data_type AS 'DataType',
        character_maximum_length AS 'CharacterMaximumLength',
        numeric_precision AS 'NumericPrecision',
        is_nullable AS 'IsNullable', 
        column_default AS 'ColumnDefault',
        column_comment AS 'ColumnComment' 
        FROM information_schema.columns 
        WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :name 
        ORDER BY ORDINAL_POSITION
    ";

    /**
     * 接続するデータベース名
     * config/database.phpで設定しているデータベース名
     */
    protected string $dbName = 'default';

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
     * データベーススキーマのテーブル一覧を取得する
     *
     * @param string $database 検索するデータベーススキーマ名
     * @return array/false 成功した場合はテーブル名を含む配列を、失敗した場合はfalseを返す
     */
    public function showDatabase(string $database = 'default')
    {
        $dbType = DBConnector::getDBType($database);
        $query = '';
        switch ($dbType) {
        case 'mysql':
            $query = self::SHOW_TABLE_MYSQL;
            break;
        case 'pgsql':
            $query = self::SHOW_TABLE_PGSQL . $database;
            break;
        case 'sqlite':
            $query = self::SHOW_TABLE_SQLITE;
            break;
        default:
            return false;
        }
        $result = $this->db->query($query, false);
        if ($result) {
            return $result->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $result;
    }

    /**
     * 指定したテーブルの構成情報を取得する
     *
     * @param  string $tableName テーブル名
     * @return void 成功した場合は以下のデータ配列を含む配列を返す。
     *              [[
     *                  'OrdinalPosition' => <カラムの配置順>,
     *                  'ColumnName' => <カラム名>,
     *                  'DataType' => <データ型>,
     *                  'CharacterMaximumLength' => <文字の最大長>,
     *                  'NumericPrecision' => <数値の最大桁数>,
     *                  'IsNullable' => <nullを格納可能な場合は"Yes"を、出来ない場合は"No">,
     *                  'ColumnDefault' => <カラムのデフォルト値>,
     *                  'ColumnComment' => <カラムのコメント>
     *              ],...]
     *              失敗した場合はfalseを返す。
     */
    public function getShowColums(string $tableName)
    {
        if (DBConnector::getDBType($this->dbName) == 'sqlite') {
            return $this->getShowColumsAtSQLite($tableName);
        }
        $schema = DBConnector::getDBSchemaName($this->dbName);
        $data = [['schema' => $schema, 'name' => $tableName]];
        $result = $this->db->prepareSearch(self::QUERY_TABLE_COLUMS, $data);
        return $result;
    }

    /**
     * SQLiteで指定したテーブルの構成情報を取得する
     *
     * @param  string $tableName テーブル名
     * @return void 成功した場合は以下のデータ配列を含む配列を返す。
     *              [[
     *                  'OrdinalPosition' => <カラムの配置順>,
     *                  'ColumnName' => <カラム名>,
     *                  'DataType' => <データ型>,
     *                  'CharacterMaximumLength' => <null>,
     *                  'NumericPrecision' => <null>,
     *                  'IsNullable' => <nullを格納可能な場合は"Yes"を、出来ない場合は"No">,
     *                  'ColumnDefault' => <カラムのデフォルト値>,
     *                  'ColumnComment' => <null>
     *              ],...]
     *              失敗した場合はfalseを返す。
     */
    public function getShowColumsAtSQLite(string $tableName)
    {
        // SQL実行
        $query = "PRAGMA table_info('$tableName')";
        try {
            // SQL実行
            $result = $this->db->query($query);
            // 配列に変換
            $records = $result->fetchAll(\PDO::FETCH_ASSOC);
            $data = [];
            foreach ($records as $line) {
                $data[] = [
                    'OrdinalPosition' => $line['cid'],
                    'ColumnName' => $line['name'],
                    'DataType' => $line['type'],
                    'CharacterMaximumLength' => null,
                    'NumericPrecision' => null,
                    'IsNullable' => ($line['notnull'] = 1 ? "Yes" : "No"),
                    'ColumnDefault' => $line['dflt_value'],
                    'ColumnComment' => null,
                ];
            }
            return $data;
        } catch (\PDOException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * 指定したテーブルをリセットする (MySQL系列用)
     *
     * @param  string $table テーブル名
     * @param  boolean $resetNumber シーケンス番号をリセットするか true:リセットする. false:リセットしない
     * @return boolean 成功した場合は trueを、失敗した場合は falseを返す
     */
    public function truncate(string $table, bool $resetNumber = true) : bool
    {
        $dbType = DBConnector::getDBType($this->dbName);
        $result = false;
        switch ($dbType) {
        case 'mysql':
            $result = $this->truncateMySql($table, $resetNumber);
            break;
        case 'pgsql':
            $result = $this->truncatePgsql($table, $resetNumber);
            break;
        case 'sqlite':
            $result = $this->truncateSqlite($table, $resetNumber);
            break;
        default:
            $result = false;
        }
        return $result;
    }

    /**
     * 指定したテーブルをリセットする (MySQL系列用)
     *
     * @param  string $table テーブル名
     * @param  boolean $resetNumber シーケンス番号をリセットするか true:リセットする. false:リセットしない
     * @return boolean 成功した場合は trueを、失敗した場合は falseを返す
     */
    protected function truncateMySql(string $table, bool $resetNumber) : bool
    {
        $query = 'DELETE FROM ' . $table;
        if ($resetNumber) {
            $query = 'TRUNCATE TABLE ' . $table;
        }
        return $this->db->exec($query);
    }

    /**
     * 指定したテーブルをリセットする (PostgreSQL用)
     *
     * @param  string $table テーブル名
     * @param  boolean $resetNumber シーケンス番号をリセットするか true:リセットする. false:リセットしない
     * @return boolean 成功した場合は trueを、失敗した場合は falseを返す
     */
    protected function truncatePgsql(string $table, bool $resetNumber) : bool
    {
        $query = 'TRUNCATE TABLE ' . $table;
        if ($resetNumber) {
            $query .= ' restart identity';
        }
        return $this->db->exec($query);
    }

    /**
     * 指定したテーブルをリセットする (SQLite用)
     *
     * @param  string $table テーブル名
     * @param  boolean $resetNumber シーケンス番号をリセットするか true:リセットする. false:リセットしない
     * @return boolean 成功した場合は trueを、失敗した場合は falseを返す
     */
    protected function truncateSqlite(string $table, bool $resetNumber) : bool
    {
        //　テーブル初期化
        $result = $this->db->exec('DELETE FROM ' . $table);
        //　シーケンス番号リセット
        if ($result && $resetNumber) {
            $result = $this->db->exec('DELETE FROM sqlite_sequence WHERE name = ' . $table);
        }
        return $result;
    }
}