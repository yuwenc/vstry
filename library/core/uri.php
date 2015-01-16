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
		return trim ( $_SERVER ['REQUEST_URI'], '/' );
	}
	
	/**
	 * array to path
	 * @param array $arr
	 * @param string $domain
	 */
	public static function a2p($arr)
	{
		$path = '/';
	    if(empty(\Core\Application::get_module()))
        {
        	$path .= \Core\Application::get_module().'/';
        }
		foreach ($arr as $key => $val)
		{
			$path .= $key.'/'.$val.'/';
		}
        return rtrim($path, '/');
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
}