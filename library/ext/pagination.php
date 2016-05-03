<?php
namespace Ext;
/**
 * 极精简的翻页
 * @author chenyuwen
 * 
 * @code php
 * 
 * $page = new \Ext\Pagination(10, 500, 20, 4);
 * var_dump($page->to_array());
 * 
 * @endcode
 */
class Pagination
{
    /**
     * 最大翻页
     * @var int
     */
    public $maximum_page;
    
    /**
     * 当前页
     * @var int
     */
    public $current_page;
    
    /**
     * 第一页
     * @var int
     */
    public $first_page;
    
    /**
     * 最后一页
     * @var int
     */
    public $last_page;
    
    /**
     * 所有数量
     * @var int
     */
    public $total = 0;
    
    
    /**
     * 初始化
     * @param int $current_page 当前页面
     * @param int $total 数据记录总数
     * @param int $limit 每页显示数据个数
     * @param int $bars_num 翻页按钮个数
     * @param int $page_max 最大翻页
     */
    public function __construct($current_page, $total = 100, $limit = 20, $bars_num = 5, $max_page = 50)
    {
        // 总数量
        $this->total = $total;
        
        // 修正最大显示页
        $this->maximum_page = $this->range($max_page, 1, intval(ceil($total / $limit)));
        
        // 修正当前页
        $this->current_page = $this->range($current_page, 1, $this->maximum_page);
        
        // 修正第一个翻页按钮
        $this->first_page = $this->range($this->current_page - intval(ceil($bars_num / 2)), 1, $this->maximum_page);
        
        // 修正最后一个翻页按钮
        $this->last_page = $this->range($this->first_page + $bars_num, 1, $this->maximum_page);
    }
    
    /**
     * 修正区间值
     * @param int $int
     * @param int $min
     * @param int $max
     */
    protected function range($int, $min, $max)
    {
        $int = $int > $max ? $max : $int;
        $int = $int < $min ? $min : $int;
        return $int;
    }
    
    /**
     * 获取数据数组
     */
    public function to_array()
    {
        return get_object_vars($this);
    }
    
    /**
     * 通过继承覆盖此方法输出html代码
     * @todo
     */
    public function __toString()
    {}
}