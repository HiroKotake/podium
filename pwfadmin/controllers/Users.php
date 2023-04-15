<?php
/**
 * Users.php
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
use system\admin\AdminUserBean;
use system\supports\Validator;

/**
 * Users Class
 * 管理者管理クラス
 * 
 * @category Admin 
 * @package  Controller
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class Users extends AdminController
{


    function __construct()
    {
        parent::__construct();
        $this->adminInitial();
    }

    /**
     * ユーザリスト表示
     *
     * @return void
     */
    private function _list()
    {
        // リスト表示
        $this->data['UserList'] = $this->auth->getAllUserInfos();
        $this->data['UserCount'] = $this->auth->getUsersCount();
        $this->output->html->adminView('user/list.html', $this->data);
    }

    /**
     * ユーザリスト表示
     *
     * @return void
     */
    function list()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_MIDDLE
        );
        // ページ編集
        $this->session->AddingUser = [];
        $this->_list();
    }

    //  ユーザ追加
    /**
     * ユーザ追加画面を表示する
     *
     * @return void
     */
    function add()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // addConfirmからの戻り対応
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!empty($csrf) && !$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        $addingUser = @$this->session->AddingUser[$csrf];
        // データ準備
        if (empty($addingUser)) {
            $this->data['AddUser'] = [];
            $this->data['AddUser']['ID'] = '';
            $this->data['AddUser']['Password'] = '';
            $this->data['AddUser']['Category'] = PWF_AUTH_CATEGORY_DEVEL;
            $this->data['AddUser']['Level'] = PWF_AUTH_LEVEL_MIDDLE;
            $this->data['AddUser']['Name'] = '';
            $this->data['AddUser']['Mail'] = '';
        } else {
            $this->data['AddUser'] = $addingUser;
        }
        $this->data['ErrorMsg'] = '';
        // CSRF準備
        $this->data['Token'] = $this->getCSRF();
        // ページ表示
        $this->output->html->adminView('user/add.html', $this->data);
    }

    /**
     * ユーザ追加画面で入力された内容を表示する
     *
     * @return void
     */
    function addConfirm()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!', false)) {
            return;
        }
        // 必須項目チェック
        $id = $this->request->Id;
        $pwd = $this->request->pwd;
        $cpwd = $this->request->cpwd;
        $category = $this->request->category;
        $level = $this->request->level;
        $name = htmlspecialchars_decode($this->request->name);
        $mail = htmlspecialchars_decode($this->request->mail);
        $valid = new Validator();
        $validId = $valid->setValid($id, 'Idの入力で', Validator::IS_ALNUM_ONLY, [Validator::OPT_LENGTH_MAX => 12, Validator::OPT_LENGTH_MIN => 4]);
        $validPwd = $valid->setValid($pwd, 'パスワードの入力で', Validator::IS_ALNUM_ONLY, [Validator::OPT_LENGTH_MIN => 8]);
        $validCmp = $valid->setValidCompare($pwd, $cpwd, 'パスワードと確認用パスワードが');
        $validMail = $valid->setValid($mail, 'メールの入力に', Validator::IS_MAIL);
        $errMsg = $valid->getMessage();
        // データ集計
        $this->data['AddUser'] = [];
        $this->data['AddUser']['ID'] = $validId ? $id : '';
        $this->data['AddUser']['Password'] = $validPwd ? $pwd : '';
        $this->data['AddUser']['PasswordCmp'] = $validCmp ? $cpwd: '';
        $this->data['AddUser']['Category'] = $category;
        $this->data['AddUser']['Level'] = $level;
        $this->data['AddUser']['Name'] = $name;
        $this->data['AddUser']['Mail'] = $validMail ? $mail : '';
        $this->data['ErrorMsg'] = $errMsg;
        $this->data['Token'] = $this->getCSRF();
        // 問題がなければセッションにデータを保持させる
        $this->session->AddingUser = [$this->data['Token'] => $this->data['AddUser']];
        // テンプレート振り分け
        if (!empty($errMsg)) {
            // 入力にバリデーションエラーあり
            $this->data['ErrorMsg'] = $errMsg;
            $this->output->html->adminView('user/add.html', $this->data);
            return;
        }
        $this->output->html->adminView('user/addConfirm.html', $this->data);
    }

    /**
     * ユーザ追加を実施する
     *
     * @return void
     */
    function addExec()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // セッションからデータを取得
        $addingUser = $this->session->AddingUser[$csrf];
        if (empty($addingUser)) {
            // 対象ユーザ無エラー
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '対象データがありません！！';
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
            return;
        }
        // 管理者ユーザ追加
        $user = new AdminUserBean();
        $user->Category = $addingUser['Category'];
        $user->Level = $addingUser['Level'];
        $user->setProfile(PWF_ADMIN_PROFILE_NAME, $addingUser['Name']);
        $user->setProfile(PWF_ADMIN_PROFILE_MAIL, $addingUser['Mail']);
        $result = $this->auth->regist($addingUser['ID'], $addingUser['Password'], $user);
        $this->session->AddingUser = [];
        // 完了画面表示
        if (!$result) {
            // 失敗画面表示
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '管理者の追加に失敗しました。';
            $this->data['ErrorMessage'][] = $this->auth->getMessage();
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
            return;
        }
        // 成功した場合は管理者リストへ飛ばす
        $this->redirect('/admin/Users/list');
    }

    /****************************
     * ユーザ編集
     ****************************/
    /**
     * 管理者データ編集
     *
     * @return void
     */
    function edit()
    {
        // 自分への編集か確認
        $editUserHid = @$this->request->hd;
        if (empty($editUserHid) || $this->data['UserInfo']->Id == $editUserHid) { 
            $this->data['EditUser'] = $this->data['UserInfo'];
        } else {
            // 自分以外のユーザへの編集の場合、権限チェック
            $this->authorityCheck(
                $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
                $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
            );
            $this->data['EditUser'] = $this->auth->getUserInfoHID($editUserHid);
        }
        // ページ編集
        $this->data['Token'] = $this->getCSRF();
        $this->output->html->adminView('user/edit.html', $this->data);
    }
    /**
     * 管理者データ更新
     *
     * @return void
     */
    function editExec()
    {
        // 自分への編集か確認
        $editUserHid = $this->request->hdtoken;
        if ($this->data['UserInfo']->Id != $editUserHid) {
            // 自分自身以外への編集ならば権限チェック
            $this->authorityCheck(
                $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
                $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
            );
        }
        // 実行者権限比較 ... 実行者の権限よりも上位権限を付与することが出来ない！
        $level = $this->request->level;
        if ($this->data['UserInfo']->Level < $level) {
            $this->data['Message'] = 'Level Shortage !!';
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->output->html->adminView('error/authorityError.tpl', $this->data);
            return;
        }
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // 送信データ受信
        $hid = $this->request->hdtoken;
        $name = $this->request->name;
        $mail = $this->request->mail;
        $category = $this->request->category;
        $user = $this->auth->getUserInfoHID($hid);
        // 管理者データ更新
        $user->Category = $category;
        $user->Level = $level;
        $user->setProfile('Name', $name);
        $user->setProfile('Mail', $mail);
        $result = $this->auth->update($user);
        if (!$result) {
            // 更新失敗
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '管理者情報の更新に失敗しました。';
            $this->data['ErrorMessage'][] = $this->auth->getMessage();
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
        }
        $this->_list();
    }

    /**
     * ユーザ停止１（失効日設定)
     *
     * @return void
     */
    public function lapse()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // ページ編集
        $this->data['EditUser'] = $this->auth->getUserInfoHID($this->request->hd);
        $this->data['Token'] = $this->getCSRF();
        $this->session->EdittingUser = [$this->data['Token'] => $this->data['EditUser']];
        $datetime = $this->data['EditUser']->LapseDate;
        if (!empty($datetime)) {
            $parts = explode(' ', $datetime);
            $this->data['LapseDate'] = $parts[0];
            $this->data['LapseTime'] = $parts[1];
        }
        $this->output->html->adminView('user/lapse.html', $this->data);

    }
    
    /**
     * ユーザ停止１（失効日設定)実施
     *
     * @return void
     */
    public function setLapse()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // パラメータ取得
        $lapseDate = $this->request->LapseDate;
        $lapseTime = $this->request->LapseTime;
        $datetime = $lapseDate . ' ' . $lapseTime;
        // ユーザデータをセッションから取得
        $this->data['EditUser'] = $this->session->EdittingUser[$csrf];
        if (empty($this->data['EditUser'])) {
            // 対象ユーザ無エラー
            return;
        }
        // バリデーション
        $valid = new Validator();
        $resultDatetime = $valid->setValid($datetime, '', Validator::IS_DATETIME, [Validator::OPT_PATTERN => 'Y/M/D H:I:S']);
        $validErr = $valid->getDateFailedResult();
        if (!$resultDatetime) {
            // バリデーションエラー
            $this->data['ErrorMessage'] = $validErr;
            $this->data['Token'] = $this->getCSRF();
            $this->data['LapseDate'] = $lapseDate;
            $this->data['LapseTime'] = $lapseTime;
            $this->session->EdittingUser = [$this->data['Token'] => $this->data['EditUser']];
            $this->output->html->adminView('user/lapse.html', $this->data);
            return;
        }
        // 失効日設定
        $result = $this->auth->expel($this->data['EditUser']->Profile['Lid'], $datetime);
        $this->session->EdittingUser =[];
        if (!$result) {
            // エラーページ表示
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '管理者の失効日の設定に失敗しました。';
            $this->data['ErrorMessage'][] = $this->auth->getMessage();
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
        }
        $this->data['DateTime'] = $datetime;
        $this->output->html->adminView('user/lapseDone.html', $this->data);
    }

    /**
     * ユーザ停止２(即時停止)
     *
     * @return void
     */
    public function stop()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // ページ編集
        $this->data['EditUser'] = $this->auth->getUserInfoHID($this->request->hd);
        $this->data['Token'] = $this->getCSRF();
        $this->session->EdittingUser = [$this->data['Token'] => $this->data['EditUser']];
        $this->output->html->adminView('user/stop.html', $this->data);

    }

    /**
     * ユーザ停止２(即時停止)実行
     *
     * @return void
     */
    public function setStop()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // パラメータ取得
        $edittingUser = $this->session->EdittingUser[$csrf];
        if (empty($edittingUser)) {
            // 対象ユーザ無エラー
            return;
        }
        // 管理者権限停止
        $result = $this->auth->authStop($edittingUser->Profile['Lid']);
        $this->session->EdittingUser = [];
        if (!$result) {
            // 更新エラーページ表示
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '管理者の権限停止に失敗しました。';
            $this->data['ErrorMessage'][] = $this->auth->getMessage();
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
            return;
        }
        $this->data['EditUser'] = $edittingUser;
        $this->output->html->adminView('user/stopDone.html', $this->data);
    }

    /**
     * ユーザ再開
     *
     * @return void
     */
    public function restart()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // ページ編集
        $this->data['EditUser'] = $this->auth->getUserInfoHID($this->request->hd);
        $this->data['Token'] = $this->getCSRF();
        $this->session->EdittingUser = [$this->data['Token'] => $this->data['EditUser']];
        $this->output->html->adminView('user/restart.html', $this->data);
    }

    /**
     * ユーザ再開
     *
     * @return void
     */
    public function setRestart()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // パラメータ取得
        $edittingUser = $this->session->EdittingUser[$csrf];
        if (empty($edittingUser)) {
            // 対象ユーザ無エラー
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '対象データがありません！！';
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
            return;
        }
        // 管理者権限再開
        $result = $this->auth->authStart($edittingUser->Profile['Lid']);
        $this->session->EdittingUser =[];
        if (!$result) {
            // エラーページ表示
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = '管理者の権限再開に失敗しました。';
            $this->data['ErrorMessage'][] = $this->auth->getMessage();
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
            return;
        }
        $this->data['EditUser'] = $edittingUser;
        $this->output->html->adminView('user/restartDone.html', $this->data);
    }

    // パスワード変更
    /**
     * パスワード変更画面表示
     *
     * @return void
     */
    public function passwd()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_BOTTOM
        );
        // ページ編集
        $this->data['Token'] = $this->getCSRF();
        $hid = @$this->request->hd;
        if (empty($hid)) {
            $this->data['Self'] = true;
            $this->data['EdittingUser'] = $this->data['UserInfo'];
        } else {
            $this->data['Self'] = false;
            $this->data['EdittingUser'] = $this->auth->getUserInfoHID($hid);
        }
        $this->session->EdittingUser = [$this->data['Token'] => $this->data['EdittingUser']];
        $this->output->html->adminView('user/password.html', $this->data);
    }

    /**
     * パスワード変更実施
     *
     * @return void
     */
    public function setPasswd()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_BOTTOM
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        $edittingUser = @$this->session->EdittingUser[$csrf];
        // パラメータ取得
        $oldPassword = @$this->request->CP;
        $newPassword = $this->request->NP;
        $confPassword = $this->request->NC;
        // バリデーション
        $valid = new Validator();
        $valid->setValid($newPassword, 'パスワードの入力で', Validator::IS_ALNUM_ONLY, [Validator::OPT_LENGTH_MIN => 8]);
        $validCmp = $valid->setValidCompare($newPassword, $confPassword, 'パスワードと確認用パスワードが');
        $errMsg = $valid->getMessage();
        if (!empty($errMsg)) {
            $this->data['Error'] = $errMsg;
            // 新パスワードと確認用パスワードの入力違い
            if (!$validCmp) {
                $this->data['Error'][] = 'パスワードと確認用パスワードが異なります。';
            }
            $token = $this->getCSRF();
            $this->data['Token'] = $token;
            if ($forceFlag == 0 && empty($edittingUser)) {
                $this->data['Self'] = 0;
            } else {
               $this->session->EdittingUser = [$token => $edittingUser]; 
            }
            $this->output->html->adminView('user/password', $this->data);
        }

        // 新パスワード設定
        if ($this->data['UserInfo']->Id == $edittingUser->Id) {
            $result = $this->auth->changePassword($edittingUser->Profile['Lid'], $newPassword, $oldPassword);
        } else {
            $result = $this->auth->forceChangePassword($edittingUser->Profile['Lid'], $newPassword);
        }
        $this->session->EdittingUser = [];
        if (!$result) {
            // エラーページ表示
            $this->data['ErrorMessage'] = [];
            $this->data['ErrorMessage'][] = 'パスワードの変更に失敗しました。';
            $this->data['ErrorMessage'][] = $this->auth->getMessage();
            $this->data['JumpPage'] = '/admin/Users/list';
            $this->data['JumpPageName'] = '管理者一覧へ';
            $this->output->html->adminView('error/error.html', $this->data);
            return;
        }
        // 完了画面表示
        $this->data['EdittingUser'] = $edittingUser;
        $this->output->html->adminView('user/passwordDone.html', $this->data);
    }
}