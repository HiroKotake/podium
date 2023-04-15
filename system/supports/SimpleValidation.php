<?php
/**
 * SimpleValidation.php
 * 
 * @category  Support 
 * @package   Validate
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\supports;

/**
 * SimpleValidation Class
 * 文字列がルールに基づいて設定されているかを確認するクラス
 * 
 * @category Support 
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class SimpleValidation
{
    // 数字のみ
    const PREG_NUMERIC_ONLY = '/^[0-9]+$/';
    const PREG_FLOAT = '/^([0-9]*)\.([0-9]+)$/';
    // 半角文字のみ
    const PREG_ALPHAT_ONLY = '/^[a-zA-Z]+$/';
    const PREG_ALNUM_ONLY = '/^[a-zA-Z0-9]+$/';
    const PREG_CLASS_NAME = '/^[a-zA-Z0-9_-]+$/';
    const PREG_NAME = '/^([a-zA-Z0-9]+[\s]?)+([a-zA-Z0-9]+)$/';
    // email
    const PREG_MAIL = '/^[0-9a-zA-Z\-_\.]+@([0-9a-zA-Z\-_\.]+\.)+[0-9a-zA-Z]+$/u';
    // URL
    const PREG_URL = '/^(https|http)?:?(\/\/)?([a-zA-Z0-9\-_\.])+([a-zA-Z]+)$/u';
    // 年月日
    const DATE_SMALL_Y = '([0-9]{2})';
    const DATE_LARGE_Y = '([0-9]{4})';
    const DATE_SMALL_M = '([0|1]?[0-9])';
    const DATE_LARGE_M = '([0|1][0-9])';
    const DATE_SMALL_D = '([0|1|2|3]?[0-9])';
    const DATE_LARGE_D = '([0|1|2|3][0-9])';
    // 時分秒
    const TIME_SMALL_H = '([0|1]?[0-9])';
    const TIME_LARGE_H = '([0|1][0-9])';
    const TIME_SMALL_T = '([0|1|2]?[0-9])';
    const TIME_LARGE_T = '([0|1|2][0-9])';
    const TIME_SMALL_I = '([0-9]?[0-9])';
    const TIME_LARGE_I = '([0-9][0-9])';
    const TIME_SMALL_S = '([0-9]?[0-9])';
    const TIME_LARGE_S = '([0-9][0-9])';
    // 文字列検証結果
    const PREG_MATCH_OK = true;
    const PREG_MATCH_NG = false;
    // 文字列長検証結果
    const LENGTH_OK    = 0;
    const MAX_FAIL     = 1;
    const MIN_FAIL     = 10;
    const DEX_NUM = 100;
    const NUM_MAX_FAIL = 100;
    const NUM_MIN_FAIL = 1000;
    const DEX_DEC = 10000;
    const DEC_MAX_FAIL = 10000;
    const DEC_MIN_FAIL = 100000;
    // 年月日検証結果
    const DATE_YEAR_2Y_ERSULT = 'Year2y';
    const DATE_YEAR_4Y_ERSULT = 'Year4y';
    const DATE_MONTH_RESULT = 'Month';
    const DATE_DAY_RESULT = 'Day';
    // 時分秒検証結果
    const TIME_HOUR_12_RESULT = 'Hour12';
    const TIME_HOUR_24_RESULT = 'Hour24';
    const TIME_MINUTE_RESULT = 'Minute';
    const TIME_SECOND_RESULT = 'Second';

    protected int $lengthResult = self::LENGTH_OK;
    protected bool $pregResult = self::PREG_MATCH_OK;
    protected array $timeResult = [];

    /**
     * 検証結果をリセットする
     *
     * @return void
     */
    protected function reset()
    {
        $this->lengthResult = self::LENGTH_OK;
        $this->pregResult = self::PREG_MATCH_OK;
    }

    /**
     * 文字列長の検証結果詳細を取得する
     *
     * @return string 
     */
    public function getLengthResult() : string 
    {
        return str_pad((string)$this->lengthResult, 6, "0", STR_PAD_LEFT);
    }

    /**
     * 文字列の検証結果を取得する
     *
     * @return boolean 検証結果がOKならばtrueを、NGならばfalseを返す
     */
    public function getPregMatchResult() : bool
    {
        return $this->pregResult;
    }

    // 文字数　(maxium/mininum)系
    /**
     * 最大・最小文字列指定
     *
     * @param  string $str チェックする文字列
     * @param  integer $maximum 最大文字数 (０を指定した場合は最大文字制限はしない)
     * @param  integer $minimum 最小文字数 (０を指定した場合は最小文字制限はしない)
     * @param  integer $offset (内部用) 文字列長の検証結果は数値で保存されるので、そのオブセット値
     * @return boolean
     */
    public function strLength(string $str, int $maximum = 0, int $minimum = 0, $offset = 1) : bool
    {
        // 文字列長の指定無
        if ($maximum == 0 && $minimum == 0) {
            return true;
        }
        $strlen = mb_strlen($str);
        $result = true;
        if ($maximum != 0) {
            if ($strlen > $maximum) {
                $this->lengthResult += self::MAX_FAIL * $offset;
                $result = false;
            }
        }
        if ($minimum != 0) {
            if ($strlen < $minimum) {
                $this->lengthResult += self::MIN_FAIL * $offset;
                $result = false;
            }
        }
        return $result;
    }

    // 数値系
    /**
     * 指定した文字列は数字のみで構成されているか判定
     *
     * @param  string $number チェックする文字列
     * @param  integer $maximum 最大文字数 (０を指定した場合は最大文字制限はしない)
     * @param  integer $minimum 最小文字数 (０を指定した場合は最小文字制限はしない)
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isNumeric(string $number, int $maximum = 0, int $minimum = 0) : bool
    {
        $result = self::PREG_MATCH_OK;
        // 文字列チェック
        $result = $result & preg_match(self::PREG_NUMERIC_ONLY, $number);
        // 文字列長チェック
        if ($maximum != 0 || $minimum != 0) {
            $result = $result & $this->strLength($number, $maximum, $minimum);
        }
        $this->pregResult = $result;
        return $result;
    }

    /**
     * 指定した文字列は数字のみで構成された小数か判定
     *
     * @param  string $float チェックする文字列
     * @param  integer $maximum 最大文字数 (０を指定した場合は最大文字制限はしない。小数点も文字数にカウントされる)
     * @param  integer $minimum 最小文字数 (０を指定した場合は最小文字制限はしない。小数点も文字数にカウントされる)
     * @param  integer $intMax 整数部の最大文字数 (０を指定した場合は最大文字制限はしない)
     * @param  integer $intMin 整数部の最小文字数 (０を指定した場合は最小文字制限はしない)
     * @param  integer $decMax 小数部の最大文字数 (０を指定した場合は最大文字制限はしない)
     * @param  integer $decMin 小数部の最小文字数 (０を指定した場合は最小文字制限はしない)
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isFloat(
        string $float,
        int $maximum = 0,
        int $minimum = 0,
        int $intMax = 0, 
        int $intMin = 0, 
        int $decMax = 0, 
        int $decMin = 0
    ) : bool {
        $result = self::PREG_MATCH_OK;
        // 文字列チェック
        $parts = [];
        $result = $result & preg_match(self::PREG_FLOAT, $float, $parts);
        // 文字列長チェック
        if ($maximum != 0 || $minimum != 0) {
            $result = $result & $this->strLength($float, $maximum, $minimum);
        }
        // 整数部チェック
        if (($intMax != 0 || $intMin != 0) && mb_strlen($parts[1]) > 0) {
            $result = $result & $this->strLength((string) $parts[1], $intMax, $intMin, self::DEX_NUM);
        }
        // 小数部チェック
        if (($decMax != 0 || $decMin != 0) && mb_strlen($parts[2]) > 0) {
            $result = $result & $this->strLength((string) $parts[2], $decMax, $decMin, self::DEX_DEC);
        }
        $this->pregResult = $result;
        return $result;
    }

    // 文字列系
    /**
     * 文字列がアルファベットのみで構成されているか。
     * また、指定した文字数にしたがっているかを判定
     *
     * @param  string $float チェックする文字列
     * @param  integer $maximum 最大文字数 (０を指定した場合は最大文字制限はしない)
     * @param  integer $minimum 最小文字数 (０を指定した場合は最小文字制限はしない)
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isAlphabetOnly(string $str, int $maximum = 0, int $minimum = 0) : bool
    {
        $result = self::PREG_MATCH_OK;
        // 文字列チェック
        $result = $result & preg_match(self::PREG_ALPHAT_ONLY, $str);
        // 文字列長チェック
        if ($maximum != 0 || $minimum != 0) {
            $result = $result & $this->strLength($str, $maximum, $minimum);
        }
        $this->pregResult = $result;
        return $result;
    }

    /**
     * 文字列がアルファベットと数字のみで構成されているか。
     * また、指定した文字数にしたがっているかを判定
     *
     * @param  string $str チェックする文字列
     * @param  integer $maximum 最大文字数 (０を指定した場合は最大文字制限はしない)
     * @param  integer $minimum 最小文字数 (０を指定した場合は最小文字制限はしない)
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isAlphabetAndNumeric(string $str, int $maximum = 0, int $minimum = 0) : bool
    {
        $result = self::PREG_MATCH_OK;
        // 文字列チェック
        $result = $result & preg_match(self::PREG_ALNUM_ONLY, $str);
        // 文字列長チェック
        if ($maximum != 0 || $minimum != 0) {
            $result = $result & $this->strLength($str, $maximum, $minimum);
        }
        $this->pregResult = $result;
        return $result;
    }

    /**
     * 文字列がクラス名で使用できる文字で構成されているか。
     * また、指定した文字数にしたがっているかを判定
     *
     * @param  string $str チェックする文字列
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isClassName(string $str) : bool
    {
        $result = self::PREG_MATCH_OK;
        // 文字列チェック
        $result = $result & preg_match(self::PREG_CLASS_NAME, $str);
        $this->pregResult = $result;
        return $result;
    }

    /**
     * 名前に相当する文字列か確認
     *
     * @param  string $name チェックする文字列
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function asName(string $name) : bool
    {
        if (preg_match(self::PREG_NAME, $name)) {
            $this->pregResult = self::PREG_MATCH_OK;
            return true;
        }
        $this->pregResult = self::PREG_MATCH_NG;
        return false;
    }

    /**
     * eメールに相当する文字列か確認
     *
     * @param string $mail チェックする文字列
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isMail(string $mail) : bool
    {
        if (preg_match(self::PREG_MAIL, $mail)) {
            $this->pregResult = self::PREG_MATCH_OK;
            return true;
        }
        $this->pregResult = self::PREG_MATCH_NG;
        return false;
    }

    /**
     * URLに相当する文字列か確認
     *
     * @param string $url チェックする文字列
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isUrl(string $url) : bool
    {
        if (preg_match(self::PREG_URL, $url)) {
            $this->pregResult = self::PREG_MATCH_OK;
            return true;
        }
        $this->pregResult = self::PREG_MATCH_NG;
        return false;
    }

    /**
     *
     */
    /**
     * 年月日時分秒の各部をチェックし、ルールに適合するか確認する
     *
     * @param  string $time 対象の文字列
     * @param  string $format 年月日、時間に関するフォーマットの指定
     * @param  array $search 検索するパターン
     * @param  array $replace パターンに合致した文字の置換先
     * @param  integer $max 最大値 （省略時 0）
     * @return integer ルールに適合した場合は 1を、ルールに適合しても最大値を超える値の場合は -1を、適合しない場合は0を返す
     */
    protected function checkDateTimeParts(string $time, string $format, array $search, array $replace, int $max = 0) : int
    {
        $result = 1;
        foreach ($search as $key => $value) {
            $check = strstr($format, $value);
            if (!empty($check)) {
                $parts = [];
                $pattern = str_replace($value, $replace[$key], $format);
                $pattern = str_replace('/', '\/', $pattern);
                $pattern = preg_replace('/%[Y|y|M|m|D|d|H|h|T|t|I|i|S|s]/', '[0-9]+', $pattern);
                $preg = '/^' . $pattern . '$/';
                $result = preg_match($preg, $time, $parts);
                if ($result == 1) {
                    if ($max != 0 && !empty($parts[1]) && $parts[1] > $max) {
                        $result = -1;
                    }
                } else {
                    $result = 0;
                }
                break;
            }
        }
        return $result;
    }

    /**
     * 日時に相当する文字列か確認
     * 
     * @param  string $date YYYY/MM/DD MM:II:SSを想定した文字列
     * @param  string $format 時間に関するフォーマットの指定
     *                        %y: 年を下２桁で指定
     *                        %Y: 年を4桁で指定 
     *                        %m: 月 1から１２を想定
     *                        %M: 月 01から１２を想定
     *                        %d: 日 1から３１を想定
     *                        %D: 日 01から３１を想定
     *                        %h: １２時間系 h時
     *                        %H: １２時間系 hh時
     *                        %t: 24時間系 t時
     *                        %T: 24時間系 tt時
     *                        %i: m分
     *                        %I: mm分
     *                        %s: s秒
     *                        %S: ss秒
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isDateTime(string $date, string $format = '%Y/%M/%D %H:%I:%S') : bool
    {
        $result = true;
        $result = $result & $this->isDate($date, $format);
        $result = $result & $this->isTime($date, $format);
        return $result;
    }
    /**
     * 年月日に相当する文字列か確認
     *
     * @param  string $date YYYY/MM/DDを想定した文字列
     * @param  string $format 時間に関するフォーマットの指定
     *                        %y: 年を下２桁で指定
     *                        %Y: 年を4桁で指定 
     *                        %m: 月 1から１２を想定
     *                        %M: 月 01から１２を想定
     *                        %d: 日 1から３１を想定
     *                        %D: 日 01から３１を想定
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isDate(string $date, string $format = '%Y/%M/%D') : bool
    {
        // 初期値
        $result = true;
        // 年(2桁)
        $checkResult = $this->checkDateTimeParts($date, $format, ['%y'], [self::DATE_SMALL_Y]);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::DATE_YEAR_2Y_ERSULT] = false;
            }
        }
        // 年(4桁)
        $checkResult = $this->checkDateTimeParts($date, $format, ['%Y'], [self::DATE_LARGE_Y]);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::DATE_YEAR_4Y_ERSULT] = false;
            }
        }
        // 月
        $checkResult = $this->checkDateTimeParts($date, $format, ['%m', '%M'], [self::DATE_SMALL_M, self::DATE_LARGE_M], 12);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::DATE_MONTH_RESULT] = false;
            }
        }
        //　日
        $checkResult = $this->checkDateTimeParts($date, $format, ['%d', '%D'], [self::DATE_SMALL_D, self::DATE_LARGE_D], 31);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::DATE_DAY_RESULT] = false;
            }
        }
        $this->pregResult = $result;
        return $result;
    }

    /**
     * 時間に相当する文字列か確認
     *
     * @param  string $time hh:mm:ssを想定した文字列
     * @param  string $format 時間に関するフォーマットの指定
     *                        %h: １２時間系 h時
     *                        %H: １２時間系 hh時
     *                        %t: 24時間系 t時
     *                        %T: 24時間系 tt時
     *                        %i: m分
     *                        %I: mm分
     *                        %s: s秒
     *                        %S: ss秒
     * @return boolean ルールに適合する場合は trueを返し、しない場合は falseを返す
     */
    public function isTime(string $time, string $format = '%T:%M:%S') : bool
    {
        // 初期値
        $result = true;
        $this->pregResult = self::PREG_MATCH_OK;
        // Check Type H
        $checkResult = $this->checkDateTimeParts($time, $format, ['%h', '%H'], [self::TIME_SMALL_H, self::TIME_LARGE_H], 12);
        if ($checkResult < 1) {
            $result = false;
            if ($checkResult == -1) {
                $this->timeResult[self::TIME_HOUR_12_RESULT] = false;
            }
        }
        // Check Type T
        $checkResult = $this->checkDateTimeParts($time, $format, ['%t', '%T'], [self::TIME_SMALL_T, self::TIME_LARGE_T], 24);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::TIME_HOUR_24_RESULT] = false;
            }
        }
        // Check Type M
        $checkResult = $this->checkDateTimeParts($time, $format, ['%i', '%I'], [self::TIME_SMALL_I, self::TIME_LARGE_I], 59);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::TIME_MINUTE_RESULT] = false;
            }
        }
        // Check Type S 
        $checkResult = $this->checkDateTimeParts($time, $format, ['%s', '%S'], [self::TIME_SMALL_S, self::TIME_LARGE_S], 59);
        if ($checkResult < 1) {
            $result = $result & false;
            if ($checkResult == -1) {
                $this->timeResult[self::TIME_SECOND_RESULT] = false;
            }
        }
        $this->pregResult = $result;
        return $result;
    }

    /**
     * 日時に関する検証の結果でNGの詳細を取得する
     *
     * @return array 検証結果がOKの場合は空の配列を返す。検証結果がNGならば、NGの対象をキー名とする配列を返す。
     *               キー名と結果
     *                  self::DATE_YEAR_2Y_ERSULT falseが設定されている場合、年(2桁)の指定が間違っている
     *                  self::DATE_YEAR_4Y_ERSULT falseが設定されている場合、年（４桁）の指定が間違っている
     *                  self::DATE_MONTH_RESULT falseが設定されている場合、月の指定が間違っている
     *                  self::DATE_DAY_RESULT falseが設定されている場合、日の指定が間違っている
     *                  self::TIME_HOUR_12_RESULT falseが設定されている場合、12時を超えて時が設定されている
     *                  self::TIME_HOUR_24_RESULT falseが設定されている場合、24時を超えて時が設定されている
     *                  self::TIME_MINUTE_RESULT falseが設定されている場合、分の指定が間違っている
     *                  self::TIME_SECOND_RESULT falseが設定されている場合、秒の指定が間違っている
     */
    public function getDateFailedResult() : array
    {
        return $this->timeResult;
    }

    /**
     * 文字列を比較する
     *
     * @param  string $base 比較元文字列
     * @param  string $string 比較先文字列
     * @return boolean 同じ文字列であればtrueを、異なるのであればfalseを返す
     */
    public function compare(string $base, string $comp) : bool
    {
        return (strcmp($base, $comp) == 0);
    }

    /**
     * 結果をリセットする
     *
     * @return void
     */
    public function resetAll()
    {
        $this->lengthResult = self::LENGTH_OK;
        $this->pregResult = self::PREG_MATCH_OK;
        $this->timeResult = [];
    }
}