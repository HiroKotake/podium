<?php declare(strict_types=1);
/**
 * PHPUnit Test Case Base Class
 */
namespace system\libraries;

use PHPUnit\Framework\TestCase;

/**
 * PodiumUnitTest class
 */
class PodiumUnitTest extends TestCase
{
    public function __construct()
    {
        define('ROOT_PATH', __DIR__ . '/../..');
        include_once __DIR__ . "/../../config/consts.php";
        include_once ROOT_PATH . "/config/controlParams.php";
        include_once ROOT_PATH . "/config" . CONFIG_DIR[ENVEROMENT] . "/caches.php";
        include_once ROOT_PATH . "/config/bootstrap.php";
    }
}