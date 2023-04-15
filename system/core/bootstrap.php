<?php
/**
 * bootstrap.php
 * 
 * @category  Bootstrap
 * @package   Bootstrap
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */

use system\settings\FuseBox;

/* 実行時間計測(開始) */
$startTime = hrtime(true);

/**
 * システム定数定義読み込み　
 */
require_once __DIR__ . "/../../config/consts.php";

/**
 * システム制御関連ファイル読み込み
 */
require_once ROOT_PATH . "/config/controlParams.php";

/**
 * システム変数読み込み
 */
require_once ROOT_PATH . "/config" . CONFIG_DIR[ENVEROMENT] . "/caches.php";

/**
 * システム初期設定
 */
require_once __DIR__ . "/../../config/bootstrap.php";
date_default_timezone_set(TIMEZONE);

/**
 * オートロード設定
 */
if ($bootConfigure[COMPOSER_USE]) {
    // オートローダーとしてComposerを使用する
    require_once ROOT_PATH . "vendor/autoload.php";
} else {
    // オートローダーとしてPodiumWF独自のものを使用する
    require_once __DIR__ . "/AutoLoader.php";
    $autoLoader = new $bootConfigure[SYSTEM_BASE_LIBS]['AutoLoder']();
    $autoLoader->autoLoad();
}

/**
 * 外部ライブラリ読み込み
 */
foreach ($bootConfigure[SYSTEM_EXTRA_LIBS] as $libFile) {
    require_once ROOT_PATH . $libFile;
}

/**
 * リクエスト返答処理実施
 */
$ignition = new system\core\Ignition();
$ignition->play();

/* 実行時間計測(終了) */
if (EXECTIME_CHECK && FuseBox::checkFinalConfirm()) {
    $endTime = hrtime(true);    // 処理終了
    echo '<hr />';
    echo '実行時間計測:<br />';
    echo 'Start Time : ' . $startTime . '<br />';
    echo 'End Time : ' . $endTime . '<br />';
    $execTime = $endTime - $startTime;
    echo 'ExecTime : ' . $execTime . ' ナノ秒<br />';
    echo 'ExecTime : ' . bcdiv($execTime, '1000000', 3) . ' ミリ秒<br />';
    echo 'ExecTime : ' . bcdiv($execTime, '1000000000', 5) . ' 秒<br />';
    echo 'Memory Use : ' . memory_get_usage() / 1024 . ' KBytes<br />';
    echo 'Memory Peak Use : ' . memory_get_peak_usage() / 1024 . ' KBytes<br />';
    echo '<br /><hr />';
}
