<?php
// 定义框架使用的常量和方法
require realpath ( __DIR__ .'/../library/core/application.php');

// 定义框架使用的常量和方法
require realpath ( __DIR__ .'/../library/core/uri.php');

// manage 后台管理模块
if(\Core\URI::part(0) == 'manage')
{
    // 设置模块url别名和操作系统路径
    \Core\Application::bind_module('manage', realpath ( __DIR__ .'/../manage'));
    // 设置自动加载目录
    \Core\Application::init_include_path ( array (W_APPLICATION_PATH, W_LIBRARY_PATH));    
}
// 默认模块
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
\Core\Application::dispatch(\Core\Application::config()->route_maps, \Core\URI::get_url_path());