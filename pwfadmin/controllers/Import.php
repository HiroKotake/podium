<?php
/**
 * Import.php
 * 
 * @category  Controller 
 * @package   Import
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\controllers;

use pwfadmin\logics\LogicImport;
use system\admin\AdminController;

/**
 * Import Class
 * 
 * @category Controller 
 * @package  Import
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class Import extends AdminController
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
     * リスト表示
     *
     * @return void
     */
    public function show()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_TOP
        );
        // セッションに含まれる既存データ削除
        $this->session->ConfirmTable = [];
        // パラメータ取得
        $this->data['Dir'] = @$this->request->dir;
        // データ取得
        $lImport = new LogicImport();
        //$list = $lImport->getImportList($this->data['Dir']);
        $list = $lImport->getResourceList("csv", $this->data['Dir']);
        $this->data['Dirs'] = $list['Dirs'];
        $this->data['Files'] = $list['Files'];
        // ビュー表示
        $this->data['Token'] = $this->getCSRF();
        $this->output->html->adminView('import/show.html', $this->data);
    }

    /**
     * 実行前確認
     *
     * @return void
     */
    public function confirm()
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
        $this->output->html->adminView('import/confirm.html', $this->data);
    }

    /**
     * インポート実行
     *
     * @return void
     */
    public function exec()
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
        // データ取り出し
        $list = $this->session->ConfirmTable;
        $lImport = new LogicImport();
        $this->data['Result'] = $lImport->execImports($list['Files'], $list['Database']);
        $this->data['BackDir'] = $list['Database'];
        // ビュー表示
        $this->output->html->adminView('import/exec.html', $this->data);
        // セッションに含まれる既存データ削除
        $this->session->ConfirmTable = [];
    }
}