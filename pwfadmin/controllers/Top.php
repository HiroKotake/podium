<?php
/**
 * Top.php
 * 
 * @category  Admin 
 * @package   Controller
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\controllers;

use system\admin\AdminController;

/**
 * Top Class
 * 管理者用トップ画面。
 * ログイン・ログアウトを管轄するクラス。
 * 
 * @category Admin 
 * @package  Controller
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class Top extends AdminController
{
    /**
     * 管理者トップ画面
     *
     * @return void
     */
    function index()
    {
        // 認証要求不要場合の処理
        if (!$this->needLogin) {
            $this->adminInitial();
        }
        // ログイン強制付き管理画面表示
        $this->data['Token'] = $this->getCSRF();
        if ($this->needLogin) {
            // 認証要求
            $this->output->html->adminView('top/index', $this->data); 
            return;
        }
        // 認証要求回避
        // $this->data['Logined'] = $this->auth->isLogined(); // 現在のログイン状態
        $this->output->html->adminView('top/index', $this->data); 
    }

    /**
     * ログイン認証
     *
     * @return void
     */
    function auth()
    {
        // CSRFチェック
        $csrf = $this->request->token;
        if (!$this->checkCSRF($csrf, 'ログイン入力待機時間切れです。')) {
            return;
        }
        // ログイン認証
        $lid = $this->request->uid;
        $pwd = $this->request->pwd;
        $result = $this->auth->login($lid, $pwd);
        if ($result) {
            // 認証OK
            $this->data['UserInfo'] = $this->auth->getCurrentUser();
        } else {
            // 認証NG
            $this->data['Message'] = 'ユーザIDもしくはパスワードが違います！';
            $this->data['Token'] = $this->getCSRF();
        }
        $this->data['Logined'] = $this->auth->isLogined(); // 現在のログイン状態
        $this->output->html->adminView('top/index', $this->data); 
    }

    /**
     * ログアウト
     *
     * @return void
     */
    function logout()
    {
        $this->auth->logout();
        $this->data['Logined'] = false;   // 現在のログイン状態
        $this->data['Token'] = $this->getCSRF();
        $this->output->html->adminView('top/index', $this->data);
    }
}