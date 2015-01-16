<?php
namespace Model;

class Master extends \Core\ORM
{
    public static function database()
    {
        static $db = null;
        if(is_null($db))
        {
            $config = \Core\Application::config()->database['master'];
            $db = new \Core\Database($config['db_target'], $config['user_name'], $config['password'], $config['params']);
        }
        return $db;
    }
}