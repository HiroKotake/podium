<?php
/**
 * DBException.php
 * 
 * @category  Exception
 * @package   Dabase
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\exception;

/**
 * DBException
 * 
 * @category Exception
 * @package  Dabase
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
 class DBException extends \Exception
 {
     /**
      * コンストラクタ
      *
      * @param string $message　エラーメッセージ
      * @param integer $code エラーコード
      * @param Throwable|null $previous
      */
    function __construct(string $message, $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
 }