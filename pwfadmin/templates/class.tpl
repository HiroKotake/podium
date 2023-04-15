/**
 * <?php echo $Class . '.php' . PHP_EOL; ?>
 * 
 * @category  <?php echo $Docs['Category']; ?> 
 * @package   <?php echo $Docs['Package'] . PHP_EOL; ?>
 * @author    <?php echo $Docs['Author'] . PHP_EOL; ?>
 * @license   <?php echo $Docs['License'] . PHP_EOL; ?>
 * @version   <?php echo $Docs['Version'] . PHP_EOL; ?>
 * @copyright <?php echo $Docs['Copyright'] . PHP_EOL; ?>
 *  
 */
namespace <?php echo $Namespace . ';' . PHP_EOL; ?>

<?php if ($SetExtend): ?>
use <?php echo $ParentClass . ';' . PHP_EOL; ?>
<?php endif; ?>

/**
 * <?php echo $Class . ' Class' . PHP_EOL; ?>
 * 
 * @category <?php echo $Docs['Category']; ?> 
 * @package  <?php echo $Docs['Package'] . PHP_EOL; ?>
 * @author   <?php echo $Docs['Author'] . PHP_EOL; ?>
 */
class <?php echo $Class; ?> <?php echo ($SetExtend ? 'extends ' . $ExtendsClass : '') . PHP_EOL; ?>
{
<?php if ($SetExtend && $Type == MAKE_CLASS_MODEL): ?>
    /**
     * 接続するデータベース名
     * config/database.phpで設定しているデータベース名
     */
    protected string $dbName = 'default';

<?php endif; ?>
    /**
     * Constructer
     */
    function __construct()
    {
<?php if ($SetExtend): ?>
<?php if ($Type == MAKE_CLASS_MODEL): ?>
        if (!empty($dbName)) {
            $this->dbName = $dbName;
        }
        parent::__construct($this->dbName);
<?php else: ?>
        parent::__construct();
<?php endif; ?>
<?php endif; ?>
<?php if ($Target == MAKE_TARGET_ADM && $Type == MAKE_CLASS_CONTROLLER): ?>
        $this->adminInitial();
<?php endif; ?>
    }
<?php if ($Target == MAKE_TARGET_ADM && $Type == MAKE_CLASS_CONTROLLER): ?>

    // サンプルファンクション
    public function sample()
    {
        // 権限チェック
        $this->authorityCheck(
            $this->data['UserInfo']->Category, <?php echo $Category; ?>,
            $this->data['UserInfo']->Level, <?php echo $Level . PHP_EOL; ?>
        );

<?php if ($Type == MAKE_CLASS_CONTROLLER): ?>
<?php if ($Target == MAKE_TARGET_APP): ?>
<?php else: ?>
        $this->output->html->view('', $this->data);
<?php endif; ?>
        $this->output->html->adminView('', $this->data);
<?php endif; ?>
    }
<?php endif; ?>

}