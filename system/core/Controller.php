<?php
/**
 * Controller.php
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

use system\core\HttpRequest;
use system\session\PodSession;
use system\session\Session;
use system\settings\FuseBox;
use system\supports\Cookie;
use system\supports\dynamicStruct;
use \Exception;

/**
 * Controller class
 * 
 * @category Core
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class Controller
{
    /**
     * リクエスト情報 
     *
     * @var HttpRequest
     */
    protected HttpRequest $request;
    /**
     * セッション
     *
     * @var PodSession
     */
    protected Session $session;
    /**
     * 外部ライブラリ情報
     *
     * @var array
     */
    protected array $extLibs;
    /**
     * クッキー関連
     *
     * @var Cookie
     */
    protected Cookie $cookie;
    /**
     * 出力関連
     *
     * @var object
     */
    protected dynamicStruct $output;

    /**
     * コンストラクタ
     *
     */
    function __construct()
    {
        // リクエスト関連
        $this->request =& HttpRequest::getInstance();
        // セッション開始
        $this->session =& Session::getInstance();
        $this->session->open();
        $this->_initSession();
        // クッキー関連
        $this->cookie = new Cookie();
        // 出力関連初期化
        FuseBox::initialize();
        try {
            $this->extLibs = array_keys(FuseBox::get(FuseBox::CONFIGURES, SYSTEM_EXTRA_LIBS));
        } catch (Exception $e) {
            $this->extLibs = [];
        }
        $this->_initOutput();
    }

    /**
     * デストラクタ
     */
    function __destruct()
    {
        // セッション終了
        $this->session->close();
    }

    /**
     * セッション初期化
     *
     * @return void
     */
    private function _initSession() : void
    {
        $dateTime = date('Y-m-d H:i:s');
        $now = strtotime($dateTime);
        // 現セッションは有効か？
        $holdExpire = $this->session->Expire;
        $expiretTime = date('Y-m-d H:i:s', $now + $this->session->getExpireTime());
        if ($holdExpire && strtotime($holdExpire) < $now) {
            // セッションの期限切れであれば新しいセッションIDを払い出す
            session_regenerate_id();
            $this->session->Expire = $expiretTime;
        }
        // 基礎データを設定
        $this->session->IpAddress = $_SERVER['REMOTE_ADDR'];
        $this->session->Timestamp = $dateTime;
        if (empty($this->session->Expire)) {
            $this->session->Expire = $expiretTime;
        }
    }

    /**
     * 出力関連の初期化
     *
     * @return void
     */
    private function _initOutput()
    {
        $this->output = new dynamicStruct();
        $fUseConpose = FuseBox::get(Fusebox::CONFIGURES, COMPOSER_USE);
        foreach (FuseBox::get(Fusebox::CONFIGURES, SYSTEM_VIEW_EXTENTION) as $value) {
            if ($value['Active']) {
                if (!empty($value['Lib']) && !$fUseConpose) {
                    include_once $value['Lib'];
                }
                $this->output->setObject($value['Valiable'], new $value['View']);
            }
        }
    }

    /**
     * リダイレクトする
     *
     * @param string $url リダイレクトするURL
     * @return void
     */
    public function redirect(string $url) : void
    {
        header("Location: " . $url);
    }
}