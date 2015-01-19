<?php
/**
 * 视图目录
 */
$config['view_dir'] = W_APPLICATION_PATH . '/view';


/**
 * 图片资源目录
 */
$config['upload_dir'] = realpath(W_APPLICATION_PATH . '/../public/upload');

/**
 * 图片资源url
 */
$config['upload_url'] = W_DOMAIN.'/upload';

/**
 * 配置日志的存储绝对路径
 */
$config['log_dir'] = W_APPLICATION_PATH . '/storage/log';

/**
 * 开启错误
 */
$config['open_error_log'] = true;

/**
 * 数据库连接
 */
$config['database']['master'] = array (
	'db_target' => 'mysql:host=127.0.0.1;port=3306;dbname=wxenterprise', 
	'user_name' => 'wxenterprise', 
	'password'  => 'wxenterprise', 
	'params'    => array ( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8" ) 
);

/**
 * cookie的配置
 */
$config['cookie'] = array(
	'expire'  => null,
	'path'     => '/',
	'domain'   => '',
	'secure'   => false,
	'httponly' => true,
);

/**
 * cookie的配置
 */
$config['session'] = array(
	'expire'  => null,
	'path'     => '/',
	'domain'   => '',
	'secure'   => false,
	'httponly' => true,
);

/**
 * 路由白名单设置
 */
$config['route_maps'] = array(
	'manage/(:let)/(:let)/(:any)'  => '/Controller/${1}::${2}/${3}',
	'manage/(:let)/(:any)'         => '/Controller/${1}::${2}',
	'manage/(:any)'                => '/Controller/Main::index',   //默认的控制器
);