<?php
namespace Core;

/**
 * 文件缓存
 *
 */
class Cache
{
    /**
     * 配置文件
     */
    protected static $config = array (
    	'cache_dir' => 'cache', 
    	'expires' => 180 
    );
    
    /**
     * 自动配置缓存
     */
    public static function init_configure()
    {
        $config = \Core\Application::config()->cache;
        foreach ( self::$config as $key => $val )
        {
            if (isset ( $config [$key] ))
            {
                self::$config [$key] = $config [$key];
            }
        }
    }
    
    /**
     * 生成缓存的键
     *
     * @access public
     * @param string $key
     * @return string
     */
    public static function generate_cache_key($key)
    {
        return static::$config ['cache_dir'] . '/' . md5 ( $key ) . '.php';
    }
    
    /**
     * 获取缓存数据
     *
     * @access public
     * @param string $key
     * @return mixed the content you set in, or null if expired or not found
     */
    public static function get($key, $raw = false, $custom_time = null)
    {
        if (! self::file_expired ( $file = self::generate_cache_key ( $key ), $custom_time ))
        {
            $content = file_get_contents ( $file );
            return $raw ? $content : unserialize ( $content );
        }
        
        return null;
    }
    
    /**
     * 将内容写入缓存
     *
     * @access public
     * @param string $key
     * @param mixed $content the the content you want to store
     * @param bool $raw whether if you want to store raw data or not. If it is true, $content *must* be a string
     * It can be useful for static html caching.
     * @return bool whether if the operation was successful or not
     */
    public static function set($key, $content, $raw = false)
    {
        $dest_file_name = self::generate_cache_key ( $key );
        
        /** Use a unique temporary filename to make writes atomic with rewrite */
        $temp_file_name = str_replace ( ".php", uniqid ( "-", true ) . ".php", $dest_file_name );
        
        $ret = @file_put_contents ( $temp_file_name, $raw ? $content : serialize ( $content ) );
        
        if ($ret !== false)
        {
        	return @rename ( $temp_file_name, $dest_file_name );
        }
        
        @unlink ( $temp_file_name );
        return false;
    }
    
    /**
     * 清理指定缓存
     *
     * @access public
     * @param string $key
     * @return bool
     */
    public static function delete($key)
    {
        return @unlink ( self::generate_cache_key ( $key ) );
    }
    
    /**
     * 清理全部缓存
     *
     * @access public
     * @return bool
     */
    public static function flush()
    {
        $cache_files = glob ( self::$config ['cache_path'] . '/*.php', GLOB_NOSORT );
        foreach ( $cache_files as $file )
        {
            @unlink ( $file );
        }
        return true;
    }
    
    /**
     * 检测是否过期
     *
     * @access public
     * @param $file 
     * @param int $time 
     * @return bool 
     */
    public static function file_expired($file, $time = null)
    {
        if (! file_exists ( $file ))
        {
            return true;
        }
        return (time () > (filemtime ( $file ) + ($time ? $time : self::$config ['expires'])));
    }
}