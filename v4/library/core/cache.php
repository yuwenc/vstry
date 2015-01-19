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
    public static $config = array (
    	'cache_dir' => 'cache', 
    	'expires' => 180 
    );
    
    /**
     * Lets you configure the cache properly, passing an array:
     *
     * <code>
     * Cache::configure(array(
     * 'expires' => 180,
     * 'cache_dir' => 'cache'
     * ));
     * </code>
     * 
     * Or passing a key/val:
     *
     * <code>
     * Cache::configure('expires', 180);
     * </code>
     *
     * @access public
     * @param mixed $key the array with de configuration or the key as string
     * @param mixed $val the value for the previous key if it was an string
     * @return void
     */
    public static function configure($key, $val = null)
    {
        if (is_array ( $key ))
        {
            foreach ( $key as $config_name => $config_value )
            {
                self::$config [$config_name] = $config_value;
            }
        }
        else
        {
            self::$config [$key] = $val;
        }
    }
    
    /**
     * Get a route to the file associated to that key.
     *
     * @access public
     * @param string $key
     * @return string the filename of the php file
     */
    public static function generate_cache_key($key)
    {
        return static::$config ['cache_dir'] . '/' . md5 ( $key ) . '.php';
    }
    
    /**
     * Get the data associated with a key
     *
     * @access public
     * @param string $key
     * @return mixed the content you put in, or null if expired or not found
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
     * Put content into the cache
     *
     * @access public
     * @param string $key
     * @param mixed $content the the content you want to store
     * @param bool $raw whether if you want to store raw data or not. If it is true, $content *must* be a string
     * It can be useful for static html caching.
     * @return bool whether if the operation was successful or not
     */
    public static function put($key, $content, $raw = false)
    {
        $dest_file_name = self::generate_cache_key ( $key );
        
        /** Use a unique temporary filename to make writes atomic with rewrite */
        $temp_file_name = str_replace ( ".php", uniqid ( "-", true ) . ".php", $dest_file_name );
        
        $ret = @file_put_contents ( $temp_file_name, $raw ? $content : serialize ( $content ) );
        
        if ($ret !== false)
        {return @rename ( $temp_file_name, $dest_file_name );}
        
        @unlink ( $temp_file_name );
        return false;
    }
    
    /**
     * 清理指定缓存
     *
     * @access public
     * @param string $key
     * @return bool true if the data was removed successfully
     */
    public static function delete($key)
    {
        return @unlink ( self::generate_cache_key ( $key ) );
    }
    
    /**
     * 清理全部缓存
     *
     * @access public
     * @return bool always true
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
     * @param $file the rout to the file
     * @param int $time the number of minutes it was set to expire
     * @return bool if the file has expired or not
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