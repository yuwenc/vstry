<?php
namespace Core;
/**
 * 错误处理
 * @author chenyuwen
 */

class Error
{
    
    /**
     * 处理异常错误
     * @param \Exception $e
     */
    public static function handler_exception(\Exception $e)
    {
        ! headers_sent () && header ( 'HTTP/1.0 500 Internal Server Error' );
        $file = str_replace(W_APPLICATION_PATH, '', $e->getFile());
        $message = "{$e->getMessage()} [{$file}] ({$e->getLine()})";
        log_message ( $message );
        /*
        echo '<h2>Throw Exception: '.$message."</h2>";
        echo self::fetch_source_code($e->getFile(), $e->getLine());
        exit();
        */
    }
    
    /**
     * 处理致命错误
     */
    public static function handler_fatal()
    {
        $e = error_get_last();
        if ($e)
        {
            self::handler_exception ( new \ErrorException ( $e ['message'], $e ['type'], 0, $e ['file'], $e ['line'] ) );
        }
    }
    
    /**
     * 处理用户错误
     */
    public static function handler_error($errno, $errstr, $file = 0, $line = 0)
    {
        if ((error_reporting () & $errno) === 0)
        {
            return;
        }
        self::handler_exception ( new \ErrorException ( $errstr, $errno, 0, $file, $line ) );
    }
    
    /**
     * 获取指定行的代码格式成html的源代码
     * @param string $file
     * @param integer $number
     * @param integer $padding
     * @return string
     */
    public static function fetch_source_code($file, $number, $padding = 8)
    {
        $start = $number - $padding - 1;
        $start < 1 && $start = 1;
        $lines = array_slice ( file ( $file ), $start, $padding * 2 + 1, true );
        $html = '<div style="border:1px solid #ccc;padding:10px;">';
        foreach ( $lines as $i => $line )
        {
            $line = str_replace(' ', '&nbsp;', $line);
            $html .= '<div style="height:20px;line-height:20px;"><b style="display:block;line-height:18px;padding-right:  8px;width:40px;text-align:right;color: inherit;height:18px;float:left;border-right:1px solid #ccc;">' . sprintf ( '%' . mb_strlen ( $number + $padding ) . 'd', $i + 1 ) . '</b> &nbsp;&nbsp;' . ($i + 1 == $number ? '<em style="color:red">' . $line . '</em>' : $line);
            $html .= '</div>';
        }
        $html .= "</div>";
        return $html;
    }
}