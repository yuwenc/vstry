<?php
/**
 * 所有框架使用的常量都以W开头，在此文件里面定义
 */
define('W_START_TIME', time());

define('W_START_MICROTIME', microtime(true));

define('W_START_MEMORY_USAGE', memory_get_usage());

define('W_EXT', '.php');

define('W_DS', '/');

define('W_LIBRARY_PATH', realpath(__DIR__));

define('W_DOMAIN', (strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'
	. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));

require W_LIBRARY_PATH . '/common.php';