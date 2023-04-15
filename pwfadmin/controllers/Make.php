<?php
/**
 * Make.php
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

use pwfadmin\logics\LogicMake;
use system\admin\AdminController;

/**
 * Make Class
 * 
 * @category Admin 
 * @package  Controller
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class Make extends AdminController
{
    protected array $makeConfig = [];

    /**
     * コンストラクタ
     */
    function __construct()
    {
        parent::__construct();
        $this->adminInitial();
        include PWF_ADMIN_PATH . 'config/ClassMake.php';
        $this->makeConfig = $createClass;
    }

    /**
     * クラス作成画面表示
     *
     * @return void
     */
    public function plan()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_DEVEL,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_BOTTOM
        );
        $this->data['Token'] = $this->getCSRF();
        $this->data['DocTemp'] = $this->makeConfig;
        $this->data['Authoer'] = $this->data['UserInfo']->Name . ' <' . $this->data['UserInfo']->Mail . '>';
        $this->output->html->adminView('class/plan.html', $this->data);
    }

    /**
     * クラス作成
     *
     * @return void
     */
    public function build()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, PWF_AUTH_CATEGORY_DEVEL,
            $this->data['UserInfo']->Level, PWF_AUTH_LEVEL_BOTTOM
        );
        // CSRFチェック
        $csrf = $this->request->Token;
        if (!$this->checkCSRF($csrf, 'Token Error !!')) {
            return;
        }
        // パラメータ取得
        $params = [];
        $params['Target'] = $this->request->Target;
        $params['Type'] = $this->request->Type;
        if ($params['Target'] == PWF_MAKE_TARGET_ADM) {
            $params['Category'] = $this->request->Category;
            $params['Level'] = $this->request->Level;
        }
        $params['Name'] = $this->request->Name;
        $params['Docs'] = [];
        $params['Docs']['Category'] = $this->request->DocCategory;
        $params['Docs']['Package'] = $this->request->DocPackage;
        $params['Docs']['Author'] = $this->request->DocAuthor;
        $params['Docs']['License'] = $this->request->DocLicense;
        $params['Docs']['Version'] = $this->request->DocVersion;
        $params['Docs']['Copyright'] = $this->request->DocCopyright;
        // クラス作成
        $makeClass = new LogicMake($this->makeConfig);
        $result = $makeClass->build($params);
        $this->data['Class'] = $params['Name'];
        $this->data['Result'] = $result;
        $this->data['ErrorMessage'] = '';
        if (!$result) {
            $this->data['ErrorMessage'] = $makeClass->getMessage();
        }
        $this->output->html->adminView('class/build.html', $this->data);
    }
}