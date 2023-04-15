<?php
/**
 * MsgValidate.php
 * 画面に表示するメッセージを格納する
 * @langage Japan
 */

use phpDocumentor\Reflection\DocBlock\Tags\Since;
use system\supports\Validator;
use system\supports\SimpleValidation;

$errMsgs = [
    'basic' => [
        Validator::ERROR_KEY_PREFIX . Validator::IS_NUMERIC_ONLY => '半角数字以外の文字が使用されています。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_FLOAT => '小数ではありません。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_ALPHAT_ONLY => '半角英字以外の文字が使用されています。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_ALNUM_ONLY=> '半角英数時以外の文字が使用されています。',
        Validator::ERROR_KEY_PREFIX . Validator::AS_NAME => '半角英数以外の文字が名前に使用されています。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_MAIL => 'Eメールではありません。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_URL => 'URLではありません。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_DATE => '指定された日付のフォーマットではありません。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_TIME => '指定された時間のフォーマットではありません。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_DATETIME => '指定された日時のフォーマットではありません。',
        Validator::ERROR_KEY_PREFIX . Validator::SAME_STR => '異なります。',
        Validator::ERROR_KEY_PREFIX . Validator::IS_CLASS_NAME => 'クラス名として使用できない文字があります。',
    ],
    'extra' => [
        Validator::ERROR_KEY_PREFIX . SimpleValidation::MAX_FAIL => '最大文字数を超えています。',
        Validator::ERROR_KEY_PREFIX . SimpleValidation::MIN_FAIL => '最小文字数を満たしていません。',
        Validator::ERROR_KEY_PREFIX . SimpleValidation::NUM_MAX_FAIL => '整数部の最大文字数を超えています。',
        Validator::ERROR_KEY_PREFIX . SimpleValidation::NUM_MIN_FAIL => '整数部の最小文字数を満たしていません。',
        Validator::ERROR_KEY_PREFIX . SimpleValidation::DEC_MAX_FAIL => '小数部の最大文字数を超えています。',
        Validator::ERROR_KEY_PREFIX . SimpleValidation::DEC_MIN_FAIL => '小数部の最小文字数を満たしていません。',
        SimpleValidation::TIME_HOUR_12_RESULT => '時間の入力が12時間系ではありません。',
        SimpleValidation::TIME_HOUR_24_RESULT => '時間の入力が24時間系ではありません。',
        SimpleValidation::TIME_MINUTE_RESULT => '分の入力に誤りがあります。',
        SimpleValidation::TIME_SECOND_RESULT => '秒の入力に誤りがあります。',
        SimpleValidation::DATE_YEAR_2Y_ERSULT => '年の入力が2桁ではありません。',
        SimpleValidation::DATE_YEAR_4Y_ERSULT => '年の入力が4桁ではありません。',
        SimpleValidation::DATE_MONTH_RESULT => '月の入力が間違っています。',
        SimpleValidation::DATE_DAY_RESULT => '日の入力が間違っています。',
    ]
];