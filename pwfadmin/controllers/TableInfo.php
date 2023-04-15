<?php
/**
 * TableInfo.php
 * 
 * @category  Controller 
 * @package   Table
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\controllers;

use pwfadmin\logics\LogicTableInfo;
use system\admin\AdminController;

/**
 * TableInfo Class
 * 
 * @category Controller 
 * @package  Table
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class TableInfo extends AdminController
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
     * テーブル一覧を表示
     *
     * @return void
     */
    public function list()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_HIGH
        );
        $this->data['Schema'] = @$this->request->Schema;
        $lTableInfo = new LogicTableInfo();
        $list = $lTableInfo->getTableList((string) $this->data['Schema']);
        $this->data['List'] = $lTableInfo->getMaskedList($list);
        $sList = $lTableInfo->getSchemaList();
        $this->data['Schemaes'] = $lTableInfo->getMaskedList($sList);
        // セッションにデータテーブルを補完
        $this->session->DBInfoMap = [
            'Schema' => $this->data['Schema'],
            'TableList' => $this->data['List'],
        ];
        // ビュー表示
        $this->data['Token'] = $this->getCSRF();
        $this->output->html->adminView('table/list.html', $this->data);
    }

    /**
     * テーブルの属性を表示
     *
     * @return void
     */
    public function columns()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_HIGH
        );
        $dbInfoMap = @$this->session->DBInfoMap;
        if (empty($dbInfoMap)) {
            $this->data['Error'] = 'Not FAILD CALL !!';
            $this->output->html->adminView('table/colums.html', $this->data);
            return;
        }
        $tblId = $this->request->tbl;
        $table = $dbInfoMap['TableList']['key_' . $tblId];
        $lTableInfo = new LogicTableInfo();
        $this->data['Schema'] = $dbInfoMap['Schema'];
        $this->data['Table'] = $table;
        $this->data['Columns'] = $lTableInfo->getColumnsInfo($dbInfoMap['Schema'], $table);
        // ビュー表示
        $this->output->html->adminView('table/columns.html', $this->data);
    }

    public function truncate()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_BOTH,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_MASTER
        );
        // CSRFチェック
        $csrf = $this->request->token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // セッションからデータを取得
        $dbInfoMap = @$this->session->DBInfoMap;
        if (empty($dbInfoMap)) {
            $this->data['Error'] = 'Not FAILD CALL !!';
            $this->output->html->adminView('table/colums.html', $this->data);
            return;
        }
        $resetNumber = $this->request->reset;
        $tblId = $this->request->tbl;
        $table = $dbInfoMap['TableList']['key_' . $tblId];
        $this->data['Schema'] = $dbInfoMap['Schema'];
        $this->data['Table'] = $table;
        // TRUNCATE処理
        $lTableInfo = new LogicTableInfo();
        $this->data['Result'] = $lTableInfo->truncate($this->data['Schema'], $table, $resetNumber);
        // ビュー表示
        $this->output->html->adminView('table/truncate.html', $this->data);
    }
}