<?php
// 定义框架使用的常量和方法
require realpath ( __DIR__ .'/../library/core/application.php');

\Core\Application::module('application', realpath ( __DIR__ .'/../application'));

\Core\Application::init_include_path ( array (W_APPLICATION_PATH, W_LIBRARY_PATH));

\Core\Application::init_autoload();

\Core\Router::dispatch();