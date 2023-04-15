<?php
/**
 * ModelResourceStatus.php
 * 
 * @category  Model 
 * @package   Resource 
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\models;

/**
 * ModelResourceStatus Class
 * 
 * @category Model 
 * @package  Resource
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class ModelResourceStatus
{
    /**
     * ステータスファイル
     */
    const STATUS_FILE = '.status';

    /**
     * 読み込むディレクトリ元
     *
     * @var string
     */
    protected string $targetDir;
    /**
     * ステータスファイル名
     *
     * @var string
     */
    protected string $statusFile;
    /**
     * インポート状況データ
     *
     * @var array
     */
    protected array $statusInfos = [];
    /**
     * インポート状況データの現在のインデックス
     *
     * @var integer
     */
    protected int $cIndex = 0;

    /**
     * constructer
     *
     * @param string $targetDir 読み込むファイルのディレクトリ (無指定時：<システムルート>/resources/imports)
     * @param string $statusFile ステータスファイル名（省略時:.status)
     */
    function __construct(string $targetDir, string $statusFile = self::STATUS_FILE)
    {
        $this->targetDir = $targetDir;
        $this->statusFile = $statusFile;
    }

    /**
     * インポート状況ファイルを読み込む
     *
     * @return boolean 成功した場合はtrueを、失敗した場合はfalseを返す
     */
    public function load(int $lineMaxLength = 256*2+18+8+1+18) : bool
    {
        // ファイル存在確認
        $statusFile = $this->targetDir . DIRECTORY_SEPARATOR . $this->statusFile;
        if (!file_exists($statusFile)) {
            return false;
        }
        // ファイルオープン
        $hFile = fopen($statusFile, 'r');
        if (!$hFile) {
            return false;
        }
        // データ読み込み
        while (($data = fgetcsv($hFile, $lineMaxLength, ',')) !== false) {
            $this->statusInfos[$data[0]] = $data;
        }
        fclose($hFile);
        $this->cIndex = 0;
        return true;
    }

    /**
     * 指定したファイルのインポート状況を読み込む
     * ファイル指定がない場合は、配列の先頭からインポート状況を取得する
     *
     * @param  string $key CSVデータファイル名(省略時:ファイル指定なしとみなす)
     * @return array 対象もしくはインデックスの次のデータ。データが存在しない場合は空の配列を返す
     *               [<ファイル名>, <タイムスタンプ>, <ファイルサイズ>, <インポートフラグ>, <インポートタイムスタンプ>]
     */
    public function get(string $key = '') : array
    {
        if (empty($this->statusInfos)) {
            return [];
        }
        if (empty($key)) {
            $data = $this->statusInfos[$this->cIndex];
            $this->cIndex += 1;
            return $data;
        }
        if (array_key_exists($key, $this->statusInfos)) {
            return $this->statusInfos[$key];
        }
        return [];
    }

    /**
     * 全ステータス状況データを取得する
     *
     * @return array
     */
    public function getAll() : array
    {
        return $this->statusInfos;
    }

    /**
     * インポート状況を指定したデータで更新する
     *
     * @param  array $data インポート状況データ 
     *                     [<ファイル名>, <タイムスタンプ>, <ファイルサイズ>, <インポートフラグ>, <インポートタイムスタンプ>]
     * @return boolean 成功した場合はtrueを、失敗した場合はfalseを返す
     */
    public function set(array $data) : bool
    {
        // 空の配列でないことを確認
        if (empty($data)) {
            return false;
        }
        $key = $data[0];
        // 既存データか確認し、既存データの場合は更新
        if (array_key_exists($key, $this->statusInfos)) {
            $this->statusInfos[$key] = $data;
            return true;
        }
        // 新規データの場合は追加する
        $this->statusInfos[$key] = $data;
        return true;
    }

    /**
     * インポート状況ファイルを保存する
     *
     * @return boolean 成功した場合はtrueを、失敗した場合はfalseを返す
     */
    public function save() : bool
    {
        // キー名順に並び替える
        ksort($this->statusInfos);
        // ファイルをオープンする
        $statusFile = $this->targetDir . DIRECTORY_SEPARATOR . $this->statusFile;
        $hFile = fopen($statusFile, 'w');
        if ($hFile === false) {
            return false;
        }
        // データ書き込み
        foreach ($this->statusInfos as $data) {
            fputcsv($hFile, $data);
        }
        fclose($hFile);
        return true;
    }
}