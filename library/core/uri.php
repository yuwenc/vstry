<?php
namespace Core;
/**
 * uri处理类
 * @author Yuwenc
 *
 */
class URI 
{
    public static $params = array();
	
	/**
	 * 获取请求的路径
	 */
	public static function get_url_path()
	{
		$url = $_SERVER ['REQUEST_URI'];
		$module = \Core\Application::get_module();
	    if(!empty($module))
        {
        	$url = str_replace('/'.$module, '', $url);
        }
		$n = stripos($url, '?');
		if($n)
		{
			$url = substr($url, 0, $n);
		}
		return trim ( $url, '/' );
	}
	
	/**
	 * array to path
	 * @param array $arr
	 * @param string $domain
	 */
	public static function a2p($arr)
	{
		$path = '/';
		$module = \Core\Application::get_module();
	    if(!empty($module))
        {
        	$path .= $module.'/';
        }
		foreach ($arr as $key => $val)
		{
			$path .= $key.'/'.$val.'/';
		}
        return rtrim($path, '/');
	}
	
	/**
	 * 合并单前url
	 */
	public static function a2p_before(array $arr = array())
	{
		$params = self::p2a();
		if(empty($params))
		{
		    $params = array('main'=>'index');
		}
        $get = $_GET;
        array_shift($get);
        $params = array_merge($params, $get);
		return self::a2p(array_merge($params, $arr));
	}
	
	/**
	 * path to array
	 * @param int $n
	 */
	public static function p2a($n = 0)
	{
		$arr = array();
		$path = self::get_url_path();
		$slice = array_slice(explode('/', $path), $n);
	 	while (count($slice) > 0)
        {
            $arr[array_shift($slice)] = array_shift($slice);
        }
        return $arr;
	}
	
	/**
	 * part of path
	 * @param int $n
	 * @param string $default
	 */
	public static function part($n, $default = null)
	{
		$path = self::get_url_path();
		$path_arr = explode('/', $path);
		if(isset($path_arr[$n]))
		{
			return $path_arr[$n];
		}
		return $default;
	}
	
	/**
	 * 键值
	 */
	public static function kv($key = NULL, $default = null)
	{
	    if(empty(self::$params))
	    {
	        self::$params = self::p2a();
	        $get = $_GET;
	        array_shift($get);
	        self::$params = array_merge(self::$params, $get);
	        self::$params = array_merge(self::$params, $_POST);
	    }
	    if(is_array($key))
	    {
	    	self::$params = array_merge(self::$params, $key);
	    }
	    if(!is_null($key) && !is_array($key))
	    {
    	    if(!isset(self::$params[$key]))
            {
                self::$params[$key] = $default;
            }
            return htmlspecialchars(self::$params[$key]);
	    }
	    return self::$params;
	}
}