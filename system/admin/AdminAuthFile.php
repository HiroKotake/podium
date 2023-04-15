<?php
/**
 * AdminAuthFile.php
 * 
 * @category  Admin 
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\admin;

use Exception;
use system\admin\AdminUserBean;
use system\supports\LogWriter;
use system\session\Session;

/**
 * AdminAuthFile class
 * 
 * @category Admin 
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class AdminAuthFile
{
    const POSTFIX = '.auth';
    /**
     * 格納先設定情報
     * 
     *
     * @var array
     */
    protected $strageInfo = [];
    /**
     * エラー発生時のメッセージ
     *
     * @var string
     */
    protected string $message = '';
    /**
     * 操作ユーザ
     *
     * @var string
     */
    private string $_lid = '';

    /**
     * コンストラクタ
     *
     * @param array $strageInfo
     */
    function __construct(array $strageInfo)
    {
        $this->strageInfo = $strageInfo;
        $this->_lid = Session::get('Id') ?: '';
    }

    /**
     * 初期構築済みか確認
     *
     * @return boolean 初期化済みならば trueを、でいないならば falseを返す
     */
    public function isInitialized() : bool
    {
        $list = scandir($this->strageInfo['directory']);
        $exist = false;
        foreach ($list as $file) {
            if (preg_match('/[!-~]+' . self::POSTFIX . '$/', $file)) {
                $exist = true;
                break;
            }
        } 
        return $exist;
    }

    /**
     * エラーメッセージを取得する
     *
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * 登録している全ユーザ情報を取得する
     *
     * @return array AdminUserBeanを要素した配列
     */
    public function getAllUser() : array
    {
        $AdminUserList = [];
        $list = scandir($this->strageInfo['directory']);
        LogWriter::set(LogWriter::LOG_ADMIN, "Get All User Info from auth file by $this->_lid.", PWF_ADMIN_LOG_DELAY);
        foreach ($list as $file) {
            if (preg_match('/[!-~]+' . self::POSTFIX . '$/', $file)) {
                $fileName = $this->strageInfo['directory'] . DIRECTORY_SEPARATOR . $file;
                try {
                    $hFile = fopen($fileName, 'r');
                    $AdminUserList[] 
                        = new AdminUserBean(fread($hFile, filesize($fileName)));
                    fclose($hFile);
                } catch (Exception $e) {
                    $this->message 
                        .= '[OPEN ERROR](' . $file .') ' 
                        . $e->getMessage() . PHP_EOL;
                }
            }
        } 
        return $AdminUserList;
    }

    /**
     * ID重複チェック
     *
     * @param  string $hashedId
     * @return boolean 重複していない場合に trueを返し、重複した場合は falseを返す
     */
    public function checkDuplicate(string $hashedId) : bool
    {
        LogWriter::set(LogWriter::LOG_ADMIN, "Check Duplicate User($hashedId) from auth file by $this->_lid.", PWF_ADMIN_LOG_DELAY);
        if (file_exists($this->strageInfo['directory'] . DIRECTORY_SEPARATOR . $hashedId . self::POSTFIX)) {
            return false;
        }
        return true;
    }

    /**
     * 指定したユーザ情報を取得する
     *
     * @param string $hashedId ハッシュ化済みユーザID
     * @return AdminUserBean|boolean 情報取得に成功したら管理者情報を取得する、失敗した場合はfalseを返す
     */
    public function getUserInfo(string $hashedId)
    {
        $file = $this->strageInfo['directory'] . DIRECTORY_SEPARATOR . $hashedId . self::POSTFIX;
        if (!file_exists($file)) {
            $this->message = 'Auth File Is Not Found !!';
            return false;
        }
        try {
            LogWriter::set(LogWriter::LOG_ADMIN, "TRY Get User($hashedId) from auth file by $this->_lid.", PWF_ADMIN_LOG_DELAY);
            $hFile = fopen($file, 'r');
            $content = fread($hFile, filesize($file));
            fclose($hFile);
            return new AdminUserBean($content);
        } catch (Exception $e) {
            $this->message = $e->getMessage();
        }
        return false;
    }

    /**
     * 管理者情報を登録する
     *
     * @param  AdminUserBean $adminInfo 管理者情報
     * @return boolean 登録に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function regist(AdminUserBean $adminInfo) : bool
    {
        // シリアライズ
        $userInfoStr = $adminInfo->toSerialize();
        // ファイル書き込み
        $file = $this->strageInfo['directory'] . DIRECTORY_SEPARATOR . $adminInfo->Id . self::POSTFIX;
        try {
            $hFile = fopen($file, 'a+');
            fwrite($hFile, $userInfoStr);
            LogWriter::set(LogWriter::LOG_ADMIN, 'TRY REGIST BY $this->_lid', PWF_ADMIN_LOG_DELAY);
            LogWriter::set(LogWriter::LOG_ADMIN, $userInfoStr, PWF_ADMIN_LOG_DELAY);
            LogWriter::set(LogWriter::LOG_ADMIN, var_export($adminInfo, true), PWF_ADMIN_LOG_DELAY);
            fclose($hFile);
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 管理者情報を更新する
     *
     * @param AdminUserBean $adminInfo 管理者情報
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function update(AdminUserBean $adminInfo) : bool
    {
        // シリアライズ
        $userInfoStr = $adminInfo->toSerialize(); 
        // ファイル書き込み
        $file = $this->strageInfo['directory'] . DIRECTORY_SEPARATOR . $adminInfo->Id . self::POSTFIX;
        try {
            $hFile = fopen($file, 'w');
            fwrite($hFile, $userInfoStr);
            LogWriter::set(LogWriter::LOG_ADMIN, 'TRY UPDATE BY $this->_lid', PWF_ADMIN_LOG_DELAY);
            LogWriter::set(LogWriter::LOG_ADMIN, $userInfoStr, PWF_ADMIN_LOG_DELAY);
            LogWriter::set(LogWriter::LOG_ADMIN, var_export($adminInfo, true), PWF_ADMIN_LOG_DELAY);
            fclose($hFile);
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
        return true;
    }
}