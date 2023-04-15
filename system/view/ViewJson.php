<?php
/**
 * ViewJson.php
 * 
 * @category  View
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\view;

/**
 * ViewJson Class
 * 
 * @category View
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class ViewJson
{

    /**
     *  送信するデータ
     *
     * @var array
     */
    protected array $data;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        $this->data = array();
    }

    /**
     * 送信するデータ
     *
     * @param array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    public function setData(array $data) 
    {
        $this->data = $data + $this->data;
    }

    /**
     * headerを送信する
     *
     * @return void
     */
    public function sendHeader()
    {
        if (!headers_sent()) {
            header("Content-Type: text/json; charset=utf-8");
        }
    }

    /**
     * self::$dataをJSON化する
     *
     * @param  array $option 送信に任意のオプションがある場合に利用する汎用の配列 
     * @return string JSONデータ
     */
    protected function arrayToJson(array $option = []) : string
    {
        // オプション無し
        if (empty($option)) {
            $json = json_encode($this->data);
            return $json;
        }
        // オプション有り
        $opt = 0;
        foreach ($option as $flag) {
            $opt += $flag;
        }
        $json = json_encode($this->data, $opt);
        return $json;
    }

    /**
     * JSONデータを送信する
     *
     * @param  array $data
     * @param  array $option 送信に任意のオプションがある場合に利用する汎用の配列 
     * @return void
     */
    public function send(array $data = [], array $option = [])
    {
        // データ追加
        if (!empty($data)) {
            $this->setData($data);
        }
        // ArrayをJSONデータへ変換
        $json = $this->arrayToJson($option);
        // ヘッダー送信（すでに self::sendHeader() で送信ずみならば送信しない
        $this->sendHeader();
        // JSONデータを送信
        echo $json;
    }

    /**
     * JSON文字列を取得する
     *
     * @param  array $data
     * @param  array $option 送信に任意のオプションがある場合に利用する汎用の配列 
     * @return string
     */
    public function fetch(array $data = [], array $option = []) : string
    {
        // データ追加
        if (!empty($data)) {
            $this->setData($data);
        }
        // ArrayをJSONデータへ変換
        $json = $this->arrayToJson($option);
        return $json;
    }
}