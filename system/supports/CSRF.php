<?php
/**
 * CSRF.php
 * 
 * @category  Support 
 * @package   CSRF 
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\supports;

use system\settings\FuseBox;
use system\core\HttpRequest;
use system\session\Session;

/**
 * CSRF Class
 * 
 * @category Support 
 * @package  CSRF 
 * @author   Takahiro Kotake <podium@teleios.jp
 * 
 */
class CSRF
{
    const KEY_CSRF = 'CSRF';
    const KEY_CSRF_CHECK_PAGE = 'CSRF_CHACK_PAGE';
    const KEY_CSRF_REMOTE_IP = 'IPADDRESS';

    static bool $strong;
    /**
     * CSRFを生成し、セッションに格納したあとで文字列を返す
     *
     * @param integer $length
     * @return string
     */
    public static function get(int $length = 16) : string
    {
        $strong = null;
        $csrfKey = openssl_random_pseudo_bytes($length, $strong);
        $csrfToken = bin2hex($csrfKey);
        self::$strong = $strong;
        return $csrfToken;
    }

    /**
     * CSRFトークンを設定する
     *
     * @param string $csrfToken
     * @return void
     */
    public static function setCsrfToken(string $csrfToken)
    {
        Session::set(self::KEY_CSRF, $csrfToken);
    }

    /**
     * リファラーチェック用に現在のコントローラーを設定する
     *
     * @return void
     */
    public static function setReferer()
    {
        Session::set(self::KEY_CSRF_CHECK_PAGE, HttpRequest::getControllerName() . '/' . HttpRequest::getControllerMethod());
    }

    /**
     * IPアドレスチェックのため現在のIPアドレスを設定する
     *
     * @return void
     */
    public static function setRemoteIp()
    {
        Session::set(self::KEY_CSRF_REMOTE_IP, FuseBox::get(FuseBox::SERVER, 'REMOTE_ADDR'));
    }

    /**
     * CSRFトークンのチェックを行う
     *
     * @param string $csrfToken CSRFトークン文字列
     * @return boolean
     */
    public static function checkCsrfToken(string $csrfToken) : bool
    {
        $holdCsrfToken = Session::get(self::KEY_CSRF);
        $csrfCheck = strcmp($csrfToken, $holdCsrfToken) == 0 ? true : false;
        return $csrfCheck;
    }

    /**
     * リファラーのチェックを行う
     *
     * @return boolean
     */
    public static function checkReferer() : bool
    {
        $refererCheck = true;
        if (FuseBox::isExist(FuseBox::SERVER, 'HTTP_REFERER')) {
            $referer = FuseBox::get(FuseBox::SERVER, 'HTTP_REFERER');
            $prePage = str_replace('/', '\\/', Session::get(self::KEY_CSRF_CHECK_PAGE));
            $refererCheck = preg_match('/' . $prePage . '/u', $referer);
        }
        return $refererCheck;
    }

    /**
     * IPアドレスのチェックを行う
     *
     * @return boolean
     */
    public static function checkRemoteId() : bool
    {
        $ipCheck = true;
        if (FuseBox::isExist(FuseBox::SERVER, 'REMOTE_ADDR')) {
            $remoteIp = FuseBox::get(FuseBox::SERVER, 'REMOTE_ADDR');
            $passedIp = Session::get(self::KEY_CSRF_REMOTE_IP);
            $ipCheck = strcmp($remoteIp, $passedIp) == 0 ? true : false;
        }
        return $ipCheck;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function getStrong()
    {
        return self::$strong;
    }
}