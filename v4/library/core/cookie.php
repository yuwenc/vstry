<?php
namespace Core;

/**
 * 
 * @author chenyuwen
 *
 */
class Cookie
{
    /**
     * cookie的配置
     * @var array
     */
    private static $config = array ('expire' => NULL, 'path' => NULL, 'domain' => NULL, 'secure' => NULL, 'httponly' => NULL );
    
    /**
     * 配置cookie参数
     * 
     * @param array $config
     */
    public static function init_cookie_params()
    {
    	$config = \Core\Application::config()->cookie;
        foreach ( self::$config as $key => $val )
        {
            if (isset ( $config [$key] ))
            {
                self::$config [$key] = $config [$key];
            }
        }
    }
    
    /**
     * 设置cookie
     *
     * @param $key string cookie的键
     * 
     * @param $value mixed cookie的值
     */
    public static function set($name, $value)
    {
        setcookie ( $name, $value, self::$config ['expire'], self::$config ['path'], self::$config ['domain'], self::$config ['secure'], self::$config ['httponly'] );
    }
    
    /**
     * 获取cookie
     *
     * @return mixed
     */
    public static function get($name, $default = NULL)
    {
        if (isset ( $_COOKIE [$name] ))
        {
            return $_COOKIE [$name];
        }
        return $default;
    }
}