<?php
/**
 * LogicMake
 * 
 * @category  Admin 
 * @package   Controller
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 * 
 */
namespace pwfadmin\logics;

use Exception;

/**
 * LogicMake Class
 * クラス作成を行うクラス
 * 
 * @category Admin 
 * @package  Controller
 * @author   Takahiro Kotake <podium@teleios.jp>
 * 
 */
class LogicMake
{
    const CLASS_TEMPLATE = PWF_ADMIN_PATH . 'templates' . DIRECTORY_SEPARATOR;

    /**
     * クラスを作成するための基礎設定データ
     *
     * @var array
     */
    protected array $createClassSetting = [];
    /**
     * エラー発生時にメッセージを格納する
     *
     * @var string
     */
    protected string $Message = '';
    protected array $CategoryCode = [
        'PWF_AUTH_CATEGORY_BOTH',
        'PWF_AUTH_CATEGORY_DEVEL',
        'PWF_AUTH_CATEGORY_MANAGE',
    ];
    protected array $LevelCode = [
        'PWF_AUTH_LEVEL_BOTTOM',
        'PWF_AUTH_LEVEL_LOW',
        'PWF_AUTH_LEVEL_MIDDLE',
        'PWF_AUTH_LEVEL_HIGH',
        'PWF_AUTH_LEVEL_TOP',
        'PWF_AUTH_LEVEL_MASTER',
    ];

    /**
     * コンストラクタ
     *
     * @param array $config
     */
    function __construct(array $config)
    {
        $this->createClassSetting = $config;
    }

    /**
     * エラーが発生した場合のエラーメッセージを取得する
     *
     * @return string
     */
    public function getMessage() : string
    {
        return $this->Message;
    }

    /**
     * ファイルパスとネームスペースを作成する
     *
     * @param  integer $target 作成ターゲット (0: アプリケーション用, 1: 管理者用)
     * @param  integer $type 作成クラス (0: controller, 1: model, 2: logic, 3: facade)
     * @return array ['FilePath' => '<ファイルパス>', 'Namespace' => 'ネームスペース', 'MkDir' => [作成が必要なディレクトリ,]]
     */
    protected function makePath(int $target, int $type, string $name) : array
    {
        $file = ($target == PWF_MAKE_TARGET_ADM ? PWF_ADMIN_PATH : APP_PATH);
        $nspace = ($target == PWF_MAKE_TARGET_ADM ? 'admin' : 'app');
        $extend = false;
        $parentClass = '';
        switch ($type) {
        case PWF_MAKE_CLASS_CONTROLLER:
            $file .= 'controllers';
            $nspace .= '\\' . 'controllers';
            $extend = true;
            $parentClass = ($target == PWF_MAKE_TARGET_ADM ? $this->createClassSetting['AdminController']: $this->createClassSetting['AppController']);
            break;
        case PWF_MAKE_CLASS_MODEL:
            $file .= 'models';
            $nspace .= '\\' . 'models';
            $extend = true;
            $parentClass = $this->createClassSetting['Model'];
            break;
        case PWF_MAKE_CLASS_LOGIC:
            $file .= 'logics';
            $nspace .= '\\' . 'logics';
            break;
        case PWF_MAKE_CLASS_FACADE:
            $file .= 'facades';
            $nspace .= '\\' . 'facades';
            break;
        }
        // 作成対象ディレクトリ
        $filename = str_replace('\\', '/', $name);
        $newDirs = explode('/', $filename);
        $className = array_pop($newDirs);
        $basePath = $file;
        $file .= DIRECTORY_SEPARATOR . $filename . '.php';  // 作成ファイル
        // 継承するオブジェクト
        $extendsClass =  '';
        if (!empty($parentClass)) {
            $classParts = explode('\\', $parentClass);
            $extendsClass = array_pop($classParts);
        }
        return [
            'BasePath' => $basePath,
            'TargetPath' => $file, 
            'Namespace' => $nspace . (!empty($newDirs) ? '\\' . implode('\\', $newDirs) : ''), 
            'MkDir' => $newDirs, 
            'Class' => $className,
            'SetExtend' => $extend,
            'ParentClass' => $parentClass,
            'ExtendsClass' => $extendsClass,
        ];
    }

    /**
     * クラスファイルに追加する内容を作成する
     *
     * @param  string $template クラステンプレートファイル
     * @param  array $data 設定するデータ
     * @return string|false 成功した場合はテキストを返し、失敗した場合はfalseを返す
     */
    protected function makeContents(string $template, array $data) : string
    {
        foreach ($data as $key => $value) {
            if ($key == 'Category') {
                ${$key} = $this->CategoryCode[$value];
                continue;
            }
            if ($key == 'Level') {
                ${$key} = $this->LevelCode[$value];
                continue;
            }
            ${$key} = $value;
        }
        ob_start();
        include_once $template;
        $contents = ob_get_contents();
        ob_end_clean();
        if (!$contents) {
            return false;
        }
        return '<?php' . PHP_EOL . $contents;
    }

    /**
     * クラスを作成する
     *
     * @param  array $params
     * @param  string $template
     * @return boolean
     */
    public function build(array $params, string $template = 'class.tpl') : bool
    {
        // 作成に関するデータ生成
        $classInfo = $this->makePath($params['Target'], $params['Type'], $params['Name']);
        $data = array_merge($params, $classInfo);
        // コンテンツ生成
        $contents = $this->makeContents(self::CLASS_TEMPLATE . $template, $data);
        // 対象のファイルを作成
        try {
            $checkDir = $classInfo['BasePath'];
            foreach ($classInfo['MkDir'] as $dir) {
                $checkDir = $checkDir . DIRECTORY_SEPARATOR . $dir;
                if (!is_dir($checkDir)) {
                    mkdir($checkDir, 0644);
                }
            }
            $hFile = fopen($classInfo['TargetPath'], 'w');
            if (!$hFile) {
                $this->Message = 'File Open Error.';
                return false;
            }
            fwrite($hFile, $contents);
            fclose($hFile);
        } catch (Exception $e) {
            $this->Message = $e->getMessage();
            return false;
        }
        return true;
    }
}