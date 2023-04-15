<?php
/**
 * Validator.php
 */
namespace system\supports;

use system\supports\SimpleValidation;

class Validator extends SimpleValidation
{
    // 数字のみ
    const IS_NUMERIC_ONLY = 1;
    const IS_FLOAT = 2;
    // 半角文字のみ
    const IS_ALPHAT_ONLY = 3;
    const IS_ALNUM_ONLY = 4;
    const AS_NAME = 5;
    // email
    const IS_MAIL = 6;
    // url
    const IS_URL = 7;
    // Date
    const IS_DATE = 8;
    // Time
    const IS_TIME = 9;
    // DateTime
    const IS_DATETIME = 10;
    // 文字列比較
    const SAME_STR = 11;
    // クラス名
    const IS_CLASS_NAME = 12;
    // オプションのキー名
    /**
     * (オプションのキー名)文字最大長
     */
    const OPT_LENGTH_MAX = 'max';
    /**
     * (オプションのキー名)文字最小長
     */
    const OPT_LENGTH_MIN = 'min';
    /**
     * (オプションのキー名)小数の整数部最大長
     */
    const OPT_LENGTH_NUM_MAX = 'numMax';
    /**
     * (オプションのキー名)小数の整数部最小長
     */
    const OPT_LENGTH_NUM_MIN = 'numMin';
    /**
     * (オプションのキー名)小数の小数部最大長
     */
    const OPT_LENGTH_DEC_MAX = 'DecMax';
    /**
     * (オプションのキー名)小数の小数部最小長
     */
    const OPT_LENGTH_DEC_MIN = 'DecMin';
    /**
     * (オプションのキー名)日時のフォーマット
     */
    const OPT_PATTERN = 'pattern';
    /**
     * エラーメッセージのキー接頭子
     */
    const ERROR_KEY_PREFIX = 'E';

