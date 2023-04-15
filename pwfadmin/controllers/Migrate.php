<?php
/**
 * Migrate.php
 * 
 * @category  Controller 
 * @package   Migrate
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\controllers;

use system\admin\AdminController;
use pwfadmin\logics\LogicMigrate;

/**
 * Migrate Class
 * 
 * @category Controller 
 * @package  Migrate
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class Migrate extends AdminController
{
    /**
     * Constructer
     */
    function __construct()
    {
        parent::__construct();
        $this->adminInitial();
    }

    /**
     * リストを表示させる
     *
     * @return void
     */
    public function show()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_DEVEL,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // セッションに含まれる既存データ削除
        $this->session->ConfirmTable = [];
        // パラメータ取得
        $this->data['Dir'] = @$this->request->dir;
        // リスト取得
        $lMigrate = new LogicMigrate();
        //$this->data['List'] = $lMigrate->getMigrationList($this->data['Dir']);
        $list = $lMigrate->getResourceList('sql', $this->data['Dir']);
        $this->data['Dirs'] = $list['Dirs'];
        $this->data['Files'] = $list['Files'];
        // ビュー表示
        $this->data['Token'] = $this->getCSRF();
        $this->output->html->adminView('migrate/show.html', $this->data);
    }

    /**
     * 確認画面表示
     *
     * @return void
     */
    public function confirm()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_DEVEL,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // パラメーター取得
        $this->data['Files'] = $this->request->Files;
        $this->data['Dir'] = $this->request->Dir;
        if (count($this->data['Files']) > 0) {
            $this->session->ConfirmTable = [
                'Files' => $this->data['Files'], 
                'Database' => $this->data['Dir']
            ];
        }
        // ビュー表示
        $this->data['Token'] = $this->getCSRF();
        $this->output->html->adminView('migrate/confirm.html', $this->data);
    }

    /**
     * テーブル作成実行
     *
     * @return void
     */
    public function create()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_DEVEL,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // パラメータ取得
        $targets = $this->session->ConfirmTable;
        $this->data['Dir'] = $targets['Database'];
        $this->data['Files'] = $targets['Files'];
        // SQL実行
        $lMigrate = new LogicMigrate();
        $this->data['Result'] = $lMigrate->execMigrate($targets);
        // ビュー表示
        $this->output->html->adminView('migrate/create.html', $this->data);
        // セッションに含まれる既存データ削除
        $this->session->ConfirmTable = [];
    }

}