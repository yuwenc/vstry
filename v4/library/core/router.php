<?php
namespace Core;

/**
 * 路由解析，多个路由规则同时满足的情况下， 排在前面优先解析
 * @author chenyuwen
 * 
 * :any 任意字符
 * :num 数字
 * :let 字母
 * :ln 字母和数字
 * 
 * code php
 * 
 * //使用例子一
 * $pattern_map => array(
 * '(:let)/(:let)/(:any)'  => '/Controller/${1}::${2}/${3}', //控制器和动作和参数
 * '(:let)/(:let)'         => '/Controller/${1}::${2}',   // 控制器和动作
 * '(:any)'                => '/Controller/Main::index',   //默认的控制器
 * );
 * $url = '/blog/joe/show/555';
 * $result = \Core\Router::matchAll($pattern_map, $url);
 * echo $result;
 * 
 * //使用例子二
 * $pattern = '(:let)/(:let)/(:any)';
 * $url = '/main/index';
 * if(\Core\Router::match($pattern, $url))
 * {
 *     echo 'success'
 * }
 * endcode
 * 
 */
class Router
{
    /**
     * 匹配的规则
     * @var string
     */
    protected static $matched_pattern = null;
    
    /**
     * 匹配一个路由映射表
     * @param $pattern_map array
     * @param $url string
     * @return string
     * 
     * @codephp
     * 
     * $pattern_map => array(
     *     '(:let)/(:let)/(:any)'  => '/Controller/${1}::${2}/${3}', //控制器和动作和参数
     *     '(:let)/(:let)'         => '/Controller/${1}::${2}',   // 控制器和动作
     *     '(:any)'                => '/Controller/Main::index',   //默认的控制器
     * );
     * 
     * $url = '/main/index';
     * 
     * self::matchAll($pattern_map, $url);
     * 
     * @endcode
     */
    public static function match_all($pattern_map, $url)
    {
        foreach ($pattern_map as $key => $val)
        {
            if(FALSE !== self::match($key, $url))
            {
                return preg_replace( '#^'.self::$matched_pattern.'$#', $val, $url );
            }
        }
        return '';
    }
    
    /**
     * 
     * 匹配一个路由
     * @param string $pattern
     * @param string $url
     * @return bool
     * 
     * @codephp
     * 
     * $pattern = '(:let)/(:let)/(:any)';
     * $url = '/main/index';
     * self::match($pattern, $url);
     * 
     * @endcode
     * 
     */
    public static function match($pattern, $url)
    {
        $key = str_replace ( array (':any', ':let', ':num', ':ln' ), array ('.*', '[a-zA-Z]+', '[0-9]+', '[a-zA-Z0-9]+' ), $pattern );
        if (preg_match ( "#^{$key}$#", $url, $vars))
        {
            self::$matched_pattern = $key;
            return $vars;
        }
        return false;
    }
}