    /**
     * Undocumented variable
     *
     * @var array
     */
    protected array $errMsgLang;
    /**
     * エラーメッセージ
     *
     * @var array
     */
    protected array $errorMessage = [];
    /**
     * 全体の検証結果
     *
     * @var boolean
     */
    protected bool $totalResult = true;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        include_once ROOT_PATH . 'language/' . LANGUAGE . '/MsgValidate.php';
        $this->errMsgLang = $errMsgs;
    }

    /**
     * 文字列の最大・最小の検証結果からエラーメッセージを生成
     *
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @return string エラーフラグ
     */
    protected function checkStrLengthResult(string $title = '') : string 
    {
        $errFlag = $this->getLengthResult();
        $failed = str_pad((string)self::MAX_FAIL, 6, "0", STR_PAD_LEFT);
        $check = $errFlag & $failed;
        if (!empty((int)$check)) {
            $this->errorMessage[] = $title . $this->errMsgLang['extra'][self::ERROR_KEY_PREFIX . self::MAX_FAIL];
        }
        $failed = str_pad((string)self::MIN_FAIL, 6, "0", STR_PAD_LEFT);
        $check = $errFlag & $failed;
        if (!empty((int)$check)) {
            $this->errorMessage[] = $title . $this->errMsgLang['extra'][self::ERROR_KEY_PREFIX . self::MIN_FAIL];
        }
        return $errFlag;
    }

    /**
     * 文字列が半角数字のみか検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。検証対象は以下のconst値をキー名で指定する
     *                       OPT_LENGTH_MAX: 最大文字列長
     *                       OPT_LENGTH_MIN: 最小文字列長
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkNumericOnly(string $subject, string $title = '', array $option = []) : bool
    {
        $result = true;
        if (empty($option)) {
            $result = $this->isNumeric($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_NUMERIC_ONLY];
            }
        } else {
            $max = array_key_exists(self::OPT_LENGTH_MAX, $option) ? $option[self::OPT_LENGTH_MAX] : 0;
            $min = array_key_exists(self::OPT_LENGTH_MIN, $option) ? $option[self::OPT_LENGTH_MIN] : 0;
            $result = $this->isNumeric($subject, $max, $min);
            if (!$result) {
                if (!$this->getPregMatchResult()) {
                    $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_NUMERIC_ONLY];
                }
                $this->checkStrLengthResult($title);
            }
        }
        return $result;
    }

    /**
     * 文字列が小数か検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。検証対象は以下のconst値をキー名で指定する
     *                       OPT_LENGTH_MAX: 最大文字列長
     *                       OPT_LENGTH_MIN: 最小文字列長
     *                       OPT_LENGTH_NUM_MAX: 整数部最大文字列長
     *                       OPT_LENGTH_NUM_MIN: 整数部最小文字列長
     *                       OPT_LENGTH_DEC_MAX: 小数部最大文字列長
     *                       OPT_LENGTH_DEC_MIN: 小数部最小文字列長
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkFloat(string $subject, string $title = '', array $option = []) : bool
    {
        $result = true;
        if (empty($option)) {
            $result = $this->isFloat($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_FLOAT];
            }
        } else {
            $max = array_key_exists(self::OPT_LENGTH_MAX, $option) ? $option[self::OPT_LENGTH_MAX] : 0;
            $min = array_key_exists(self::OPT_LENGTH_MIN, $option) ? $option[self::OPT_LENGTH_MIN] : 0;
            $numMax = array_key_exists(self::OPT_LENGTH_NUM_MAX, $option) ? $option[self::OPT_LENGTH_NUM_MAX] : 0;
            $numMin = array_key_exists(self::OPT_LENGTH_NUM_MIN, $option) ? $option[self::OPT_LENGTH_NUM_MIN] : 0;
            $decMax = array_key_exists(self::OPT_LENGTH_DEC_MAX, $option) ? $option[self::OPT_LENGTH_DEC_MAX] : 0;
            $decMin = array_key_exists(self::OPT_LENGTH_DEC_MIN, $option) ? $option[self::OPT_LENGTH_DEC_MIN] : 0;
            $result = $this->isFloat($subject, $max, $min, $numMax, $numMin, $decMax, $decMin);
            if (!$result) {
                if (!$this->getPregMatchResult()) {
                    $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_FLOAT];
                }
                $errFlag = $this->checkStrLengthResult($title);
                $check = $errFlag & str_pad((string)self::NUM_MAX_FAIL, 6, "0", STR_PAD_LEFT);
                if (!empty((int)$check)) {
                    $this->errorMessage[] = $title . $this->errMsgLang['extra'][self::ERROR_KEY_PREFIX . self::NUM_MAX_FAIL];
                }
                $check = $errFlag & str_pad((string)self::NUM_MIN_FAIL, 6, "0", STR_PAD_LEFT);
                if (!empty((int)$check)) {
                    $this->errorMessage[] = $title . $this->errMsgLang['extra'][self::ERROR_KEY_PREFIX . self::NUM_MIN_FAIL];
                }
                $check = $errFlag & str_pad((string)self::DEC_MAX_FAIL, 6, "0", STR_PAD_LEFT);
                if (!empty((int)$check)) {
                    $this->errorMessage[] = $title . $this->errMsgLang['extra'][self::ERROR_KEY_PREFIX . self::DEC_MAX_FAIL];
                }
                $check = $errFlag & str_pad((string)self::DEC_MIN_FAIL, 6, "0", STR_PAD_LEFT);
                if (!empty((int)$check)) {
                    $this->errorMessage[] = $title . $this->errMsgLang['extra'][self::ERROR_KEY_PREFIX . self::DEC_MIN_FAIL];
                }
            }
        }
        return $result;
    }

    /**
     * 文字列が半角英字のみか検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。検証対象は以下のconst値をキー名で指定する
     *                       OPT_LENGTH_MAX: 最大文字列長
     *                       OPT_LENGTH_MIN: 最小文字列長
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkAlphatOnly(string $subject, string $title = '', array $option = []) : bool
    {
        $result = true;
        if (empty($option)) {
            $result = $this->isAlphabetOnly($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_ALPHAT_ONLY];
            }
        } else {
            $max = array_key_exists(self::OPT_LENGTH_MAX, $option) ? $option[self::OPT_LENGTH_MAX] : 0;
            $min = array_key_exists(self::OPT_LENGTH_MIN, $option) ? $option[self::OPT_LENGTH_MIN] : 0;
            $result = $this->isAlphabetOnly($subject, $max, $min);
            if (!$result) {
                if (!$this->getPregMatchResult()) {
                    $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_ALPHAT_ONLY];
                }
                $this->checkStrLengthResult($title);
            }
        }
        return $result;
    }

    /**
     * 文字列が半角英数字のみか検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。検証対象は以下のconst値をキー名で指定する
     *                       OPT_LENGTH_MAX: 最大文字列長
     *                       OPT_LENGTH_MIN: 最小文字列長
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkAlnumOnly(string $subject, string $title = '', array $option = []) : bool
    {
        if (empty($option)) {
            $result = $this->isAlphabetAndNumeric($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_ALNUM_ONLY];
            }
        } else {
            $max = array_key_exists(self::OPT_LENGTH_MAX, $option) ? $option[self::OPT_LENGTH_MAX] : 0;
            $min = array_key_exists(self::OPT_LENGTH_MIN, $option) ? $option[self::OPT_LENGTH_MIN] : 0;
            $result = $this->isAlphabetAndNumeric($subject, $max, $min);
            if (!$result) {
                if (!$this->getPregMatchResult()) {
                    $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_ALNUM_ONLY];
                }
                $this->checkStrLengthResult($title);
            }
        }
        return $result;
    }

    /**
     * 日時検証のNGの詳細を設定する
     *
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @return void
     */
    protected function checkDateFailedResult(string $title)
    {
        $check = $this->getDateFailedResult();
        if (!empty($check)) {
            foreach ($check as $key => $value) {
                if (!$value) {
                    $this->errorMessage[] = $title . $this->errMsgLang['extra'][$key];
                }
            } 
        }
    }

    /**
     * 文字列が時間を示す半角数字か検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。
     *                       検証対象はconst値OPT_PATTERNをキー名で指定し、時刻のフォーマットを値として設定する
     *                        %h １２時間系 h時
     *                        %H １２時間系 hh時
     *                        %t 24時間系 t時
     *                        %T 24時間系 tt時
     *                        %i m分
     *                        %I mm分
     *                        %s s秒
     *                        %S ss秒
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkTime(string $subject, string $title = '', array $opt) : bool
    {
        $result = $this->isTime($subject, $opt[self::OPT_PATTERN]);
        if (!$result) {
            $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_TIME];
            $this->checkDateFailedResult($title);
        }
        return $result;
    }

    /**
     * 文字列が日付を示す半角数字か検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。
     *                       検証対象はconst値OPT_PATTERNをキー名で指定し、日付のフォーマットを値として設定する
     *                        %y 年を下２桁で指定
     *                        %Y 年を4桁で指定 
     *                        %m 月 1から１２を想定
     *                        %M 月 01から１２を想定
     *                        %d 日 1から３１を想定
     *                        %D 日 01から３１を想定
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkDate(string $subject, string $title = '', array $opt) : bool
    {
        $result = $this->isDate($subject, $opt[self::OPT_PATTERN]);
        if (!$result) {
            $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_DATE];
            $this->checkDateFailedResult($title);
        }
        return $result;
    }

    /**
     * 文字列が日時を示す半角数字か検証し、検証結果がエラーの場合にエラーメッセージを生成
     *
     * @param  string $subject 検証対象の文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。
     *                       検証対象はconst値OPT_PATTERNをキー名で指定し、日時のフォーマットを値として設定する
     *                        %y 年を下２桁で指定
     *                        %Y 年を4桁で指定 
     *                        %m 月 1から１２を想定
     *                        %M 月 01から１２を想定
     *                        %d 日 1から３１を想定
     *                        %D 日 01から３１を想定
     *                        %h １２時間系 h時
     *                        %H １２時間系 hh時
     *                        %t 24時間系 t時
     *                        %T 24時間系 tt時
     *                        %i m分
     *                        %I mm分
     *                        %s s秒
     *                        %S ss秒
     * @return boolean 検証結果が問題ない場合は trueを、問題がある場合には falseを返す
     */
    protected function checkDateTime(string $subject, string $title = '', array $opt) : bool
    {
        $result = $this->isDateTime($subject, $opt[self::OPT_PATTERN]);
        if (!$result) {
            $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_DATETIME];
            $this->checkDateFailedResult($title);
        }
        return $result;
    }

    /**
     * 対象のオブジェクトの検証を実施する
     *
     * @param  string $subject 検証の対象
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @param  integer $type 検証のタイプ
     *                       検証できるタイプの指定は以下のものがあるので、そのconst値にて指定を行う
     *                       IS_NUMERIC_ONLY: 半角数字のみか検証
     *                       IS_FLOAT: 小数か検証
     *                       IS_ALPHAT_ONLY: 半角英字のみか検証
     *                       IS_ALNUM_ONLY: 半角英数字のみか検証
     *                       AS_NAME: 半角スペースを含む英数字のみか検証
     *                       IS_MAIL: Eメールか検証
     *                       IS_URL: URLか検証
     *                       IS_DATE: 日付か検証
     *                      　　IS_TIME: 時刻か検証
     *                      　IS_DATETIME: 日時で検証
     *                       CLASS_NAME: クラス名で使用できる文字列か検証
     * @param  array $option 文字列長の検証を実施する時にオプションを付与する必要がある。検証対象は以下のconst値をキー名で指定する
     *                       OPT_LENGTH_MAX: 最大文字列長
     *                       OPT_LENGTH_MIN: 最小文字列長
     *                       OPT_LENGTH_NUM_MAX: (検証タイプがIS_FLOAT時に使用) 整数部最大文字列長
     *                       OPT_LENGTH_NUM_MIN: (検証タイプがIS_FLOAT時に使用) 整数部最小文字列長
     *                       OPT_LENGTH_DEC_MAX: (検証タイプがIS_FLOAT時に使用) 小数部最大文字列長
     *                       OPT_LENGTH_DEC_MIN: (検証タイプがIS_FLOAT時に使用) 小数部最小文字列長
     *                       OPT_PATTERN: (IS_DATE, IS_TIME, IS_DATETIMEで使用
     *                          %y: 年を下２桁で指定
     *                          %Y: 年を4桁で指定 
     *                          %m: 月 1から１２を想定
     *                          %M: 月 01から１２を想定
     *                          %d: 日 1から３１を想定
     *                          %D: 日 01から３１を想定
     *                          %h: １２時間系 h時
     *                          %H: １２時間系 hh時
     *                          %t: 24時間系 t時
     *                          %T: 24時間系 tt時
     *                          %i: m分
     *                          %I: mm分
     *                          %s: s秒
     *                          %S: ss秒
     *                          例) 2022/01/01 -> %Y/%M/%D
     *                              21:00:00 -> %T:%I:%S
     * @return boolean 検証結果 問題がなければ trueを、問題が発生した場合は falseを返す
     */
    public function setValid(
        string $subject, 
        string $title = '', 
        int $type, 
        array $option = []
    ) : bool {
        $result = true;
        switch($type) {
        case self::IS_NUMERIC_ONLY:
            return $this->checkNumericOnly($subject, $title, $option);
        case self::IS_FLOAT:
            return $this->checkFloat($subject, $title, $option);
        case self::IS_ALPHAT_ONLY:
            return $this->checkAlphatOnly($subject, $title, $option);
        case self::IS_ALNUM_ONLY:
            return $this->checkAlnumOnly($subject, $title, $option);
        case self::AS_NAME:
            $result = $this->asName($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::AS_NAME];
            }
            break;
        case self::IS_MAIL:
            $result = $this->isMail($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_MAIL];
            }
            break;
        case self::IS_URL:
            $result = $this->isUrl($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_URL];
            }
            break;
        case self::IS_TIME:
            $result = $this->checkTime($subject, $title, $option);
            break;
        case self::IS_DATE:
            $result = $this->checkDate($subject, $title, $option);
            break;
        case self::IS_DATETIME:
            $result = $this->checkDateTime($subject, $title, $option);
            break;
        case self::IS_CLASS_NAME:
            $result = $this->isClassName($subject);
            if (!$result) {
                $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::IS_CLASS_NAME];
            }
            break;
        }
        $this->totalResult = $this->totalResult & $result;
        return $result;
    }

    /**
     * 文字列を比較する
     *
     * @param  string $base 比較元文字列
     * @param  string $string 比較先文字列
     * @param  string $title 検証NG時のエラーに付与するタイトル
     * @return boolean 同じ文字列であればtrueを、異なるのであればfalseを返す
     */
    public function setValidCompare(string $base, string $comp, string $title) : bool
    {
        $result = $this->compare($base, $comp);
        if (!$result) {
            $this->errorMessage[] = $title . $this->errMsgLang['basic'][self::ERROR_KEY_PREFIX . self::SAME_STR];
        }
        return $result;
    }

    /**
     * 検証結果を取得する
     *
     * @return boolean 全ての検証が問題なければ trueを、一部での問題がある場合はfalseを返す
     */
    public function getResult() : bool
    {
        return $this->totalResult;
    }

    /**
     * バリデーションの結果を取得する
     *
     * @return string エラー発生時のエラーの原因メッセージを含む配列を返す
     */
    public function getMessage() : array 
    {
        return $this->errorMessage;
    }

    /**
     * 検証結果をリセットする　
     *
     * @return void
     */
    public function reset()
    {
        $this->resetAll();
        $this->totalResult = true;
        $this->errorMessage = [];
    }
}