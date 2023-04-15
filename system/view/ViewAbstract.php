<?php
/**
 * ViewAbstract.php
 * 
 * @category  View
 * @package   Abstract
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\view;

/**
 * ViewAbstract class
 * 
 * @category View
 * @package  Abstract
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
abstract class ViewAbstract
{
    /**
     * 一般向けテンプレート
     */
    const FOR_GENERAL = true;
    const GENERAL_TEMPLATE = 'app';
    /**
     * 管理者向けテンプレート
     */
    const FOR_ADMIN = false;
    const ADMIN_TEMPLATE = 'admin';

    /**
     * テンプレート名
     *
     * @var array
     */
    protected array $templates;
    /**
     * テンプレート配列へのインデックス
     *
     * @var integer
     */
    protected int $index;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        $this->index = 0;
        $this->templates = array();
    }

    /**
     * テンプレートファイルが存在するか確認し、テンプレートファイルのフルパスを返す
     *
     * @param string $template テンプレート名
     * @param string $type 対象のフォルダ (標準値：app) appかａｄｍｉｎを期待
     * @return void 存在する場合はファイル名を含むファイルパスを返す。存在しない場合はfalseを返す
     */
    protected function existTemplate(string $template, $type = self::GENERAL_TEMPLATE)
    {
        $baseDir = ($type == self::GENERAL_TEMPLATE)? VIEW_PATH : PWF_ADMIN_VIEW_PATH;
        $file = $baseDir . $template;
        if (file_exists($file . '.tpl')) {
            return $file . '.tpl';
        }
        if (file_exists($file . '.php')) {
            return $file . '.php';
        }
        if (file_exists($file)) {
            return $file;
        }
        return false;
    }

    /**
     * テンプレートを設定する
     *
     * @param  string $template テンプレート名
     * @param  boolean $type  テンプレートが一般向けであればtrue(デフォルト値), 管理者向けであればfalseを設定する。
     * @return boolean テンプレートが存在した場合はtrueを、存在しない場合はfalseを返す
     */
    protected function setChoiceTemplate(string $template, bool $type = self::FOR_GENERAL) : bool
    {
        if ($file = $this->existTemplate($template, ($type ? self::GENERAL_TEMPLATE : self::ADMIN_TEMPLATE))) {
            $this->templates[$this->index] = $file;
            $this->index += 1;
            return true;
        }
        return false;
    }

    /**
     * アプリケーション用テンプレートを設定する
     *
     * @param  string $template テンプレート名
     * @return boolean
     */
    public function setTemplate(string $template) : bool
    {
        return $this->setChoiceTemplate($template, self::FOR_GENERAL);
    }

    /**
     * 管理者用テンプレートを設定する
     *
     * @param  string $template テンプレート名
     * @return boolean
     */
    public function setAdminTemplate(string $template) : bool
    {
        return $this->setChoiceTemplate($template, self::FOR_ADMIN);
    }

    /**
     * 送信するデータ
     *
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    abstract public function setData(array $data);
    /**
     *
     * @return void 
     */
    /**
     * コンテンツを送信する
     *
     * @param  string $template テンプレート名
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return void
     */
    abstract public function view(string $template = '', array $data = []);
    /**
     * コンテンツを取得する
     *
     * @param  string $template テンプレート名
     * @param  array $data テンプレートへ反映させるデータを含む配列
     * @return string コンテンツ文字列
     */
    abstract public function fetch(string $template = '', array $data = []) : string;
}
