<?php
namespace Ext;
/**
 * 文件上传封装类, 支持单个文件和多个文件
 * 
 * 
 * @author chenyuwen
 * 
 * phpcode
 * 
 * // 单个文件处理
 * $file = \Ext\Uploader::get('one');
 * $file->move('/store/pic/'.$file->file_name());
 * 
 * // 多个文件处理
 * $files = \Ext\Uploader::get();
 * foreach ($files as $file)
 * {
 * 		$file->move('/store/pic/'. $file->file_name());
 * }
 * 
 * endphp
 * 
 * 
 */

/**
 * 单个键单个文件上传
 * 
 * htmlcode
 * 
 * <input type="file" name="one"/>
 * 
 * endhtml
 *
 * Array
 * (
 *     [one] => Array
 *     (
 *         [name] => C360_2011-10-23 16-23-06.jpg
 *         [type] => image/jpeg
 *         [tmp_name] => /private/var/tmp/phpWw9v36
 *         [error] => 0
 *         [size] => 777403
 *      )
 * )
 */
/**
 * 多键多文件
 *
 * htmlcode
 * 
 * <input type="file" name="one"/>
 * <input type="file" name="two"/>
 * 
 * endhtml
 *
 * Array
 * (
 *     [one] => Array
 *     (
 *         [name] => 1.jpg
 *         [type] => image/jpeg
 *         [tmp_name] => /private/var/tmp/phpnZX2VN
 *         [error] => 0
 *         [size] => 52407
 *     )
 *
 *     [two] => Array
 *     (
 *         [name] => 2cf5e0fe9925bc31629868235edf8db1ca137047.jpg
 *         [type] => image/jpeg
 *         [tmp_name] => /private/var/tmp/phpKlakSR
 *         [error] => 0
 *         [size] => 117895
 *     )
 * )
 */
/**
 * 单个键多个文件
 *
 * htmlcode
 * 
 * <input type="file" name="show[]"/>
 * <input type="file" name="show[]"/>
 * 
 * endhtml
 *
 * Array
 * (
 * [show] => Array
 * (
 * [name] => Array
 * (
 * [0] => 0d338744ebf81a4ca06e8272d72a6059252da629.jpg
 * [1] => 3Fa4D.jpg
 * )
 *
 * [type] => Array
 * (
 * [0] => image/jpeg
 * [1] => image/jpeg
 * )
 *
 * [tmp_name] => Array
 * (
 * [0] => /private/var/tmp/phpofRkJN
 * [1] => /private/var/tmp/phpguk83W
 * )
 *
 * [error] => Array
 * (
 * [0] => 0
 * [1] => 0
 * )
 *
 * [size] => Array
 * (
 * [0] => 40931
 * [1] => 16615
 * )
 *
 * )
 *
 * )
 */
class Uploader
{
    /**
     * 所有上传的文件
     *
     * @var array
     */
    public static $upload_files = array();
    
    /**
     * 是否对上传文件数组进行整理排序
     * @var bool
     */
    private static $is_sort_files = false;
    
    /**
     * 某个文件对象
     *
     * @var array
     */
    protected $_file = array();

    /**
     * 生成一个数组
     */
    private static function sort_upload_files()
    {
        $temp_files = array ();
        foreach ( $_FILES as $key => $val )
        {
            if (isset ( $val['error'] ) && ! is_array( $val['error'] ))
            {
                self::$upload_files[$key] = new self ( $val );
            }
            else
            {
                foreach ( $val as $k => $v )
                {
                    foreach ( $v as $n => $r )
                    {
                        $temp_files [$n] [$k] = $r;
                    }
                }
            }
        }
        if (! empty ( $temp_files ))
        {
            foreach ( $temp_files as $file )
            {
                self::$upload_files[] = new self($file);
            }
        }
        self::$is_sort_files = TRUE;
    }
    
    /**
     * 获取一个指定键名的上传文件或者所有上传文件
     * @param string $key
     * @return \Ext\Uploader
     */
    public static function get($key = NULL, $default = NULL)
    {
        !self::$is_sort_files && self::sort_upload_files();
        
        if (is_null($key))
        {
            return self::$upload_files;
        }
        
        if (isset ( self::$upload_files[$key] ))
        {
        	return self::$upload_files[$key];
        }
        return $default;
    }

