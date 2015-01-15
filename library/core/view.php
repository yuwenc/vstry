<?php
namespace Core;
/**
 * 抽象视图处理
 *
 * @author chenyuwen
 */

class View
{
    /**
     * 脚本
     * @var array
     */
    protected static $script = array();
    
    /**
     * style样式
     * @var array
     */
    protected static $css = array();
    
    /**
     * 视图文件
     * @var string
     */
    private $__view = NULL;
    
    /**
     * 脚本文件
     */
    public static function script($file = NULL)
    {
        if (is_null($file))
        {
            $html = '';
            foreach (self::$script as $script)
            {
                $html .= sprintf('<script type="text/javascript" src="%s"></script>', $script);
            }
            return $html;
        }
        else 
        {
            array_push(self::$script, $file);
        }
    }
    
    /**
     * 样式文件
     * @param string $file
     */
    public static function css($file = NULL)
    {
        if (is_null($file))
        {
            $html = '';
            foreach (self::$css as $css)
            {
                $html .= sprintf('<link rel="stylesheet" href="%s">', $css);
            }
            return $html;
        }
        else 
        {
            array_push(self::$css, $file);
        }
    }
    
    /**
     * 初始化
     *
     * @param string $file 视图文件路径
     * @param array $data
     */
    public function __construct($file)
    {
    	if(file_exists($file))
    	{
        	$this->__view = $file;
    	}
    	else
    	{
    		throw new \Exception($file.' is not exits!');
    	}
    }
    
    /**
     * 魔术方法设置数据
     * @param string $k
     * @param mix(int|string|array|object) $v
     */
    public function __set($k, $v)
    {
        $this->$k = $v;
    }
    
    /**
     * 使用ob_start获取缓存数据
     *
     * @return string
     */
    public function __toString()
    {
        ob_start();
        require $this->__view;
        return ob_get_clean ();
    }
}