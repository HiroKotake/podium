<?php
/**
 * LogWriter.php
 * 
 * @category  Support 
 * @package   Log
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\supports;

use \Exception;
use system\settings\FuseBox;

/**
 * LogWriter Class
 * ログを書き込むためのクラス
 * 
 * @category Support 
 * @package  Log
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class LogWriter
{
    /**
     * SQLログプリフィックス
     * SQLログファイルへ付与するシステム提供の接頭子
     */
    const LOG_SQL = 'sql';          // SQLログ 
    /**
     * アクセスログプリフィックス
     * アクセスログファイルへ付与するシステム提供の接頭子
     */
    const LOG_ACCESS = 'access';    // アクセスログ
    /**
     * 管理者ログ
     * 管理者ログファイルへ付与するシステム提供の接頭子
     */
    const LOG_ADMIN = 'admin';

    /**
     * ログバッファ
     *
     * @var array
     */
    protected static array $logBuffer = [];
    /**
     * ファイルポインタリソース
     *
     * @var array
     */
    protected static array $hFile = [];
    /**
     * エラーメッセージ
     *
     * @var string
     */
    protected static string $errorMessage = '';
    /**
     * ログフォーマット
     *
     * @var string
     */
    protected static string $logFormat = '[!DATE !TIME] (!IPADDR) !MESSAGE';

    /**
     * ログファイル名を作成する
     * ログファイル名は指定した"logPrefix_YYYYmmdd.log"となる。
     *
     * @param string $logPrefix ログファイルのファイル名につける接頭子
     * @return string ログファイル名
     */
    public static function getLogFileName(string $logPrefix) : string
    {
        return LOG_DIR . $logPrefix . '_' . date('Ymd') . '.log';
    }

    /**
     * ログのフォーマットを設定する
     *
     * @param string $logFormat
     * @return void
     */
    public static function logFormat(string $logFormat = null)
    {
        $logFormat ?? '[!DATE !TIME] (!IPADDR) ';
        self::$logFormat = $logFormat;
    }

    /**
     * ログをフォーマットに従い成形する
     *
     * @param string $log
     * @return string
     */
    public static function logFormater(string $log) : string
    {
         // [2022-05-10 00:00:05](192.168.120.1) sql statement
        // -> [!DATE !TIME](!IPADDR) sql statement 
        $date = date('Y-m-d');
        $time = date('H:m:s');
        $ipaddr = FuseBox::get(FuseBox::SERVER, 'REMOTE_ADDR');
        $user = '';
        $formated = str_replace(
            ['!DATE', '!TIME', '!IPADDR', '!USER', '!MESSAGE'],
            [$date, $time, $ipaddr, $user, $log],
            self::$logFormat
        );
        return $formated;
    }

    /**
     * 指定したログファイルをオープンする
     *
     * @param string $logPrefix ログファイルのファイル名につける接頭子
     * @return boolean
     */
    public static function open(string $logPrefix) : bool
    {
        if (!in_array($logPrefix, self::$hFile)) {
            self::$hFile[$logPrefix] = null;
            self::$logBuffer[$logPrefix] = [];
        }
        $logFileName = self::getLogFileName($logPrefix);
        self::$hFile[$logPrefix] = fopen($logFileName, 'a');
        if (!self::$hFile[$logPrefix]) {
            unset(self::$hFile[$logPrefix]);
            unset(self::$logBuffer[$logPrefix]);
            return false;
        }
        return true;
    }

    /**
     * ログを書き込む
     *
     * @param string $logPrefix ログファイルのファイル名につける接頭子
     * @param string $log ログ文字列
     * @param boolean $delay 遅延フラグ：　 （省略時:true）
     *                       遅延指定すると即時に書き込みは行われず、flush(),close()のどちらかの関数の実行まで
     *                       ログファイルへの書き込みは実施されない。
     *                       即時にログファイルに反映さえたい場合は、falseを指定すること。
     * @return boolean 正常に設定された場合はtrueを、失敗した場合はfalseを返す
     */
    public static function set(string $logPrefix, string $log, bool $delay = true) : bool
    {
        $formatedLog = self::logFormater($log);
        if ($delay) {
            self::$logBuffer[$logPrefix][] = $formatedLog;
            return true;
        }

        try {
            $result = fwrite(self::$hFile[$logPrefix], $formatedLog. PHP_EOL);
        } catch (Exception $e) {
            self::$errorMessage = $e->getMessage();
            return false;
        }
        return $result;
    }

    /**
     * ログをファイルへ反映させる
     * ファイルが開かれていない場合、もしくはログが書き込まれていない場合には何もしない
     *
     * @param string $logPrefix ログファイルのファイル名につける接頭子
     * @return boolean 正常に成功した場合はtrueを、失敗した場合はfalseを返す
     * 　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　falseが返された場合はgetErrorMessage()で内容を取得するべきである。
     *                 返され値が空であれば、ログファイルかログが空の場合である。
     */
    public static function flush(string $logPrefix) : bool
    {
        if (array_key_exists($logPrefix, self::$hFile) && !empty(self::$logBuffer[$logPrefix])) {
            try {
                foreach (self::$logBuffer[$logPrefix] as $line) {
                    fwrite(self::$hFile[$logPrefix], $line . PHP_EOL);
                }
            } catch (Exception $e) {
                self::$errorMessage = $e->getMessage();
                return false;
            }
            self::$logBuffer[$logPrefix] = [];
            return true;
        }
        return false;
    }

    /**
     * ログファイルを閉じる
     *
     * @param string $logPrefix ログファイルのファイル名につける接頭子
     * @return boolean 正常に閉じた場合はtrueを、失敗した場合はfalseを返す
     * @see LogWriter::flush()
     */
    public static function close(string $logPrefix) : bool
    {
        if (!self::flush($logPrefix) && !empty(self::$errorMessage)) {
            return false;
        }
        $result = fclose(self::$hFile[$logPrefix]);
        unset(self::$hFile[$logPrefix]);
        return $result;
    }

    /**
     * エラーが発生した時の、メッセージを取得する
     *
     * @return string エラーが発生したときのメッセージの文字列。
     */
    public static function getErrorMessage() : string
    {
        return self::$errorMessage;
    }

}