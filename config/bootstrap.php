<?php
/**
 * bootstrat.php
 * システム起動に必要な情報を記述
 * 
 * @category  Configure
 * @package   Basic
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
$bootConfigure = [

    /**
     * オートロードにComposerを使用する
     * true : 使用する
     * false : 使用しない
     */
    COMPOSER_USE => true,
    //COMPOSER_USE => false,

    /**
     * コアライブラリ指定
     */
    SYSTEM_BASE_LIBS => [
        'AutoLoder' => 'system\core\AutoLoader',    // オートローダー
        'request' => 'system\core\HttpRequest',     // リクエスト解析関連
    ],

    /**
     * 外部ライブラリ指定
     */
    SYSTEM_EXTRA_LIBS => [
    ],

    /**
     * Viewライブラリ設定
     */
    SYSTEM_VIEW_EXTENTION => [
        'HTML' => [
            'Active' => true,
            'Valiable' => 'html',
            'View' => '\system\view\ViewHtml',
            'Lib' => null
        ],
        'XML' => [
            'Active' => true,
            'Valiable' => 'xml',
            'View' => '\system\view\ViewXML',
            'Lib' => null
        ],
        'JSON' => [
            'Active' => true,
            'Valiable' => 'json',
            'View' => '\system\view\ViewJson',
            'Lib' => null
        ],
        'Smarty' => [
            'Active' => true,
            'Valiable' => 'smarty',
            'View' => '\system\view\ViewSmarty',
            'Lib' => 'libraries/smarty/libs/Smarty.class.php' 
        ],
    ],

    // ユーザ画面関連
    DEFAULT_PAGE => 'welcome',

    // 管理者画面関連
    PWF_ADMIN_PAGE => [
        DEFAULT_PAGE => 'Top/index',   // コントローラーの指定がない場合に使用されるコントローラーとサービス
    ],

    // エラー画面
    SYSTEM_ERROR_PAGES => [
        'HTTP404' => '404.html',
    ],

    AUTO_EXEC_INITIAL => [
        /* 設定サンプル
        'testAlpha' => [
            AUTO_INDEX_ACTION => AUTO_ACTION_NEW,
            AUTO_INDEX_CLASS => 'temp\test\check\AutotestAlpha',
            AUTO_INDEX_METHOD => 'show',
            AUTO_INDEX_PARAMS => ['message' => 'AUTO INITIAL (Alpha)',],
            AUTO_INDEX_FLAG => true,
        ],
        'testBeta' => [
            AUTO_INDEX_ACTION => AUTO_ACTION_EXEC,
            AUTO_INDEX_CLASS => '',
            AUTO_INDEX_METHOD => 'temp\test\check\AutotestBeta::show',
            AUTO_INDEX_PARAMS => ['message' => 'AUTO INITIAL (Beta)',],
            AUTO_INDEX_FLAG => true,
        ],
        */
    ],
    AUTO_EXEC_PRESHOW => [],
    AUTO_EXEC_SHOW => [],
    AUTO_EXEC_POSTSHOW => [],
    AUTO_EXEC_FINAL => [],
];