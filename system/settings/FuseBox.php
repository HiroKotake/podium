<?php
/**
 * FuseBox.php
 * 
 * @category  Setting 
 * @package   Strage
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\settings;

/**
 * FuseBox Class
 * 設定ファイルを保持するクラス
 * 
 * @category Setting 
 * @package  Strage
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class FuseBox
{
    const SERVER = 'SERVER';
    const REQUEST = 'REQUEST';
    const GET = 'GET';
    const POST = 'POST';
    const CONFIGURES = 'CONFIGURES';
    const USER = 'USER';

    /**
     * システムパラメータ
     *
     * @var array
     */
    protected static array $system = [];

    /**
     * 時間計測実施最終確認フラグ
     *
     * @var boolean
     */
    protected static bool $exectime_check = true;

    /**
     * 設定ファイルの読み込み
     *
     * @return void
     */
    public static function initialize()
    {
        // システム設定読み込み
        require ROOT_PATH . 'config' . CONFIG_DIR[ENVEROMENT] . '/bootstrap.php';
        self::$system[self::CONFIGURES] = [];
        foreach ($bootConfigure as $key => $value) {
            if ($key == SYSTEM_EXTRA_LIBS) {
                foreach ($value as $name => $val) {
                    self::$system[self::CONFIGURES][$key][strtoupper($name)] = $val;
                }
                continue;
            }
            self::$system[self::CONFIGURES][$key] = $value;
        }
        // スーパー変数関連 backup
        self::$system[self::SERVER] = [];
        self::$system[self::SERVER]['PATH_INFO'] = '/' . $bootConfigure[DEFAULT_PAGE];
        foreach ($_SERVER as $key => $value) {
            self::$system[self::SERVER][$key] = $value;
        }
        self::$system[self::REQUEST] = [];
        foreach ($_REQUEST as $key => $value) {
            self::$system[self::REQUEST][$key] = $value;
        }
        self::$system[self::GET] = [];
        foreach ($_GET as $key => $value) {
            self::$system[self::GET][$key] = $value;
        }
        self::$system[self::POST] = [];
        foreach ($_POST as $key => $value) {
            self::$system[self::POST][$key] = $value;
        }
    }

    /**
     * Setter 
     *
     * @param string $name
     * @param [type] $value
     */
    function __set(string $name, $value)
    {
        self::$system[self::USER][$name] = $value;
    }

    /**
     * Getter
     *
     * @param string $name
     * @return void
     */
    function __get(string $name)
    {
        return self::$system[self::USER][$name];
    }

    /**
     * 指定したカテゴリに対応する配列名が存在するか
     *
     * @param string $category カテゴリ名
     * @param string $name 配列名
     * @return boolean 存在する場合は true を、存在しない場合は false を返す
     */
    public static function isExist(string $category, $name) : bool
    {
        $exists = array_key_exists($name, self::$system[$category]);
        return $exists;
    }

    /**
     * 値を取得する
     *
     * @param string $category カテゴリ名
     * @param string $name キー名
     * @param string $subkey 複キー名(省略可)
     * @return mix 指定したカテゴリ内のキー名の値を返す 
     * @throws Exception 対象が無い場合に例外を発生させます
     */
    public static function get(string $category, string $name, string $subkey = '')
    {
        if (empty(self::$system[$category])) {
            return '';
        }
        if (!array_key_exists($category, self::$system)) {
            throw new \Exception('Non Exist Category(' . $category . ') !!');
        }
        if (!array_key_exists($name, self::$system[$category])) {
            throw new \Exception('Non Exist Name(' . $name. ') in Category(' . $category . ')!!');
        }
        return empty($subkey) ? self::$system[$category][$name] : self::$system[$category][$name][$subkey];
    }

    /**
     * 指定したカテゴリに属する情報を含む配列を返す
     *
     * @param string $category カテゴリ名
     * @return array 指定したカテゴリーの情報配列を返す
     * @throws Exception 対象が無い場合に例外を発生させます
     */
    public static function getCategory(string $category) : array
    {
        if (!array_key_exists($category, self::$system)) {
            throw new \Exception('Non Exist Category(' . $category . ') !!');
        }
        return self::$system[$category];
    }
    /**
     * 値を設定する
     *
     * @param string $category カテゴリ名
     * @param string $name 変数名
     * @param [type] $value 値
     * @return void
     */
    public static function set(string $category, string $name, $value)
    {
        if (!in_array($category, self::$system)) {
            self::$system[$category] = [];
        }
        self::$system[$category][$name] = $value;
    }

    /**
     * 時間計測実施最終確認フラグ変更
     *
     * @param boolean $flag
     * @return void
     */
    public static function setExectimeCheck(bool $flag = true) : void
    {
        self::$exectime_check = $flag;
    }

    /**
     * 時間計測実施最終確認フラグ確認
     *
     * @return boolean
     */
    public static function checkFinalConfirm() : bool
    {
        return self::$exectime_check;
    }
}