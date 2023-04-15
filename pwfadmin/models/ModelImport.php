<?php
/**
 * ModelImport.php
 * 
 * @category  Model 
 * @package   Import
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\models;

use system\core\Model;

/**
 * ModelImport Class
 * 
 * @category Model 
 * @package  Import
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class ModelImport extends Model
{
    /**
     * 接続するデータベース名
     * config/database.phpで設定しているデータベース名
     */
    protected string $dbName = 'default';
    protected string $message = '';

    /**
     * Constructer
     *
     * @param string $dbName データベース名
     */
    function __construct(string $dbName = '')
    {
        if (!empty($dbName)) {
            $this->dbName = $dbName;
        }
        parent::__construct($this->dbName);
    }

    /**
     * 配列の値を確認し、’’ならばnullに置き換える
     *
     * @param  array $data
     * @return array
     */
    protected function changeEmptyToNull(array $data) : array
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if ($value === '') {
                $value = null;
            }
            $newData[$key] = $value;
        }
        return $newData;
    }

    protected function resultCheck(array $result) : bool
    {
        $check = true;
        foreach ($result as $value) {
            if ($value == 0) {
                $check = $check && false;
            }
        }
        return $check;
    }

    /**
     *
     * @param  string $file
     * @return mix
     */
    /**
     * CSVデータファイルから一括してデータをデータベースに反映させる
     *
     * @param  string $file
     * @return boolean
     */
    public function import(string $file) : bool
    {
        // テーブル名抽出
        $temp = explode(DIRECTORY_SEPARATOR, $file);
        $table = strtr(array_pop($temp), [".csv" => ""]);
        // ファイル読み込み
        $hFile = fopen($file, 'r');
        if ($hFile) {
            $fHeadLine = true;
            $data = [];
            $query = '';
            while (($line = fgetcsv($hFile, 2048, ',')) !== false) {
                // クエリー生成
                if ($fHeadLine) {
                    $query = 'INSERT INTO `' . $table . '`(';
                    $columns = '';
                    foreach ($line as $column) {
                        $columns .= '`' . $column . '`,';
                    }
                    $query .= rtrim($columns, ',') . ') VALUES (';
                    $query .= str_repeat('?,', count($line) - 1);
                    $query .= '?)';
                    $fHeadLine = false;
                    continue;
                }
                // データ読み込み
                $data[] = $this->changeEmptyToNull($line);
            }
            if (empty($query)) {
                return false;
            }
            // データ書き込み
            $result = $this->db->prepareInsert($query, $data);
            if (empty($result)) {
                $this->message =  $this->db->getMessage();
                return false;
            }
            return $this->resultCheck($result);
        }
        return false;
    }

    /**
     * エラー発生時のエラーメッセージを取得する
     *
     * @return array エラーメッセージ
     */
    public function getMessage() : array
    {
        if (empty($this->message)) {
            return [];
        }
        $msgs = [];
        foreach (explode(':', $this->message) as $line) {
            $temp = trim($line);
            if (in_array($temp, $msgs)) {
                continue;
            }
            $msgs[] = $temp;
        }
        return $msgs;
    }
}