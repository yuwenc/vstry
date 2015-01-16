<?php
namespace Core;

class Session
{

    /**
     * 设置session的处理对象
     * @param object $handler
     */
    public static function set_operate_handler($handler)
    {
        session_set_save_handler ( array ($handler, 'open' ), array ($handler, 'close' ), array ($handler, 'read' ), array ($handler, 'write' ), array ($handler, 'destroy' ), array ($handler, 'gc' ) );
    }
    
    /**
     * 设置cookie配置
     * @param array $config
     */
    public static function init_cookie_params()
    {
    	$config = \Core\Application::config()->session;
        session_set_cookie_params ( $config ['expire'], $config ['path'], $config ['domain'], $config ['secure'], $config ['httponly'] );
    }
    
    /**
     * session 自动开启
     */
    protected static function auto_start()
    {
        if (! session_id ())
        {
            session_start ();
        }
    }
    
    /**
     * 退出登录，清除session
     */
    public static function destory()
    {
        static::auto_start ();
        session_destroy ();
        session_regenerate_id ();
    }
    
    /**
     * 删除$_SESSION指定的键
     * @param string $key
     */
    public static function clear($key)
    {
        static::auto_start ();
        if (isset ( $_SESSION [$key] ))
        {
            unset ( $_SESSION [$key] );
        }
        return true;
    }
    
    /**
     * 设置 session
     * @param string $name
     * @param mix $value
     */
    public static function set($name, $value)
    {
        static::auto_start ();
        $_SESSION [$name] = $value;
        return true;
    }
    
    /**
     * 获取session
     * @param string $name
     * @return mix
     */
    public static function get($name)
    {
        static::auto_start ();
		if (isset ( $_SESSION [$name] ))
        {
        	return $_SESSION [$name];
        }
        return NULL;
    }
}