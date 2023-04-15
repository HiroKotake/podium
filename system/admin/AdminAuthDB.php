<?php
/**
 * AdminAuthDB.php
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
use PDO;
use PDOException;
use system\database\DBConnector;
use system\admin\AdminUserBean;
use system\supports\LogWriter;
use system\session\Session;


/**
 * AdminAuthDB
 * 
 * @category  Admin 
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 */
class AdminAuthDB
{
    /**
     * SQL Statement
     */
    const DB_SELECT = 'SELECT * FROM `AdminUsers` WHERE `Id` = :Id';
    const DB_INSERT = 'INSERT INTO `AdminUsers`(`Id`,`Password`, `Category`, `Level`, `Profile`, `CreateDate`,`LapseDate`, `StopFlag`)'
                    . ' VALUES('
                    . ':Id,' 
                    . ' :Password,' 
                    . ' :Category,'
                    . ' :Level,'
                    . ' :Profile,'
                    . ' :CreateDate,'
                    . ' :LapseDate,'
                    . ' :StopFlag'
                    . ')';
    const DB_UPDATE = 'UPDATE `AdminUsers`'
                    . ' SET' 
                    . ' `Password` = :Password,' 
                    . ' `Category` = :Category,'
                    . ' `Level` = :Level,'
                    . ' `Profile` = :Profile,'
                    . ' `CreateDate` = :CreateDate,'
                    . ' `LapseDate` = :LapseDate,'
                    . ' `StopFlag` = :StopFlag'
                    . ' WHERE `Id` = :Id';
    const DB_EXIST_SQLITE = 'SELECT * FROM sqlite_master WHERE TYPE="table" AND name="AdminUsers"';
    const DB_EXIST_MYSQL  = 'SHOW TABLES LIKE `AdminUsers`';
    const DB_EXIST_PGSQL  = 'SELECT * FROM information_schema.tables WHERE table_name = "AdminUsers"';
    const DB_EXIST_COL    = 'SELECT * FROM `AdminUsers`';

