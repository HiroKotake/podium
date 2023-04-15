<?php
/**
 * ViewSmarty.php
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

use Smarty;
use system\view\ViewAbstract;

/**
 * ViewSmarty class
 * 
 * @category View
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class ViewSmarty extends ViewAbstract 
{
    /**
     * テンプレートエンジンインスタンス
     *
     * @var Smarty
     */
    private Smarty $_tplEngine;

    /**
     * コンストラクタ
     *
     */
    function __construct()
    {
        parent::__construct();
        $this->_tplEngine = new Smarty();
        $this->_tplEngine->compile_dir  = ROOT_PATH . 'cache' . DIRECTORY_SEPARATOR . 'templates_c';
        $this->_tplEngine->config_dir   = ROOT_PATH . 'config';
        $this->_tplEngine->caching = Smarty::CACHING_OFF;
    }

    /**
     * テンプレートエンジンのインスタンスを取得する
     *
     * @return Smarty
     */
    public function getTemplateEngine() : Smarty
    {
        return $this->_tplEngine;
    }

    /**
     * 送信するデータ
     *
     * @param array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->_tplEngine->assign($key, $value);
        }
    }

    /**
     * コンテンツを送信する
     *
     * @param array $data テンプレートへ反映させるデータを含む配列
     */
    private function _sendContents(array $data) : void
    {
        // データ追加
        if (!empty($data)) {
            $this->_tplEngine->setData($data);
        }

        foreach ($this->templates as $tpl) {
            $this->_tplEngine->display($tpl);
        }
    }

    /**
     * アプリケーション用コンテンツを送信する
     *
     * @param  string $template テンプレート名
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    public function view(string $template = '', array $data = []) : void
    {
        // テンプレート追加
        if (!empty($template)) {
            $this->_tplEngine->setTemplate($template);
        }
        $this->_sendContents($data);
    }

    /**
     * 管理者用コンテンツを送信する
     *
     * @param  string $template テンプレート名
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    public function adminView(string $template = '', array $data = []) : void
    {
        // テンプレート追加
        if (!empty($template)) {
            $this->_tplEngine->setAdminTemplate($template);
        }
        $this->_sendContents($data);
    }

    /**
     * コンテンツを取得する
     *
     * @param array $data テンプレートへ反映させるデータを含む配列
     * @return string 評価済みのコンテンツ 
     */
    private function _getContents(array $data) : string
    {
        // データ追加
        if (!empty($data)) {
            $this->setData($data);
        }
        $html = '';
        foreach ($this->templates as $tpl) {
            $html .= $this->_tplEngine->fetch($tpl);
        }
        return $html;
    }

    /**
     * アプリケーション用コンテンツを取得する
     *
     * @param string $template テンプレート名
     * @param array $data テンプレートへ反映させるデータを含む配列
     * @return string 評価済みのコンテンツ 
     */
    public function fetch(string $template = '', array $data = []) : string
    {
        // テンプレート追加
        if (!empty($template)) {
            $this->setTemplate($template);
        }
        return $this->_getContents($data);
    }

    /**
     * 管理者用コンテンツを取得する
     *
     * @param string $template テンプレート名
     * @param array $data テンプレートへ反映させるデータを含む配列
     * @param  array $option 送信に任意のオプションがある場合に利用する汎用の配列 (将来拡張用)
     * @return string 評価済みのコンテンツ 
     */
    public function adminFetch(string $template = '', array $data = []) : string
    {
        // テンプレート追加
        if (!empty($template)) {
            $this->setAdminTemplate($template);
        }
        return $this->_getContents($data);
    }
}