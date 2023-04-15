<?php
/**
 * LogicResource.php
 * 
 * @category  Logic 
 * @package   Resource
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\logics;

use pwfadmin\models\ModelResourceStatus;

/**
 * LogicResource Class
 * 
 * @category Logic 
 * @package  Resource
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class LogicResource 
{
    protected string $resourcePath = '';

    /**
     * constructer
     *
     * @param string $path リソースディレクトリのパス
     */
    function __construct(string $path = '')
    {
        $this->resourcePath = $path;
    }

    /**
     * リソースリストを取得する
     *
     * @param  string $ext 検索するファイルの拡張子
     * @param  string $path 検索するリソースファイルの基準ディレクトリ
     * @return array ['Dir' => ディレクトリリスト、 'Files' => ファイルステータスリスト]
     */
    public function getResourceList(string $ext, $path = '') : array
    {
        // ステータスファイル処理
        $targetDir = $this->resourcePath . (empty($path) ? '' : DIRECTORY_SEPARATOR . $path);
        $status = [];
        $mResourceStatus = new ModelResourceStatus((string) $targetDir);
        $mResourceStatus->load();
        $status = $mResourceStatus->getAll();
        // ファイル・ディレクトリ確認
        $hDir = opendir($targetDir);
        $dirs = [];
        if ($hDir) {
            while (($file = readdir($hDir)) !== false) {
                // 
                if ($file == '.' || $file == '..' || $file == ModelResourceStatus::STATUS_FILE) {
                    continue;
                }
                $targetFile = $targetDir . DIRECTORY_SEPARATOR . $file;
                // ディレクトリ
                if (is_dir($targetFile)) {
                    $dirs[] = $file;
                    continue;
                }
                // ファイル
                $needle = '/^[!-~]+.' . $ext . '$/u';
                if (preg_match($needle, $file)) {
                    $timestamp = filemtime($targetFile);
                    $filesize = filesize($targetFile);
                    $filedata = $mResourceStatus->get($file);
                    if (empty($filedata)) {
                        $filedata = [
                            $file,
                            $timestamp,
                            $filesize,
                            0,
                            0
                        ];
                    } else {
                        if ($filedata[1] != $timestamp) {
                            // ファイルが更新されているので、インポートフラグをリセット
                            $filedata[3] = 0;
                        }
                        $filedata[1] = $timestamp;
                        $filedata[2] = $filesize;
                    }
                    $status[$file] = $filedata;
                    $mResourceStatus->set($filedata);
                }
            }
            $mResourceStatus->save();
            ksort($status);
        }
        return ['Dirs' => $dirs, 'Files' => $status];
    }

}