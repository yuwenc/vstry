<?php
namespace Core;
/**
 * uri处理类
 * @author Yuwenc
 *
 */
class URI 
{
	
	/**
	 * 获取请求的路径
	 */
	public static function get_url_path()
	{
		$url = $_SERVER ['REQUEST_URI'];
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
	public static function a2p_before(array $arr)
	{
		$n = 0;
		$module = \Core\Application::get_module();
		if(!empty($module))
		{
			$n = 1;
		}
		$arr = array_merge(self::p2a($n), $arr);
		return self::a2p($arr);
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
	public static function kv($key, $default = null, $start = 0)
	{
	    static $kv_arr = null;
	    
	    if(empty($kv_arr[$start]))
	    {
	        $kv_arr[$start] = self::p2a($start);
	    }
	    if(!isset($kv_arr[$start][$key]))
	    {
	        if(isset($_GET[$key]))
	        {
	            $kv_arr[$start][$key] =  $_GET[$key];
	        }
	        elseif(isset($_POST[$key]))
	        {
	            $kv_arr[$start][$key] =  $_POST[$key];
	        }
	        else 
	        {
	            $kv_arr[$start][$key] = $default;
	        }
	    }
	    return $kv_arr[$start][$key];
	}
}