<?php
/**
 * Cookie.php
 * 
 * @category  Support
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\supports;

/**
 * Cookie Class
 * 
 * @category Support
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * @see      https://www.php.net/manual/ja/function.setcookie.php setcookieに関するページ
 * 
 */
class Cookie
{

    /**
     * クッキーに設定されている値
     *
     * @var array
     */
    protected array $params = []; 
    /**
     * setcookie()のoption配列
     *
     * @var array
     */
    protected array $options = [];

    /**
     * コンストラクタ
     *
     * @param string $cookieName クッキー名
     */
    function __construct()
    {
        // 設定オプションの初期値を設定
        $this->options = [
            'expires' => time() + 3600 * 24,  // int
            'path' => "/",               // string
            'domain' => "",             // string
            'secure' => false,          // boolean
            'httponly' => true,        // boolean 
        ];
        // クッキーの値を読み込み
        foreach ($_COOKIE as $key => $value) {
            $this->params[$key] = htmlspecialchars($value);
        }
    }

    /**
     * クッキーに値を設定する
     *
     * @param string $key キー名
     * @param mix $value 値
     */
    function __set(string $key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * クッキーから値を取得する
     *
     * @param string $key キー名
     * @return void キー名に対応する値を返す。キー名に対応する場合は何も返さない
     */
    function __get(string $key)
    {
        if (in_array($key, $this->params)) {
            return;
        }
        return  $this->params[$key];
    }

    /**
     * クッキーの有効期限を設定する
     *
     * @param int $time 有効期限（秒) 無指定時：　 3600 * 24
     * @return void
     */
    public function setExpireTime(int $time = null)
    {
        $time = $time ?: time() + 3600 * 24;
        $this->options['expires'] = $time;
    }

    /**
     * クッキーを有効としたいパスを設定
     *
     * @param  string $path パス cookieの有効範囲を設定する [省略時"/(サイト全体)"]
     * @return void
     */
    public function setPath(string $path = "/")
    {
        $this->options['path'] = $path;
    }

    /**
     * クッキーが有効な (サブ) ドメインを設定
     *
     * @param string $domain
     * @return void
     */
    public function setDomain(string $domain = "")
    {
        $this->options['domain'] = $domain;
    }

    /**
     * HTTPS 接続の場合にのみクッキーが送信されるかを設定
     *
     * @param boolean $secure trueを指定するとHTTPSオンリーになる。 (無指定時 false)
     * @return void
     */
    public function setSecure(bool $secure = false)
    {
        $this->options['secure'] = $secure;
    }

    /**
     * HTTP を通してのみクッキーにアクセスするかを設定
     *
     * @param  boolean $httponly trueを指定するとHTTP接続オンリー。(無指定時 true)
     * @return void
     */
    public function setHttpOnly(bool $httponly = true)
    {
        $this->options['httponly'] = $httponly;
    }

    /**
     * 特定の値をクライアントのクッキーに反映させる
     *
     * @param string $key セッション名
     * @param [type] $value 値
     * @param array|null $options オプション(省略時はデフォルト値を使用)
     * @return void
     */
    public function set(string $key, $value, array $options = null)
    {
        $options = $options ?: $this->options;
        $this->params[$key] = $value;
        setcookie($key, $value, $options);
    }

    /**
     * 設定している値を全てクライアントに反映させる
     *
     * @return void
     */
    public function flushAll()
    {
        foreach ($this->params as $key => $value) {
            $this->set($key, $value);
        }
    }
}