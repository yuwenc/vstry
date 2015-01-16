<?php
namespace Core;
/**
 * application
 *
 */
class Application
{
	
    
    /**
     * 调用的模块
     * @var string
     */
    protected static $module = 'application';
    
    /**
     * 配置文件
     * @var array
     */
	protected static $configs = array();    
    
    /**
     * 设置模块
     */
    public static function bind_module($name, $path)
    {
        // 设置模块
        self::$module = $name;
        // 定义项目目录常量
		define ( 'W_APPLICATION_PATH', $path);
		//初始化加载
		require realpath ( __DIR__ .'/../bootstrap.php');
    }
    
    /**
     * 获取模块
     */
    public static function get_module()
    {
        return self::$module;
    }
	
    /**
     * 自动加载目录
     * @param $paths array
     */
    public static function init_include_path($path_arr)
    {
        $include_string = implode ( PATH_SEPARATOR, $path_arr );
        set_include_path($include_string);
    }
    
    /**
     * 自动加载,只支持命名空间方式
     * @param array $config
     * @return \Core\Application
     */
    public static function init_autoload()
    {
        spl_autoload_extensions ( '.php' );
        spl_autoload_register ();
    }
    
	/**
	 * 获取配置文件
	 *
	 * @param string $file 配置文件路径
	 * @param boolean $clear 清除配置信息
	 * @return object
	 */
	public static function config($file_name = 'ini.php')
	{
	
		if(empty(self::$configs[$file_name]))
		{
			$file = W_APPLICATION_PATH.'/storage/config/'.$file_name;
		    if(file_exists($file))
		    {
		    	require $file;
		        $configs[$file_name] = (object) $config;
		    }
		    else 
		    {
		        throw new \Exception("you must set config file path");
		    }
		}
	
		return $configs[$file_name];
	}

	/**
	 * 中断
	 */
	public static function abort($error_code, $view)
	{
        header ( "status: {$error_code}" );
        echo $view;
        exit ();
	}

    /**
     * 获取版本
     */
    public static function version()
    {
        return '0.1';
    }
}