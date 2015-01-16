<?php
namespace Core;
 
/**
 * 数据验证模块
 * @author chenyuwen
 * 
 * @code php
 * 
 * $v = new \Core\Validation();
 * $v->required($name)->message("姓名不能为空");
 * $v->max_val($age, 100)->message("年龄不能大于100岁");
 * $v->min_val($age, 0)->message("年龄不能小于0岁");
 * $v->filter_var(filter_var('yuwenc@live.cn', FILTER_VALIDATE_EMAIL))->message('邮箱格式不正确', 100);
 * if($v->has_error())
 * {
 *     var_dump($v->get_error());
 *     exit();
 * }
 * 
 * @endcode
 *
 */
class Validation
{
    /**
     * 错误消息
     * @var string
     */
    protected $error_mesage = '';
     
    /**
     * 错误代码
     * @var int
     */
    protected $error_code = 0;
     
    /**
     * 是否有错误
     * @var bool
     */
    protected $has_error = false;
     
    /**
     * 最大值
     * @param string $param
     * @param string $val
     * @return \Core\Validation
     */
    public function max_val($param, $val)
    {
        if($param > $val)
        {
            $this->has_error = true;
        }
        return $this;
    }
     
    /**
     * 最小值
     * @param string $param
     * @param string $val
     * @return \Core\Validation
     */
    public function min_val($param, $val)
    {
        if($param < $val)
        {
            $this->has_error = true;
        }
        return $this;
    }
     
    /**
     * 是否匹配
     * @param string $param
     * @param string $exp
     * @return \Core\Validation
     */
    public function match($param, $exp)
    {
        if(!preg_match($exp, $param))
        {
            $this->has_error = true;
        }
        return $this;
    }
     
    /**
     * 支持php内置的 filter_var函数
     * @param bool $rs 验证的结果
     * @return \Core\Validation
     */
    public function filter_var($rs = true)
    {
        if (false === $rs)
        {
            $this->has_error = true;
        }
        return $this;
    }
     
    /**
     * 必须填充
     * @return \Core\Validation
     */
    public function required($param)
    {
        if(empty($param))
        {
            $this->has_error = true;
        }
        return $this;
    }
 
    /**
     * 设置错误信息
     * @param string $msg
     */
    public function message($message, $code = 0)
    {
        if ($this->has_error && empty($this->error_mesage))
        {
            $this->error_mesage = $message;
            $this->error_code = $code;
        }
    }
     
    /**
     * 是否有错误
     */
    public function has_error()
    {
        return $this->has_error;
    }
     
    /**
     * 获取错误消息
     */
    public function get_error($key = NULL)
    {
        $data = array('message'=>$this->error_mesage, 'code'=>$this->error_code);
        if ($key !== NULL && isset($data[$key]))
        {
            return $data[$key];
        }
        return $data;
    }
}