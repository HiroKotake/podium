<?php
/**
 * dynamicStruct.php
 * 
 * @category  Support
 * @package   Strage
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\supports;

/**
 * dynamicStruct Class
 * 
 * @category Support
 * @package  Strage
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class dynamicStruct
{
    private $params = [];

    /**
     * 指定した名称の変数の値を設定する
     *
     * @param string $key 変数名
     * @param mix $value 値
     */
    function __set(string $key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * 指定した名称の変数の値を設定する
     * (__setで格納出来ない場合の代替メソッド)
     *
     * @param string $key 変数名
     * @param Object $value 格納したい値
     * @return void
     */
    function setObject(string $key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * 指定した名称の変数の値を取得する
     *
     * @param string $key 変数名
     * @return object
     */
    function __get(string $key) : object
    {
        return $this->params[$key];
    }

    /**
     * 格納されているリストの要素名を取得する
     *
     * @return array
     */
    public function getList() : array
    {
        return array_keys($this->params);
    }

    /**
     * 対象が設定されているか確認
     *
     * @param string $name 変数名
     * @return boolean 存在する場合はtrue, しない場合はfalseを返す
     */
    function exists(string $name) : bool
    {
        return array_key_exists($name, $this->params);
    }
}