<?php
namespace Core;

class Curl
{
    /**
     * 创建一个curl请求
     * @param string $method
     * @param string $host
     * @param mix $params
     * @param array $options
     * @return object
     */
    public static function request($host, $options = array())
    {
        $defaults = array (CURLOPT_HEADER => 0, CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 3, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false );
        $ch = curl_init ( $host );
        curl_setopt_array ( $ch, ( array ) $options + $defaults );
        $object = new \stdClass ();
        $object->response = curl_exec ( $ch );
        $object->info = curl_getinfo ( $ch );
        $object->error_code = curl_errno ( $ch );
        $object->error = curl_error ( $ch );
        curl_close ( $ch );
        return $object;
    }
    
    /**
     * post请求
     * @param string $host 
     * @param array $data 
     * @param array $options 
     * @return Ambigous <object, stdClass>
     */
    public static function post($host, $params = null, $options = array())
    {
        !is_null($params) && $defaults[CURLOPT_POSTFIELDS] = $params;
        $defaults [CURLOPT_CUSTOMREQUEST] = 'POST';
        $options = $options + $defaults;
        return self::request($host, $options);
    }
    
    /**
     * @param $host string
     * @param $data array
     * @param $options array()
     * get请求
     */
    public static function get($host, $options = array())
    {
        return self::request($host, $options );
    }
    
    /**
     * put请求
     * @param string $host
     * @param mix $params
     * @param array $options
     */
    public static function put($host, $params = null, $options = array())
    {
        !is_null($params) && $defaults [CURLOPT_POSTFIELDS] = $params;
        $defaults [CURLOPT_CUSTOMREQUEST] = 'PUT';
        $options = $options + $defaults;
        return self::request($host, $options);
    }
    
    /**
     * 删除请求
     * @param string $host
     * @param mix $params
     * @param array $options
     */
    public static function delete($host, $params = null, $options = array())
    {
        !is_null($params) && $defaults [CURLOPT_POSTFIELDS] = $params;
        $defaults [CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $options = $options + $defaults;
        return self::request($host, $options);
    }
    
    /**
     * 创建一个scoket请求
     * 
     * @phpcode
     * 
     * $curl_scoket = \Core\Curl::scoket('192.168.1.56')
     * $rs = fwrite($curl_scoket, "xxxxxx")
     * if($rs){
     * 	 fclose($connect)
     * }
     * 
     * @endphpcode
     *
     * @param string $ip 被请求的ip地址
     * @param array $buffer 缓冲大小
     * @param array $blocking [1|0]是否阻塞 
     */
    public static function scoket($target_ip, $buffer = 128, $blocking = 0)
    {
        $connect = stream_socket_client ( $target_ip, $err, $errstr, 30, STREAM_CLIENT_CONNECT );
        if (! $connect)
        {
            throw new \Exception ( "scoket failed to connect:{$err}\n{$errstr}" );
        }
        stream_set_write_buffer ( $connect, $buffer );
        stream_set_blocking ( $connect, $blocking );
        return $connect;
    }
}