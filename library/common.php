<?php
/**
 * 简化操作的核心函数
 * @date 2012-5-3
 *
 * @author 古嗣小井 279537592@qq.com
 */

function event($event, $value = NULL, $callback = NULL)
{
	static $events = array ();
	if($callback !== NULL)
	{
		if($callback)
		{
			$events[$event][] = $callback;
		}
		else
		{
			unset($events[$event]);
		}
	}
	elseif(isset($events[$event]))
	{
		foreach($events[$event] as $function)
		{
			$value = call_user_func($function, $value);
		}
		return $value;
	}
}

/**
 * 获取请求方法
 */
function get_method()
{
	return strtolower($_SERVER['REQUEST_METHOD']);
}

/**
 * 函数方式调用view类
 * @param string $file
 * @return \Core\View
 */
function view($file)
{
    $file_path = rtrim(\Core\Application::config()->view_dir, W_DS) . W_DS . ltrim($file, W_DS);
	return new \Core\View ( $file_path );
}

/**
 * 获取客户端ip
 */
function client_ip()
{
    $keys = array ('HTTP_X_FORWARDED_FOR', 'CLIENT_IP', 'REMOTE_ADDR' );
    foreach ( $keys as $key )
    {
        if (isset($_SERVER[$key]))
        {
            return $_SERVER[$key];
        }
    }
    return null;
}

/**
 * 如果没有配置日志路径则不写日志，
 * 使用php内置的error_log记录错误日志
 *
 * @param string $message
 * @return bool
 */
function log_message($message, $dir = NULL)
{
	if (is_null($dir))
	{
		$dir = \Core\Application::config ()->log_dir;
		if (empty($dir))
		{
		    return false;
		}
	}
	$client_ip = client_ip();
	if ( !is_dir($dir) )
	{
		@mkdir ( $dir, 0755, TRUE );
	}
	$file =  $dir . '/' . date ( 'Y-m-d' ).'.log'; 
	return error_log ( date ( 'H:i:s ' ) . $client_ip . " $message\n", 3, $file);
}

/**
 * 页面跳转函数
 * @param string $url
 * @param int $code http状态码
 * @param string $method 刷新或者跳转
 */
function redirect($url = NULL, $code = 302, $method = 'location')
{
	header ( $method == 'refresh' ? "Refresh:0;url = $url" : "Location: $url", TRUE, $code );
	exit();
}

/**
 * base64转码
 * 
 * @param string $string to encode
 * @return string
 */
function base64_url_encode($string = NULL)
{
	return strtr ( base64_encode ( $string ), '+/=', '-_~' );
}

/**
 * base64解码
 *
 * @param string $string to decode
 * @return string
 */
function base64_url_decode($string = NULL)
{
	return base64_decode ( strtr ( $string, '-_~', '+/=' ) );
}

/**
 * 字符转gbk格式
 * @param string $string
 * @param string $from_encode
 */
function str_to_gbk($string, $from_encode = 'utf-8')
{
	return iconv ( $from_encode, 'gbk', $string );
}

/**
 * 字符转utf8格式
 * @param string $string
 * @param string $from_encode
 */
function str_to_utf8($string, $from_encode = 'gbk')
{
	return iconv ( $from_encode, 'utf-8', $string );
}

/**
 * Filter a valid UTF-8 string so that it contains only words, numbers,
 * dashes, underscores, periods, and spaces - all of which are safe
 * characters to use in file names, URI, XML, JSON, and (X)HTML.
 *
 * @param string $string to clean
 * @param bool $spaces TRUE to allow spaces
 * @return string
 */
function sanitize($string, $spaces = TRUE)
{
	$search = array(
		'/[^\w\-\. ]+/u',			// Remove non safe characters
		'/\s\s+/',					// Remove extra whitespace
		'/\.\.+/', '/--+/', '/__+/'	// Remove duplicate symbols
	);

	$string = preg_replace($search, array(' ', ' ', '.', '-', '_'), $string);

	if( ! $spaces)
	{
		$string = preg_replace('/--+/', '-', str_replace(' ', '-', $string));
	}

	return trim($string, '-._ ');
}

/**
 * Filter a valid UTF-8 string to be file name safe.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_filename($string)
{
	return sanitize($string, FALSE);
}

/**
 * Create a SEO friendly URL string from a valid UTF-8 string.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_url($string)
{
	return urlencode(mb_strtolower(sanitize($string, FALSE)));
}

/**
 * Create a RecursiveDirectoryIterator object
 *
 * @param string $dir the directory to load
 * @param boolean $recursive to include subfolders
 * @return object
 */
function directory($dir, $recursive = TRUE)
{
	$i = new \RecursiveDirectoryIterator($dir);

	if( ! $recursive) return $i;

	return new \RecursiveIteratorIterator($i, \RecursiveIteratorIterator::SELF_FIRST);
}


/**
 * Make sure that a directory exists and is writable by the current PHP process.
 *
 * @param string $dir the directory to load
 * @param string $chmod value as octal
 * @return boolean
 */
function directory_is_writable($dir, $chmod = 0755)
{
	// If it doesn't exist, and can't be made
	if(! is_dir($dir) AND ! mkdir($dir, $chmod, TRUE)) 
	{
	   return FALSE; 
	}

	// If it isn't writable, and can't be made writable
	if(! is_writable($dir) and ! chmod ( $dir, $chmod ))
	{
        return FALSE;
	}
    return TRUE;
}
    
/**
 * 输出内容格式化的函数
 */
function dump()
{
	$string = '';
	foreach ( func_get_args () as $value )
	{
		$string .= '<pre>' . ( $value === NULL ? 'NULL' : (is_scalar ( $value ) ? $value : print_r ( $value, TRUE )) ) . "</pre>\n";
	}
	echo $string;
}

/**
 * 换行函数
 */
function br($ident = '-')
{
	echo "\n" . str_repeat ( $ident, 50 ) . "\n";
}

/**
 * 把特殊字符转换成html的字符串格式
 * @param string $string to encode
 * @return string
 */
function h($string)
{
	return htmlspecialchars ( $string, ENT_QUOTES, 'utf-8' );
}
