<?php
/**
 * AdminController.php
 * 
 * @category  Admin 
 * @package   Controller
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\admin;

use system\core\Controller;
use system\admin\AdminAuth;
use system\admin\AdminUserBean;
use system\supports\CSRF;

/**
 * AdminController Class
 * 
 * @category Admin 
 * @package  Controller
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class AdminController extends Controller
{
    /**
     * 認証機能
     *
     * @var AdminAuth
     */
    protected AdminAuth $auth;
    /**
     * ログイン認証要求
     *
     * @var boolean
     */
    protected bool $needLogin = true;
    /**
     * ログインユーザ情報
     *
     * @var AdminUserBean
     */
    protected AdminUserBean $adminUser;
    /**
     * 出力データ
     *
     * @var array
     */
    protected $data = [];

    /**
     * コンストラクタ
     */
    function __construct()
    {
        parent::__construct();
        $this->data['UserInfo'] = '';
        $this->auth = new AdminAuth();
        // 管理者認証OFFの確認
        // 管理者認証がONの場合は、ログイン状態が常に維持される様になっている
        $this->needLogin = $this->auth->withoutAuthLogin();
    }

    /**
     * ログイン状態に無い場合に,管理者トップへ飛ばす
     *
     * @return void
     */
    protected function jumpLoginPage()
    {
        $this->data['Logined'] = $this->auth->isLogined(); // 現在のログイン状態
        if (!$this->data['Logined']) {
            $this->redirect('/admin/Top');
        }
    }

    /**
     * ページ初期化ツール
     * Topコントローラー以外のページのコンストラクタに設定
     *
     * @return void
     */
    protected function adminInitial()
    {
        $this->jumpLoginPage(); // ログイン状態に無い場合、管理者トップへ飛ばす
        // 共通出力データ設定
        //$this->data['Logined'] = $this->auth->isLogined(); // 現在のログイン状態
        $this->data['UserInfo'] = $this->auth->getCurrentUser();
    }

    /**
     * 権限を満たしているか確認し、権限不足の場合はエラーページを表示させる
     *
     * @param integer $category 対象ユーザのカテゴリ
     * @param integer $needCategory 必要なカテゴリ
     * @param integer $level 対象ユーザの権限レベル
     * @param integer $needLevel 必要なレベル
     * @return boolean
     */
    protected function authorityCheck(int $category, int $needCategory, int $level, int $needLevel) : bool
    {
        if ($needCategory == PWF_AUTH_CATEGORY_BOTH || $category == $needCategory) {
            if ($level >= $needLevel) {
                return true;
            }
        }
        // 権限不適合によりエラーページへ飛ばす
        $data = [];
        $data['Category'] = $category;
        $data['NeedCategory'] = $needCategory;
        $data['Level'] = $level;
        $data['NeedLevel'] = $needLevel;
        // 権限不足のエラーページを表示
        $this->output->html->adminView('error/authorityError.html', $data);
    }

    /**
     * 新しいCSRFトークンを生成し、セッションに格納。
     *
     * @return string CSRFトークン文字列
     */
    protected function getCSRF() : string
    {
        $token = CSRF::get();
        CSRF::setCsrfToken($token);
        return $token;
    }

    /**
     * CSRFトークンをチェックする
     * 管理者用のページであるのでトークンエラーが発生した場合は、ログイン状態を解除し、トップページに戻す
     *
     * @param string $csrf CSRFトークン文字列
     * @param string $message CSRFチェックNGの場合に、表示させるメッセージ文字列
     * @param boolean $autoLogout false指定でCSRFトークンエラーが発生しても、自動的にログアウトさせない(省略時、true)
     * @return boolean CSRFチェックがOKならば trueを返し、NGならば falseを返す
     *                 (注意) falseを返した場合は、エラーページを送信済みなので、処理をreturnで抜けること！！
     */
    protected function checkCSRF(string $csrf, string $message, bool $autoLogout = true) : bool
    {
        if (!CSRF::checkCsrfToken($csrf)) {
            $data = [];
            if ($autoLogout) {
                // 強制ログアウト
                $this->auth->logout();
                $data['Logined'] = false;   // 現在のログイン状態
            }
            // CSRFチェックエラー
            $data['CsrfFail'] = true;
            $data['Message'] = $message;
            $data['Token'] = $this->getCSRF();
            $this->output->html->adminView('error/tokenError.html', $data); 
            return false;
        }
        return true;
    }
}