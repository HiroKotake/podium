<?php
/**
 * ViewXML.php
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

use system\view\ViewAbstract;

/**
 * ViewXML Class
 * 
 * @category View
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class ViewXML extends ViewAbstract
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
        parent::__construct();
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
     * 表示するデータと、コンテンツをバッファに読み込ませる
     *
     * @param array $data 表示させるデータを含む配列
     * @return void
     */
    private function _makeContents(array $data)
    {
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }
        foreach ($this->templates as $tpl) {
            include_once $tpl;
        }
    }

    /**
     * headerを送信する
     *
     * @param string $charCode
     * @return void
     */
    public function sendHeader(string $charCode = 'utf-8') : void
    {
        if (!headers_sent()) {
            header("Content-Type: text/xml; charset=" . $charCode);
        }
    }

    /**
     * コンテンツを送信する
     *
     * @param  string $template テンプレート名
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    public function view(string $template = '', array $data = [])
    {
        // テンプレート追加
        if (!empty($template)) {
            $this->setTemplate($template);
        }
        // データ追加
        if (!empty($data)) {
            $this->setData($data);
        }
        // ヘッダー送信（すでに self::sendHeader() で送信ずみならば送信しない
        $this->sendHeader();
        // 出力制御を利用してコンテンツを生成する
        ob_start();
        $this->_makeContents($this->data);
        ob_end_flush();
    }

    /**
     * コンテンツを取得する
     *
     * @param  string $template テンプレート名
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return string コンテンツ文字列
     */
    public function fetch(string $template = '', array $data = []) : string
    {
        // テンプレート追加
        if (!empty($template)) {
            $this->setTemplate($template);
        }
        // データ追加
        if (!empty($data)) {
            $this->setData($data);
        }
        // 出力制御を利用してコンテンツを生成する
        $contents = '';
        ob_start();
        $this->_makeContents($this->data);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
}