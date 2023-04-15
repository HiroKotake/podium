<?php
/**
 * LogicImport.php
 * 
 * @category  Logic 
 * @package   Import
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\logics;

use pwfadmin\models\ModelImport;
use pwfadmin\models\ModelResourceStatus;

/**
 * LogicImport Class
 * 
 * @category Logic 
 * @package  Import
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class LogicImport extends LogicResource
{
    /**
     * インポートするCSVファイルの格納先ディレクトリ
     */
    const PATH_RESOURCE = ROOT_PATH . 'resources' . DIRECTORY_SEPARATOR . 'imports';

    /**
     * Undocumented function
     *
     * @param string $path リソースディレクトリのパス
     */
    function __construct(string $path = '')
    {
        $resourceDir = $path ?: self::PATH_RESOURCE;
        parent::__construct($resourceDir);
    }

    /**
     * データインポートを実施する
     *
     * @param  array $list
     * @param  string $database
     * @return mix  
     */
    public function execImports(array $list, string $database = '')
    {
        $result = [];
        $mImport = new ModelImport($database);
        foreach ($list as $file) {
            // ファイルを指定してインポート実行
            $filename = self::PATH_RESOURCE . DIRECTORY_SEPARATOR . (empty($database) ? '' : $database . DIRECTORY_SEPARATOR) . $file;
            $result[$file]['Result'] = $mImport->import($filename);
            $result[$file]['Message'] = $mImport->getMessage();
        }
        // 結果に基づきstatusファイル更新処理
        $targetDir = self::PATH_RESOURCE . (empty($database) ? '' : DIRECTORY_SEPARATOR . $database);
        $mResourceStatus = new ModelResourceStatus($targetDir);
        if ($mResourceStatus->load()) {
            foreach ($result as $file => $status) {
                $infos = $mResourceStatus->get($file);
                $infos[3] = empty($status['Result']) ? 0 : 1;
                $infos[4] = $status['Result'] ? time() : 0;
                $mResourceStatus->set($infos);
            }
            $mResourceStatus->save();
        } else {
            return false;
        }
        return $result;
    }
}