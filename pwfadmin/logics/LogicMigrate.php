<?php
/**
 * LogicMigrate.php
 * 
 * @category  Logic 
 * @package   Maigrate
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   0.0.1
 * @copyright 2022 Teleios
 *  
 */
namespace pwfadmin\logics;

use pwfadmin\models\ModelMigrate;
use pwfadmin\models\ModelResourceStatus;

/**
 * LogicMigrate Class
 * 
 * @category Logic 
 * @package  Maigrate
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class LogicMigrate extends LogicResource
{
    /**
     * インポートするCSVファイルの格納先ディレクトリ
     */
    const PATH_RESOURCE = ROOT_PATH . 'resources' . DIRECTORY_SEPARATOR . 'databases';

    /**
     * エラーメッセージを格納する配列
     *
     * @var array
     */
    protected array $errorMessage = [];

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
     * 指定したSQLファイルを実行する
     *
     * @param  array $targets array['Dir' => <String:データベース名>, 'Files' => <array:SQLファイル名>]
     * @return boolean 問題なく指定した全てのファイルが終了したらtrueを、一部でも失敗した場合はfalseを返す
     */
    public function execMigrate(array $targets) : array 
    {
        $targetDir = ROOT_PATH . 'resources' . DIRECTORY_SEPARATOR . 'databases' . DIRECTORY_SEPARATOR . $targets['Database'];
        $mMigrate = new ModelMigrate($targets['Database']);
        $result = [];
        foreach ($targets['Files'] as $file) {
            $targetFile = $targetDir . DIRECTORY_SEPARATOR . $file;
            $hFile = fopen($targetFile, 'r');
            if ($hFile) {
                $query = fread($hFile, filesize($targetFile));
                $result[$file]['Result'] = $mMigrate->execQuery($query);
                $result[$file]['Message'] = '';
                if (!$result[$file]['Result']) {
                    $errMsg = '[ERROR](' . $file . ')' . $mMigrate->getErrMessage();
                    $this->errorMessage[] = $errMsg;
                    $result[$file]['Message'] = $errMsg;
                }
            }
            fclose($hFile);
        }
        $mResourceStatus = new ModelResourceStatus($targetDir);
        if ($mResourceStatus->load()) {
            foreach ($result as $file => $vals) {
                $infos = $mResourceStatus->get($file);
                $infos[3] = $vals['Result'];
                $infos[4] = $vals['Result'] ? time() : 0;
                $mResourceStatus->set($infos);
            }
            $mResourceStatus->save();
        }
        return $result;
    }

    /**
     * エラーメッセージを含んだ配列を返す
     *
     * @return array
     */
    public function getErrorMessage() : array
    {
        return $this->errorMessage;
    }
}