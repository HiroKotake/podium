<?php
/**
 * Ignition.php
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
use system\settings\FuseBox;
use system\supports\LogWriter;

/**
 * Ignition Class
 * 
 * @category Core
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class Ignition
{
    /**
     * コントローラー名
     *
     * @var string
     */
    protected string $controllerName;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        FuseBox::initialize();
        HttpRequest::initialize();
    }

    /**
     * config/bootstrap.phpで設定されている自動メソッドを実行する
     *
     * @param array $hook
     * @return void
     */
    function doHookExec(array $hook) : bool
    {
        // 設定が空の場合は実行しない
        if (empty($hook)) {
            return true;
        }
        // actionに応じて処理を変更
        $resut = true;
        foreach ($hook as $data) {
            if (!$data[AUTO_INDEX_FLAG]) {
                continue;
            }
            switch ($data['action']) {
            case 'EXEC':
                $result = $data['method']($data['params']);
            break;
            case 'NEW':
                $inst = new $data['class']();
                $method = $data['method'];
                $result = $inst->$method($data['params']);
            break;
            }
        }
        return $result;
    }
        
    /**
     * 処理開始
     *
     * @return void
     */
    public function play()
    {
        if (!$this->initial()){
            return;
        }
        if (!$this->preShow()) {
            return;
        }
        if (!$this->show()) {
            return;
        }
        if (!$this->postShow()) {
            return;
        }
        if (!$this->final()) {
            return;
        }
    }

    /**
     * 開始処理
     *
     * @return boolean
     */
    public function initial() : bool
    {
        /**
         * 自動実行
         */
        $this->doHookExec(FuseBox::get(FuseBox::CONFIGURES, AUTO_EXEC_INITIAL));
        /**
         * ロギング開始
         */
        if (ACCESS_LOG_ON) {
            LogWriter::open(LogWriter::LOG_ACCESS);
        }
        if (SQL_LOG_ON) {
            LogWriter::open(LogWriter::LOG_SQL);
        }
        return true;
    }

    /**
     * レスポンス出力前処理
     *
     * @return boolean
     */
    public function preShow() : bool
    {
        /**
         * 自動実行
         */
        $this->doHookExec(FuseBox::get(FuseBox::CONFIGURES, AUTO_EXEC_PRESHOW));
        /**
         * リクエスト情報処理
         */
        $this->controllerName = HttpRequest::getTargetController();
        /**
         * 設定されていないコントローラーが指定されている場合は、停止。
         * htmlやhtmを拡張子にもつファイルが指定された場合は、それを出力する。
         */
        if (HttpRequest::isIllegalController()) {
            $hyperTextFile = HttpRequest::getHtmlContests();
            if (!empty($hyperTextFile)) {
                // htmlファイルが指定されている場合は出力
                echo file_get_contents($hyperTextFile);
                return false;
            }
            if (empty($this->controllerName)) {
                header("HTTP/1.1 404 Not Found");
                $page = FuseBox::get(FuseBox::CONFIGURES, SYSTEM_ERROR_PAGES, 'HTTP404');
                echo file_get_contents(RESOURCE_PATH . 'HttpStatus' . DIRECTORY_SEPARATOR . $page);
                return false;
            }
        }

        /**
         * コントローラに指定がない場合は規定値を使って処理を継続する
         */
        if (empty($this->controllerName)) {
            $this->controllerName = HttpRequest::reset(
                HttpRequest::isAdmin() ?
                PWF_ADMIN_DIR . '/' . FuseBox::get(FuseBox::CONFIGURES, PWF_ADMIN_PAGE, DEFAULT_PAGE) : 
                FuseBox::get(FuseBox::CONFIGURES, DEFAULT_PAGE)
            );
        }
        return true;
    }

    /**
     * レスポンス出力
     *
     * @return boolean
     */
    public function show() : bool
    {
        /**
         * 自動実行
         */
        $this->doHookExec(FuseBox::get(FuseBox::CONFIGURES, AUTO_EXEC_SHOW));
        /**
         * サービス実施
         * 指定されたコントロールのメソッドを実行する
         */
        $ctrlClass = HttpRequest::getFullControllerName();
        $controllerMethod = HttpRequest::getControllerMethod();
        $doCtrl = new $ctrlClass();
        if (method_exists($doCtrl, $controllerMethod)) {
            $doCtrl->$controllerMethod();
        } else {
            // 対象サービス無し
            header("HTTP/1.1 404 Not Found");
            $page = FuseBox::get(FuseBox::CONFIGURES, SYSTEM_ERROR_PAGES, 'HTTP404'); 
            echo file_get_contents(RESOURCE_PATH . 'HttpStatus' . DIRECTORY_SEPARATOR . $page);
            return false;
        }
        return true;
    }

    /**
     * レスポンス出力後後処理
     *
     * @return boolean
     */
    public function postShow() : bool
    {
        /**
         * 自動実行
         */
        $this->doHookExec(FuseBox::get(FuseBox::CONFIGURES, AUTO_EXEC_POSTSHOW));
        return true;
    }

    /**
     * 終了処理
     *
     * @return boolean
     */
    public function final() : bool
    {
        /**
         * 自動実行
         */
        $this->doHookExec(FuseBox::get(FuseBox::CONFIGURES, AUTO_EXEC_FINAL));
        /**
         * ロギング終了
         */
        if (ACCESS_LOG_ON) {
            LogWriter::close(LogWriter::LOG_ACCESS);
        }
        if (SQL_LOG_ON) {
            LogWriter::close(LogWriter::LOG_SQL);
        }
        return true;
    }
}