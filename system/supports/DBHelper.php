<?php
/**
 * DBHelper.php
 * 
 * @category  Support
 * @package   Database
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\supports;

use PDOException;

/**
 * DBHelper class
 * 
 * @category Support
 * @package  Database
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class DBHelper
{

    /**
     * DSNを生成する
     *
     * @return string 対応するデータベースの場合はdnsを返す。対応していない場合はPDOExeptionを発行する
     * @throws PDOException
     */
    public static function makeDsn(array $dbInfo) : string
    {
        $dsn = '';
        switch($dbInfo['type']) {
        case STRAGE_TYPE_MYSQL:
        case 'mysql':
            $dsn = $dbInfo['type'] 
                . ':host=' . $dbInfo['host'] 
                . ';dbname=' . $dbInfo['database']
                . ';port=' . $dbInfo['port']
                . ';charset=' . $dbInfo['charset'];
            break;
        case STRAGE_TYPE_PGSQL:
        case 'pgsql':
            $dsn = $dbInfo['type'] 
                . ':host=' . $dbInfo['host'] 
                . ';dbname=' . $dbInfo['database']
                . ';port=' . $dbInfo['port'];
            break;
        default:
            throw new PDOException('Non Support Database(' . $dbInfo['type'] . ') !!');
        }
        return $dsn;
    }


}