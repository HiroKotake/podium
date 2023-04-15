<?php
/**
 * dbCommon.php
 * 
 * @category  Database
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\database;

use Exception;
use \PDO;
use \PDOException;
use \PDOStatement;
use system\database\DBConnector;
use system\supports\LogWriter;

/**
 * dbCommon
 * データベースへの共通クラス
 * 
 * @category Database
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class dbCommon
{
    const QUERY_TABLE_COLUMS = "SELECT
        ordinal_position AS 'OrdinalPosition',
        column_name AS 'ColumnName', 
        data_type AS 'DataType',
        character_maximum_length AS 'CharacterMaximumLength',
        numeric_precision AS 'NumericPrecision',
        is_nullable AS 'IsNullable', 
        column_default AS 'ColumnDefault',
        column_comment AS 'ColumnComment' 
        FROM information_schema.columns 
        WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :name 
        ORDER BY ORDINAL_POSITION
    ";
    protected string $dbName;
    protected string $message = '';
    protected \PDO $conn;

    /**
     * コンストラクタ
     *
     * @param string $dbName データベース名
     */
    function __construct(string $dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * デストラクタ
     */
    function __destruct()
    {
        $this->close();
    }

    /**
     * データベース接続情報を設定する
     *
     * @param string $dbName データベース名
     * @return void
     */
    public function setDbName(string $dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * データベースへ接続
     *
     * @return boolean 接続に成功した場合はtrueを、失敗した場合はfalseを返す
     */
    public function connect() : bool
    {
        try {
            $this->conn =& DBConnector::getDBConnection($this->dbName);
            return true;
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false;
        } catch (PDOException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * データベースを切断
     *
     * @return void
     */
    public function close()
    {
        $this->message = '';
        unset($this->conn);
    }

    /**
     * 接続済みのPDOインスタンスを返す
     * dbCommonで定義されている関数以外の
     * PDOで定義されている関数を使用したい場合はPDOインスタンスをこの関数経由で
     * 取得して、その取得したインスタンスでPDOの関数を利用するようにしてください。
     *
     * @return \PDO 接続済みであればPDOインスタンスを返し、接続に失敗していればfalseを返す
     */
    public function getConnection() : \PDO
    {
        $this->conn ?? $this->connect();
        return $this->conn;
    }

    /**
     * エラーメッセージを結果を返す
     *
     * @return string エラーメッセージ 
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * SQLクエリーがSELECTか確認
     *
     * @param string $query SQLクエリー
     * @return boolean SELECT文の場合にはtrueを、そうでない場合はfalseを返す
     */
    private function _isSelectQuery(string $query) : bool
    {
        return preg_match('/select[!-~]*/i', $query);
    }

    /**
     * SQLクエリーがINSERTか確認
     *
     * @param string $query SQLクエリー
     * @return boolean SELECT文の場合にはtrueを、そうでない場合はfalseを返す
     */
    private function _isInsertQuery(string $query) : bool
    {
        return preg_match('/insert[!-~]*/i', $query);
    }

    // SQL実行
    /**
     * 指定したSQLを実行
     *
     * @param  string $query
     * @param  boolean $transaction
     * @return boolean 実行に成功した場合はtureを、失敗した場合はfalseを返す
     */
    public function exec(string $query, bool $transaction = true) : bool
    {
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実行
        try {
            // SQL実行
            if ($transaction) {
                $this->conn->beginTransaction();
            }
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
            }
            // SQL実行
            $this->conn->exec($query);
            // インデックスIDを取得
            if ($transaction) {
                $this->conn->commit();
            }
        } catch (\PDOException $e) {
            if ($transaction) {
                $this->conn->rollback();
            }
            $this->message = $e->getMessage(); 
            return false;
        }
        return true;
    }

    /**
     * 指定したSQLを実行
     *
     * @param  string $query
     * @param  boolean $transaction
     * @return PDOStatement/false 実行に成功した場合はPDOStatumentを、失敗した場合はfalseを返す
     */
    public function query(string $query, bool $transaction = true)
    {
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実行
        $result = false;
        try {
            // SQL実行
            if ($transaction) {
                $this->conn->beginTransaction();
            }
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
            }
            // SQL実行
            $result = $this->conn->query($query);
            // インデックスIDを取得
            if ($transaction) {
                $this->conn->commit();
            }
        } catch (\PDOException $e) {
            if ($transaction) {
                $this->conn->rollback();
            }
            $this->message = $e->getMessage(); 
            return false;
        }
        return $result;
    }

    /**
     * 一行レコードを挿入する
     *
     * @param string $query レコード挿入用SQLステートメント
     * @param boolean $transaction トランザクション処理を実施するか 実施:true(デフォルト値), 未実施:false
     * @return void 挿入に成功した場合は対象のレコードID 失敗した場合にはfalseを返す
     */
    public function insert(string $query, bool $transaction = true)
    {
        $lastestId = false;
        // INSERT確認
        if (!$this->_isInsertQuery($query)) {
            $this->message = 'Query is not INSERT STATEMENT !!';
            return $lastestId;
        }
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実施
        try {
            // SQL実行
            if ($transaction) {
                $this->conn->beginTransaction();
            }
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
            }
            // SQL実行
            $num = $this->conn->exec($query);
            // インデックスIDを取得
            // コミットするとlastInsertIdは0リセットされるので、
            // この位置で取得する
            if ($num > 0) {
                $lastestId = $this->conn->lastInsertId(DB_PRIMARY_KEY);
            }
            if ($transaction) {
                $this->conn->commit();
            }
        } catch (\PDOException $e) {
            if ($transaction) {
                $this->conn->rollback();
            }
            $this->message = $e->getMessage(); 
        }
        return $lastestId;
    }

    /**
     * 一括して複数のレコードを挿入する
     *
     * @param string $query レコード挿入用SQLステートメント
     * @param array $data 対応するデータを含む配列
     * @return mix 成功した場合は挿入したレコードのIdを配列に格納したものを返し、失敗した場合はfalseを返す 
     */
    public function prepareInsert(string $query, array $data, $options = [])
    {
        $result = [];
        // INSERT確認
        if (!$this->_isInsertQuery($query)) {
            $this->message = 'Query is not INSERT STATEMENT !!';
            return false;
        }
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実施
        try {
            // SQL実行
            $this->conn->beginTransaction();
            // SQL設定
            $pdoStatment = $this->conn->prepare($query, $options);
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
                foreach ($data as $line) {
                    foreach ($line as $key => $value) {
                        LogWriter::set(LogWriter::LOG_SQL, '[' . $key . '] ' . var_export($value, true), SQL_LOG_DELAY);
                    }
                }
            }
            // SQL実施
            foreach ($data as $value) {
                // SQL実行
                $execResult = $pdoStatment->execute($value);
                // インデックスIDを取得
                if ($execResult) {
                    $result[] = $this->conn->lastInsertId(DB_PRIMARY_KEY);
                } else {
                    // エラー発生時
                    $errInfo = $pdoStatment->errorInfo();
                    $this->message .= $errInfo[2] . ': ';
                }
            }
            $this->conn->commit();
        } catch (\PDOException $e) {
            $this->conn->rollback();
            $this->message = $e->getMessage(); 
            return false;
        }
        return $result;
    }
    /**
     * レコードを検索する
     *
     * @param string $query 検索用SQLステートメント
     * @param boolean $native 戻り値の型指定：　tureを指定するとSQLStatementで結果を返す,
     *                        無指定もしくはfalseの場合はPDO::FETCH_ASSOCに基づいて値を返します。
     * @return void 検索結果 検索結果を返す
     */
    public function search(string $query, bool $native = false)
    {
        // SELECT確認
        if (!$this->_isSelectQuery($query)) {
            $this->message = 'Query is not SELECT STATEMENT !!';
            return false;
        }
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実施
        try {
            // SQL実行
            $result = $this->conn->query($query);
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
            }
            // SQL実行
            if ($native) {
                // 結果をPDOStatementで返す
                return $result;
            }
            // 結果のレコードを配列で返す
            if ($result->columnCount() == 0) {
                // 結果の行数が０の場合は空の配列を返す
                return [];
            }
            // 配列に変換
            $records = $result->fetchAll(PDO::FETCH_ASSOC);
            return $records;
        } catch (\PDOException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }

    /**
     * プリペアードで検索用SQLクエリーを実施する
     * 成功した場合はPDO::FETCH_ASSOCに基づいて値を返します
     *
     * @param string $query 検索用SQLステートメント
     * @param array $data 対応するデータ配列を含む配列
     * @return void 成功した場合はPDO::FETCH_ASSOCに基づいた値、失敗した場合はfalseを返す
     */
    public function prepareSearch(string $query, array $data)
    {
        $result = false;
        // SELECT確認
        if (!$this->_isSelectQuery($query)) {
            $this->message = 'Query is not SELECT STATEMENT !!';
            return $result;
        }
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実施
        $records = [];
        try {
            // SQLクエリー設定
            $pdoStatment = $this->conn->prepare($query);
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
                foreach ($data as $line) {
                    foreach ($line as $key => $value) {
                        LogWriter::set(LogWriter::LOG_SQL, '[' . $key . '] ' . var_export($value, true), SQL_LOG_DELAY);
                    }
                }
            }
            // SQL実施
            foreach ($data as $value) {
                // SQL実行
                if ($pdoStatment->execute($value)) {
                    if ($lines = $pdoStatment->fetchAll(PDO::FETCH_ASSOC)) {
                        $records = array_merge($records, $lines);
                    }
                }
            }
            return empty($records) ? $result : $records;
        } catch (\PDOException $e) {
            $this->message = $e->getMessage(); 
        }
        return $result;
    }

    /**
     * 一行レコードを更新する
     *
     * @param string $query 更新用SQLステートメント
     * @param boolean $transaction トランザクション処理を実施するか 実施:true(デフォルト値), 未実施:false
     * @return boolean 成功時：true , 失敗時:false
     */
    public function update(string $query, bool $transaction = true) : bool
    {
        $result = false;
        // SELECT確認
        if ($this->_isSelectQuery($query)) {
            $this->message = 'Query is SELECT STATEMENT !!';
            return $result;
        }
        // INSERT確認
        if ($this->_isInsertQuery($query)) {
            $this->message = 'Query is INSERT STATEMENT !!';
            return $result;
        }
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実施
        try {
            // SQL実行
            if ($transaction) {
                $this->conn->beginTransaction();
            }
            // SQL実行
            $num = $this->conn->exec($query);
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
            }
            if ($transaction) {
                $this->conn->commit();
            }
            $result = $num > 0 ? true : false;
        } catch (\PDOException $e) {
            if ($transaction) {
                $this->conn->rollback();
            }
            $this->message = $e->getMessage(); 
        }
        return $result;
    }

    /**
     * プリペアードで更新用SQLクエリーを実施する
     * このメソッドを利用した処理は全てトランザクションで処理される
     *
     * @param string $query SQLステートメント
     * @param array $data 対応するデータを含む配列
     * @return boolean
     */
    public function prepareUpdate(string $query, array $data) : bool
    {
        $result = false;
        // SELECT確認
        if ($this->_isSelectQuery($query)) {
            $this->message = 'Query is SELECT STATEMENT !!';
            return $result;
        }
        // INSERT確認
        if ($this->_isInsertQuery($query)) {
            $this->message = 'Query is INSERT STATEMENT !!';
            return $result;
        }
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実施
        try {
            // SQL実行
            $this->conn->beginTransaction();
            // SQLクエリー設定
            $pdoStatment = $this->conn->prepare($query);
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
                foreach ($data as $line) {
                    foreach ($line as $key => $value) {
                        LogWriter::set(LogWriter::LOG_SQL, '[' . $key . '] ' . var_export($value, true), SQL_LOG_DELAY);
                    }
                }
            }
            // SQL実施
            foreach ($data as $value) {
                // SQL実行
                $pdoStatment->execute($value);
            }
            $this->conn->commit();
            $result = true;
        } catch (\PDOException $e) {
            $this->conn->rollback();
            $this->message = $e->getMessage(); 
        }
        return $result;
    }

    /**
     * 指定したテーブルの構成情報を取得する
     *
     * @param  string $tableName テーブル名
     * @return void 成功した場合は以下のデータ配列を含む配列を返す。
     *              [[
     *                  'OrdinalPosition' => <カラムの配置順>,
     *                  'ColumnName' => <カラム名>,
     *                  'DataType' => <データ型>,
     *                  'CharacterMaximumLength' => <文字の最大長>,
     *                  'NumericPrecision' => <数値の最大桁数>,
     *                  'IsNullable' => <nullを格納可能な場合は"Yes"を、出来ない場合は"No">,
     *                  'ColumnDefault' => <カラムのデフォルト値>,
     *                  'ColumnComment' => <カラムのコメント>
     *              ],...]
     *              失敗した場合はfalseを返す。
     */
    public function getShowColums(string $tableName)
    {
        if (DBConnector::getDBType($this->dbName) == 'sqlite') {
            return $this->getShowColumsAtSQLite($tableName);
        }
        $data = [['schema' => $this->dbName, 'name' => $tableName]];
        $result = $this->prepareSearch(self::QUERY_TABLE_COLUMS, $data);
        return $result;
    }

    /**
     * SQLiteで指定したテーブルの構成情報を取得する
     *
     * @param  string $tableName テーブル名
     * @return void 成功した場合は以下のデータ配列を含む配列を返す。
     *              [[
     *                  'OrdinalPosition' => <カラムの配置順>,
     *                  'ColumnName' => <カラム名>,
     *                  'DataType' => <データ型>,
     *                  'CharacterMaximumLength' => <null>,
     *                  'NumericPrecision' => <null>,
     *                  'IsNullable' => <nullを格納可能な場合は"Yes"を、出来ない場合は"No">,
     *                  'ColumnDefault' => <カラムのデフォルト値>,
     *                  'ColumnComment' => <null>
     *              ],...]
     *              失敗した場合はfalseを返す。
     */
    public function getShowColumsAtSQLite(string $tableName)
    {
        // コネクションが貼られているか確認し、貼られていなければ接続
        if (empty($this->conn)) {
            if (!$this->connect()) {
                // 接続失敗
                return false;
            }
        }
        // SQL実行
        $query = "PRAGMA table_info('$tableName')";
        try {
            // SQL実行
            $result = $this->conn->query($query);
            // ログ出力
            if (SQL_LOG_ON) {
                LogWriter::set(LogWriter::LOG_SQL, $query, SQL_LOG_DELAY);
            }
            // 配列に変換
            $records = $result->fetchAll(PDO::FETCH_ASSOC);
            $data = [];
            foreach ($records as $line) {
                $data[] = [
                    'OrdinalPosition' => $line['cid'],
                    'ColumnName' => $line['name'],
                    'DataType' => $line['type'],
                    'CharacterMaximumLength' => null,
                    'NumericPrecision' => null,
                    'IsNullable' => ($line['notnull'] = 1 ? "Yes" : "No"),
                    'ColumnDefault' => $line['dflt_value'],
                    'ColumnComment' => null,
                ];
            }
            return $data;
        } catch (\PDOException $e) {
            $this->message = $e->getMessage();
            return false;
        }
    }
}