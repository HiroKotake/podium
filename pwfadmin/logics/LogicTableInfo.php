<?php
/**
 * LogicTableInfo.php
 * 
 * @category  Logic 
 * @package   Table
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\logics;

use pwfadmin\models\ModelTableInfo;
use system\database\DBConnector;

/**
 * LogicTableInfo Class
 * 
 * @category Logic 
 * @package  Table
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class LogicTableInfo 
{
    protected string $database;

    /**
     * Constructer
     */
    function __construct()
    {
    }

    public function getMaskedList(array $list) : array
    {
        $data = [];
        foreach ($list as $val) {
            $num = mt_rand(1000, 9999);
            $key = 'key_' . $num;
            while (array_key_exists($key, $data)) {
                $num = mt_rand(1000, 9999);
                $key = 'key_' . $num;
            }
            $data[$key] = $val;
        }
        return $data;
    }

    /**
     * データベーススキーマの一覧を取得する
     *
     * @return array
     */
    public function getSchemaList() : array
    {
        $dbList = DBConnector::getAllDbInfos();
        $list = array_keys($dbList);
        return $list;
    }

    /**
     * 指定したデータベーススキーマのテーブル一覧を取得する
     *
     * @param  string $database 検索するデータベーススキーマ名
     * @return array/false 成功した場合はテーブル名を含む配列を、失敗した場合はfalseを返す
     */
    public function getTableList(string $database = 'default') 
    {
        $database = $database ?: 'default';
        $mTableInfo = new ModelTableInfo($database);
        $result = $mTableInfo->showDatabase($database);
        if (!$result) {
            return false;
        }
        $list = [];
        foreach ($result as $rset) {
            foreach ($rset as $key => $val) {
                $list[] = $val;
            }
        }
        return  $list; 
    }

    /**
     * 指定したテーブルの構成情報を取得する
     *
     * @param  string $database データベーススキーマ名
     * @param  string $table テーブル名
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
    public function getColumnsInfo($database, string $table)
    {
        $database = $database ?: 'default';
        $mTableInfo = new ModelTableInfo($database);
        $data = $mTableInfo->getShowColums($table);
        $columns = [];
        foreach ($data as $record) {
            $type = $record['DataType'];
            if ($record['CharacterMaximumLength'] != null || $record['NumericPrecision'] != null) {
                if ($record['DataType'] != 'text' && $record['DataType'] != 'blob') {
                    $number = $record['CharacterMaximumLength'] != null ? $record['CharacterMaximumLength'] : $record['NumericPrecision'];
                    $type = $record['DataType'] . "(" . $number . ")";
                }
            }
            $columns[] = [
                'Name' => $record['ColumnName'],
                'Type' => strtoupper($type),
                'Nullable' => $record['IsNullable'],
                'Default' => $record['ColumnDefault'],
                'Comment' => $record['ColumnComment'],
            ];
        }
        return $columns;
    }

    /**
     * 指定されたテーブルをリセットする
     *
     * @param  string $database データベース名
     * @param  string $table テーブル名
     * @param  boolean $resetNumber シーケンス番号をリセットするか true:リセットする. false:リセットしない
     * @return boolean 成功した場合は trueを、失敗した場合は falseを返す
     */
    public function truncate($database, string $table, bool $resetNumber = true) : bool
    {
        $database = $database ?: 'default';
        $mTableInfo = new ModelTableInfo($database);
        return $mTableInfo->truncate($table, $resetNumber);
    }
}