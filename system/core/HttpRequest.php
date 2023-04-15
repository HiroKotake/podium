<?php
/**
 * HttpRequest.php
 * 
 * @category  Core
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\core;

use system\settings\FuseBox;

/**
 * リクエスト情報処理クラス 
 * 
 * @category Core
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class HttpRequest
{

    /**
     * パラメータ保存用
     *
     * @var array
     */
    protected static array $params = [];
    /**
     * コントローラー名
     *
     * @var string
     */
    protected static string $ctrlName = '';
    /**
     * 管理画面表示要求フラグ
     *
     * @var bool (初期値) false
     */
    protected static bool $adminCtrl = false;
    /**
     * コントロールメソッド名
     *
     * @var string (初期値)index
     */
    protected static string $ctrlMethod = 'index';
    /**
     * 定義されていないコントローラー
     *
     * @var boolean
     */
    private static bool $_nonDefinedCtrl = false;
    /**
     * サービス名(PATH_INFO)
     *
     * @var string
     */
    public static string $service = '';
    /**
     * リクエストURI
     *
     * @var string
     */
    public static string $requestUri = '';
    /**
     * メソッド名
     *
     * @var string (初期値)GET
     */
    public static string $method = 'GET';
    /**
     * イニシャライズ完了フラグ
     *
     * @var boolean
     */
    private static bool $_initialized = false;
    /**
     * インスタンス格納先
     *
     * @var [type]
     */
    protected static $instance = null;

    /**
     * 初期設定を実施する
     *
     * @return void
     */
    public static function initialize()
    {
        if (!self::$_initialized) {
            //self::$method = empty($_POST) ? "GET": "POST";
            self::$method = FuseBox::get(FuseBox::SERVER, 'REQUEST_METHOD');
            self::$params['Method'] = self::$method;
            self::$service = FuseBox::get(FuseBox::SERVER, 'PATH_INFO');
            self::$params['Service'] = self::$service;
            self::$requestUri = FuseBox::get(FuseBox::SERVER, 'REQUEST_URI');
            self::$params['RequestUri'] = self::$requestUri;
            // $_REQUESTを転記する
            foreach ($_REQUEST as $key => $value) {
                if (is_string($value)) {
                    self::$params[$key] = htmlspecialchars_decode($value);
                } else {
                    self::$params[$key] = $value;
                }
            }
            self::$_initialized = true;
        }
    }

    /**
     * インスタンスを取得する
     *
     * @return void
     */
    public static function &getInstance()
    {
        if (!self::$_initialized) {
            self::initialize();
        }
        if (empty(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * 未設定変数取得
     *
     * @param string $name 変数名
     * @return void 変数値
     */
    function __get($name)
    {
        return self::get($name);
    }

    /**
     * 値を取得する
     *
     * @param string $name
     * @return void
     */
    static function get(string $name)
    {
        return self::$params[$name];
    }

    /**
     * 未設定変数設定
     *
     * @param string $name 変数名
     * @param $value 変数値
     */
    function __set(string $name, $value)
    {
        self::set($name, $value);
    }

    /**
     * 値を設定する
     *
     * @param string $name
     * @param [type] $value
     * @return void
     */
    static function set(string $name, $value)
    {
        self::$params[$name] = htmlspecialchars_decode($value);
    }

    /**
     * パラメータを取得する
     *
     * @return array パラメータを含む配列
     */
    public static function getData() : array
    {
        return self::$params;
    }

    /**
     * コントローラー名を設定する
     *
     * @param string $controllerName コントローラー名
     * @return void
     */
    public static function setControllerName(string $controllerName)
    {
        self::$ctrlName = $controllerName;
    }

    /**
     * uriを設定してコントローラーの特定をやり直す
     *
     * @param string $uri
     * @return string コントローラー名
     */
    public static function reset(string $uri) : string
    {
        self::$ctrlName = '';
        self::$ctrlMethod = 'index';
        self::$adminCtrl = false;
        self::$_nonDefinedCtrl = false;
        self::$service = '/' . $uri;
        return self::getTargetController();
    }

    /**
     * コントローラー名(単体)を取得する
     *
     * @return string
     */
    public static function getControllerName() : string
    {
        if (empty(self::$ctrlName)) {
            self::getTargetController();
        }
        return rtrim(self::$ctrlName, '/');
    }

    /**
     * 定義されているコントローラーか確認
     *
     * @return boolean
     */
    public static function isIllegalController() : bool
    {
        if (empty(self::$ctrlName)) {
            self::getTargetController();
        }
        return (self::$_nonDefinedCtrl && !empty(self::$ctrlName));
    }

    /**
     * 管理コントローラー呼び出し指定
     *
     * @return bool TRUE:管理コントローラー呼び出し要求
     */
    public static function isAdmin() : bool
    {
        return self::$adminCtrl;
    }

    /**
     * コントローラー名を取得
     *
     * @return string
     */
    public static function getTargetController() : string 
    {
        // すでに実行済みの場合は設定済みコントローラー名を返す
        if (!empty(self::$ctrlName)) {
            return self::$ctrlName;
        }
        // 管理者ページでコントローラーが無指定か判定し、無指定の場合は管理者ページフラグを立て、''を返す
        if (self::$service == '/' . PWF_ADMIN_DIR . '/') {
            self::$adminCtrl = true;
            return '';
        }
        // 一般ページでコントローラーが無指定か判定し、無指定の場合は''を返す
        if (self::$service == '/') {
            return '';
        }
        $paths = explode('/', ltrim(self::$service, '/'));
        $ctrlPath = APP_PATH . DIRECTORY_SEPARATOR . 'controllers';
        $count = count($paths);
        // コントローラー検索
        for ($i = 0; $i < $count; $i++) {
            self::$ctrlName .= $paths[$i];
            // 管理コントローラー指定 : 最初の要素にadminが設定されている場合は管理者ページへのアクセス
            //if ($i === 0 && $paths[$i] == 'admin') {
            if ($i === 0 && $paths[$i] == PWF_ADMIN_DIR) {
                self::$adminCtrl = true;
                self::$ctrlName = '';
                $ctrlPath = PWF_ADMIN_PATH . DIRECTORY_SEPARATOR . 'controllers';
                continue;
            }
            // コントローラー特定
            if (!is_dir($ctrlPath . DIRECTORY_SEPARATOR . self::$ctrlName)) {
                if (is_file($ctrlPath . DIRECTORY_SEPARATOR . self::$ctrlName . '.php')) {
                    // パラメータ分離
                    $paramsString = str_replace(self::$ctrlName, '', self::$service);
                    if (!empty($paramsString)) {
                        $paramWork = explode('/', ltrim($paramsString, '/'));
                        if (self::$adminCtrl) {
                            // adminの場合は$paramWorkの最初の要素はいらないので排除する
                            $paramsString = str_replace('/' . PWF_ADMIN_DIR . '/' . self::$ctrlName, '', self::$service);
                            $paramWork = explode('/', ltrim($paramsString, '/'));
                            if (empty($paramWork[0])) {
                                $paramWork = [];
                            }
                        }
                        // $paramWork[0] が空の場合はメソッド未設定でかつパラメーターなしなので終了させる
                        if (empty($paramWork[0])) {
                            return self::$ctrlName;
                        }
                        $paramCount = count($paramWork);
                        $index = 0;
                        // メソッド名設定
                        if ($paramCount > 0 && $paramCount % 2 == 1) {
                            self::$ctrlMethod = $paramWork[$index];
                            $index = 1;
                        }
                        for ($i = $index; $i < $paramCount; $i++) {
                            self::$params[$paramWork[$i++]] = $paramWork[$i];
                        }
                    }
                    return self::$ctrlName;
                }
            }
            self::$ctrlName .= DIRECTORY_SEPARATOR;
        }
        self::$_nonDefinedCtrl = true;   // 存在しないコントローラーなので、フラグを立てる
        return '';
    }

    /**
     * コントローラーのフルパスを取得する
     *
     * @return string コントローラーのフルパス、存在しない場合な''を返す
     */
    public static function getControllerFilePath() : string
    {
        if (!self::existCtrlName()) {
            return '';
        }
        return (self::$isAdmin() ? PWF_ADMIN_CTRL_PATH : CTRL_PATH) . self::$ctrlName . '.php';
    }

    /**
     * コントローラのフルパスでの名称を取得する
     *
     * @return string
     */
    public static function getFullControllerName() : string
    {
        if (!self::existCtrlName()) {
            return '';
        }
        return (self::$adminCtrl ? PWF_ADMIN_DIR : 'app') . '\controllers\\' . str_replace(DIRECTORY_SEPARATOR, '\\', self::$ctrlName);
    }

    /**
     * メソッド名を取得する
     *
     * @return string
     */
    public static function getControllerMethod() : string
    {
        if (!self::existCtrlName()) {
            return '';
        }
        return self::$ctrlMethod;
    }

    /**
     * コントローラーが取得できているか確認
     *
     * @return boolean 存在する場合は真を、しない場合は偽を返す
     */
    private static function existCtrlName() : bool
    {
        if (empty(self::$ctrlName)) {
            $check = self::getTargetController();
            if (empty($check)) {
                return false;
            }
        }
        return true;
    }

    /**
     * HTMLコンテンツのファイルパスを取得する
     *
     * @return string 対象のファイルがある場合はファイルパスを返す、存在しない場合は空を返す
     */
    public static function getHtmlContests() : string
    {
        $controller = rtrim(self::$ctrlName, '/');
        $check = explode('.', $controller);
        $extend = array_pop($check);
        if ($extend == 'html' || $extend == 'htm') {
            $file = PUBLIC_PATH . DIRECTORY_SEPARATOR . $controller;
            if (file_exists($file)) {
                return $file;
            }
        }
        return '';
    }

    /**
     * HTTPリクエストがPOSTメソッドか確認する
     *
     * @return boolean 戻り値が true であれば POSTメソッド、falseであれば GETメソッド
     */
    public static function isPostMethod() : bool
    {
        return (strcasecmp(self::$method, 'POST') == 0);
    }
}
