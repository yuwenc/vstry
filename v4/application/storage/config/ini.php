<?php
/**
 * 视图目录
 */
$config['view_dir'] = W_APPLICATION_PATH . '/view';

/**
 * 配置日志的存储绝对路径
 */
$config['log_dir'] = W_APPLICATION_PATH . '/storage/log';

/**
 * 图片资源目录
 */
$config['upload_dir'] = realpath(W_APPLICATION_PATH . '/../public/upload');

/**
 * 图片资源url
 */
$config['upload_url'] = W_DOMAIN.'/upload';

/**
 * 根目录
 */
$config['base_url'] = W_DOMAIN;

/**
 * 开启错误
 */
$config['open_error_log'] = true;

/**
 * 数据库连接
 */
$config['database']['master'] = array (
	'db_target' => 'mysql:host=203.195.149.15;port=3306;dbname=baken', 
	'user_name' => 'baken', 
	'password'  => 'baken', 
	'params'    => array ( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) 
);

/**
 * cookie的配置
 */
$config['cookie'] = array(
	'lifetime'  => null,
	'path'     => '/',
	'domain'   => '',
	'secure'   => false,
	'httponly' => true,
);

/**
 * memcached 配置
 */
$config['cache_option'] = array(
	'servers'    => array(
		'127.0.0.1'  =>  11211,
		'127.0.0.1'  =>  11210,
		'127.0.0.1'  =>  11209,
	 ),
	'cache_life' => 92000,
	'compress'   => false,
);

/**
 * 路由白名单设置
 */
$config['route_maps'] = array(
	'(:let)/(:let)/(:any)'  => '/Controller/${1}::${2}/${3}',
	'(:let)/(:any)'         => '/Controller/${1}::${2}',
	'(:any)'                => '/Controller/Main::index',   //默认的控制器
);