    /**
     * 格納先設定情報
     * 
     *
     * @var array
     */
    protected array $strageInfo = [];
    /**
     * DBコネクション
     *
     * @var PDO
     */
    protected PDO $db;
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
     *                 falseの場合はDB接続エラーの可能性があるので、getMessage()
     *                 で内容を確認すること。メッセージが空白なら初期化していないことを
     *              　　　　　　示している
     */
    public function isInitialized() : bool
    {
        $exist = false;
        if (empty($this->db) && !$this->openStrage()) {
            return false;
        }
        // テーブルの存在を確認し、存在した場合はレコードが登録されているか確認する。
        $sql = '';
        switch($this->strageInfo['type']) {
        case STRAGE_TYPE_SQLITE:
            $sql = self::DB_EXIST_SQLITE;
            break;
        case STRAGE_TYPE_MYSQL:
            $sql = self::DB_EXIST_MYSQL;
            break;
        case STRAGE_TYPE_PGSQL:
            $sql = self::DB_EXIST_PGSQL;
            break;
        default:
            $this->message = 'Undifned Database Type';
            break;
        }
        if (!empty($this->message)) {
            return false;
        }
        try {
            $result = $this->db->query($sql);
            LogWriter::set(LogWriter::LOG_ADMIN, $sql, ADMIN_LOG_DELAY);
            if ($result->columnCount() > 0) {
                $lines = $this->db->query(self::DB_EXIST_COL);
                LogWriter::set(LogWriter::LOG_ADMIN, self::DB_EXIST_COL, ADMIN_LOG_DELAY);
                if ($lines->columnCount() > 0) {
                    $exist = true;
                }
            }
        } catch (PDOException $e) {
            $this->message = $e->getMessage();
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
     * 個人認証設定DBを開く
     *
     * @return boolean DBのオープン成功時に trueを、失敗時にfalseを返す。
     *                 falseを返した場合はエラーメッセージが格納されるので、
     *                 getMessage()で取得できる。
     */
    protected function openStrage() : bool
    {
        try {
            $this->db = DBConnector::getDBConnectByInfo($this->strageInfo);
            if (empty($this->db)) {
                $this->message = DBConnector::getErrorMessage();
                return false;
            }
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 登録している全ユーザ情報を取得する
     *
     * @return array|boolean AdminUserBeanを要素した配列。取得に失敗した場合はfalseを返す
     */
    public function getAllUser()
    {
        if (empty($this->db) && !$this->openStrage()) {
            return false;
        }
        try {
            $sql = 'SELECT * FROM `AdminUsers`';
            $result = $this->db->query($sql);
            LogWriter::set(LogWriter::LOG_ADMIN, '[' . $this->_lid . '] ' . $sql, ADMIN_LOG_DELAY);
            // 配列に変換
            $records = $result->fetchAll(PDO::FETCH_ASSOC);
            $AdminUsers = [];
            foreach ($records as $user) {
                $AdminUsers[] = new AdminUserBean($user);
            }
            return $AdminUsers;
        } catch (PDOException $e) {
            $this->message = $e->getMessage();
        }
        return false;
    }

    /**
     * ID重複チェック
     *
     * @param  string $hashedId
     * @return boolean 重複していない場合に trueを返し、重複した場合は falseを返す
     */
    public function checkDuplicate(string $hashedId) : bool
    {
        if (empty($this->db) && !$this->openStrage()) {
            return false;
        }
        // SQL実行
        try {
            $pdoStatement = $this->db->prepare(self::DB_SELECT);
            LogWriter::set(LogWriter::LOG_ADMIN, '[' . $this->_lid . '] ' . self::DB_SELECT, ADMIN_LOG_DELAY);
            if ($pdoStatement) {
                if ($pdoStatement->execute(['Id' => $hashedId])) {
                    if ($pdoStatement->columnCount() > 0) {
                        return true;
                    }
                } else {
                    $this->message = 'Can not execute sql statement !!';
                    LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
                    return false;
                }
            } else {
                LogWriter::set(LogWriter::LOG_ADMIN, 'Can not get PDOStatement.', ADMIN_LOG_DELAY);
                return false;
            }
        } catch (PDOException $e) {
            $this->message = $e->getMessage();
            return false;
        }
        return false;
    }

    /**
     * 指定したユーザ情報を取得する
     *
     * @param  string $hashedId ハッシュ化済みユーザID
     * @return AdminUserBean|boolean 情報取得に成功したら管理者情報を取得する、失敗した場合はfalseを返す
     */
    public function getUserInfo(string $hashedId)
    {
        if (empty($this->db) && !$this->openStrage()) {
            return false;
        }
        $pdoStatement = $this->db->prepare(self::DB_SELECT);
        LogWriter::set(LogWriter::LOG_ADMIN, '[' . $this->_lid . '] ' . self::DB_SELECT, ADMIN_LOG_DELAY);
        $pdoStatement->execute(['Id' => $hashedId]);
        LogWriter::set(LogWriter::LOG_ADMIN, "Id => $hashedId", ADMIN_LOG_DELAY);
        if ($pdoStatement->columnCount() > 0) {
            $record = $pdoStatement->fetch(PDO::FETCH_ASSOC);
            return new AdminUserBean($record);
        }
        return false; 
    }

    /**
     * 管理者情報を登録する
     *
     * @param  AdminUserBean $adminInfo 管理者情報
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function regist(AdminUserBean $adminInfo) : bool
    {
        if (empty($this->db) && !$this->openStrage()) {
            return false;
        }
        // 登録データ生成
        $userInfoData = $adminInfo->toArrayWithSerializedProfile();
        // データ挿入
        try {
            $this->db->beginTransaction();
            $pdoStatement = $this->db->prepare(self::DB_INSERT);
            LogWriter::set(LogWriter::LOG_ADMIN, '[' . $this->_lid . '] ' . self::DB_INSERT, ADMIN_LOG_DELAY);
            if ($pdoStatement) {
                if ($pdoStatement->execute($userInfoData)) {
                    LogWriter::set(LogWriter::LOG_ADMIN, var_export($userInfoData, true), ADMIN_LOG_DELAY);
                } else {
                    $this->db->rollBack();
                    $this->message = 'Record Insert Error !';
                    LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
                    return false;
                }
            } else {
                $this->db->rollBack();
                $this->message = 'Can not get PDOStatement.';
                LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
            }
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->message = $e->getMessage();
            LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
            return false;
        }
        return true;
    }

    /**
     * 管理者情報を更新する
     *
     * @param  AdminUserBean $adminInfo 管理者情報
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function update(AdminUserBean $adminInfo) : bool
    {
        if (empty($this->db) && !$this->openStrage()) {
            return false;
        }
        try {
            $this->db->beginTransaction();
            $pdoStatement = $this->db->prepare(self::DB_UPDATE);
            LogWriter::set(LogWriter::LOG_ADMIN, '[' . $this->_lid . '] ' . self::DB_UPDATE, ADMIN_LOG_DELAY);
            if ($pdoStatement) {
                $data = $adminInfo->toArrayWithSerializedProfile();
                if ($pdoStatement->execute($data)) {
                    LogWriter::set(LogWriter::LOG_ADMIN, var_export($data, true), ADMIN_LOG_DELAY);
                } else {
                    $this->db->rollBack();
                    $this->message = 'Record Update Error !';
                    LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
                    return false;
                }
            } else {
                $this->db->rollBack();
                $this->message = 'Can not get PDOStatement.';
                LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
            }
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->message = $e->getMessage();
            LogWriter::set(LogWriter::LOG_ADMIN, $this->message, ADMIN_LOG_DELAY);
            return false;
        }
        return true;
    }
}