    /**
     * 初始化
     *
     * @param $upload_file array
     */
    private function __construct($upload_file)
    {
        $this->_file = $upload_file;
        $this->_file['full_path'] = $this->_file ['tmp_name'];
        $this->_file['is_moved'] = false;
    }

    /**
     * 上传是否成功
     *
     * @return boolean 指示上传是否成功
     */
    public function is_successed()
    {
        return $this->_file['error'] == UPLOAD_ERR_OK;
    }

    /**
     * 返回上传错误代码
     *
     * @return int 上传错误代码
     */
    public function error_code()
    {
        return $this->_file['error'];
    }

    /**
     * 上传文件是否已经从临时目录移出
     *
     * @return boolean 指示文件是否已经移动
     */
    public function is_moved()
    {
        return $this->_file['is_moved'];
    }

    /**
     * 返回上传文件的原名
     *
     * @return string 上传文件的原名
     */
    public function file_name()
    {
        return $this->_file ['name'];
    }

    /**
     * 返回上传文件不带"."的扩展名 [png][jpg]
     *
     * @return string 上传文件的扩展名
     */
    public function file_ext()
    {
        return strtolower(pathinfo ( $this->file_name(), PATHINFO_EXTENSION));
    }

    /**
     * 返回上传文件的大小（字节数）
     *
     * @return int 上传文件的大小
     */
    public function file_size()
    {
        return $this->_file ['size'];
    }

    /**
     * 返回上传文件的 MIME 类型（由浏览器提供，不可信）
     *
     * @return string 上传文件的 MIME 类型
     */
    public function file_mime_type()
    {
        return $this->_file ['type'];
    }

    /**
     * 获得文件的完整路径
     *
     * @return string 文件的完整路径
     */
    public function file_path()
    {
        return $this->_file ['full_path'];
    }

    /**
     * 检查上传的文件是否成功上传，并符合检查条件（文件类型、最大尺寸）
     *
     * 文件类型以扩展名为准，多个扩展名以 , 分割，例如 “jpg, jpeg, png。”。
     *
     * 用法：
     * @code
     * // 检查文件类型和大小 2 * 1204 * 1024 = 2M
     * if ($file->is_valid('jpg, jpeg, png', 2 * 1204 * 1024))
     * {
     * ....
     * }
     * @endcode
     *
     * @param $allowed_types string 允许的扩展名
     *       
     * @param $max_size int 允许的最大上传字节数
     *       
     * @return boolean 是否检查通过
     */
    public function is_valid($allowed_types = null, $max_size = null)
    {
        if (! $this->is_successed())
        {
            return false;
        }
        if ($allowed_types)
        {
            $passed = false;
            $allowed_types = explode(',', $allowed_types);
            foreach ($allowed_types as $val)
            {
                if ($this->file_ext() == trim($val))
                {
                    $passed = true;
                    break;
                }
            }
            if (!$passed)
            {
                return false;
            }
        }
        if ($max_size > 0 && ($this->file_size() > $max_size))
        {
            return false;
        }
        return true;
    }

    /**
     * 移动上传文件到指定位置和文件名
     *
     * @param $to_path_file string 目的地路径
     *       
     * @return \Ext\Uploader
     */
    public function move($to_path_file)
    {
        @mkdir(dirname($to_path_file), 0775, true);
        if ($this->_file['is_moved'])
        {
            $ret = rename( $this->file_path (), $to_path_file );
        }
        else
        {
            $ret = move_uploaded_file( $this->file_path(), $to_path_file);
        }
        if ($ret)
        {
            $this->_file['is_moved'] = true;
            $this->_file['full_path'] = $to_path_file;
        }
        return $this;
    }

    /**
     * 复制上传文件
     *
     * @param $to_path_file string 目的地路径
     *       
     * @return \Ext\Uploader
     */
    public function copy($to_path_file)
    {
        @mkdir(dirname( $to_path_file ), 0777, true);
        copy($this->file_path(), $to_path_file);
        return $this;
    }

    /**
     * 删除上传文件
     *
     * @return \Ext\Uploader
     */
    public function unlink()
    {
        unlink($this->file_path());
        return $this;
    }
}