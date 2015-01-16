<?php
// 定义框架使用的常量和方法
require realpath ( __DIR__ .'/../library/core/uri.php');
// 定义框架使用的常量和方法
require realpath ( __DIR__ .'/../library/core/application.php');

if(\Core\URI::part(0) == 'manage')
{
    
}
elseif(\Core\URI::part(0) == 'api') 
{
    
}
else 
{
    // 设置模块url别名和操作系统路径
    \Core\Application::bind_module('', realpath ( __DIR__ .'/../application'));
    // 设置自动加载目录
    \Core\Application::init_include_path ( array (W_APPLICATION_PATH, W_LIBRARY_PATH));

}

//初始化自动加载    
\Core\Application::init_autoload();
// 分发请求
\Core\Router::dispatch(\Core\Application::config()->route_maps, \Core\URI::get_url_path());