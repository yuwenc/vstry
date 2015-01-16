<?php
namespace Core;
/**
 * 抽象控制器
 *
 * @author chenyuwen
 */
abstract class Controller
{   
    /**
     * 用户自定义初始化
     */
    public function initialize()
    {
    }
    
    /**
     * 输出数据
     * @param string $export 输出数据的格式
     */
    public function send()
    {
    }
    
    /**
     * 分发到控制器
     * 1. 检验action是否允许执行，如果不允许执行则抛出异常，中断执行，否则执行下一步，注：[controller 的私有方法&&保留方法&&静态方法不可执行(view调用controller方法专用)],
     * 2. 进行初始化
     * 3. 执行action
     * 4. 执行send输出
     * @param string
     * @throws \Exception
     */
    public function run($method)
    {
        // 检测action是否可以执行
        $bool = method_exists ( $this, $method ) && $reflector = new \ReflectionMethod ( $this, $method );
        if (! $bool || ! $reflector->isPublic () || $reflector->isStatic () || $method == 'run' || $method == 'initialize' || $method == 'send')
        {
            \Core\Application::abort(404, 'action not exits!');
        }
        // 如果不想让他顺序进行抛出异常即可中断
        $this->initialize ();
        $this->$method ();
        $this->send ();
    }
}