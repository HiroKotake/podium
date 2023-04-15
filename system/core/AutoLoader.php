<?php
/**
 * AutoLoader.php
 * 
 * @category  Core
 * @package   Loader
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace system\core;

/**
 * AutoLoader
 * 
 * @category System
 * @package  Loader
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class AutoLoader
{
    private $alc = null;

    public function __construct()
    {
        $this->alc = [];
    }

    public function autoLoad()
    {
        spl_autoload_register(array($this,'_autoload'));
    }

    private function _autoload ($className)
    {
        $className = ltrim($className, '\\');
        $indexName = str_replace('\\', '_', $className);
        $fileName  = '';

        if (array_key_exists($indexName, $this->alc)) {
            return;
        }

        $fileName  = ROOT_PATH . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        if (!file_exists($fileName)) {
            return;
        }
        $this->alc[$indexName] = $fileName;
        require_once $fileName;
    }
}
