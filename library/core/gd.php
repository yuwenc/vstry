<?php
namespace Core;
/**
 * 图片处理
 * @author EVEN
 *
 */
class GD
{
    /**
     * 配置文件
     */
    protected static $config = array (
    	'expires' => 36000, 
    );
    
    /**
     * 生成url访问路径
     */
    public static function gen_url_path($file_path, $width, $height)
    {
        $file_path = parse_url($file_path, PHP_URL_PATH);
        
        $path_parts  = pathinfo($file_path);
        
        $name = $path_parts['filename'] . "_{$width}-{$height}.jpg";
        
        $path_parts['url'] = $path_parts['dirname'] . '/' .$name;
        
        $path_parts['file'] = $_SERVER['DOCUMENT_ROOT'].$path_parts['dirname'] . '/' .$name;
        
        $path_parts['original_file'] = $_SERVER['DOCUMENT_ROOT'].$file_path;
		
		return $path_parts;
    }
    
    /**
     * 检测是否过期
     *
     * @access public
     * @param $file 
     * @param int $time 
     * @return bool 
     */
    public static function expired($file, $second = null)
    {
        if (! file_exists ( $file ))
        {
            return true;
        }
        return (time () > (filemtime ( $file ) + ($second ? $second : self::$config ['expires'])));
    }
    
	/**
	 * Create a JPEG thumbnail for the given png/gif/jpeg image and return the path to the new image .
	 *
	 * @param string $file the file path to the image
	 * @param int $width the width
	 * @param int $height the height
	 * @param int $quality of image thumbnail
	 * @return string
	 */
	public static function thumb($original_file, $width = 80, $height = 80, $quality = 80, $center = true)
	{
	    $info = self::gen_url_path($original_file, $width, $height);
	    
		// If the thumbnail already exists, we can't write to the directory, or the image file is invalid
	    if(!self::expired($info['file']))
	    {
		    return $info['url'];
	    }
		    
		if( ! directory_is_writable(dirname($info['file'])))
		{ 
		    return false;
		}
		$image = self::open($info['original_file']);
		if(!$image)
		{
			return false;
		}
		
		
		// Resize the image and save it as a compressed JPEG
		$obj = self::resize($image, $width, $height, $center);
		
		if(imagejpeg($obj, $info['file'], $quality))
		{
			return $info['url'];
		}
		return false;
	}
	
	/**
	 * Open a resource handle to a (png/gif/jpeg) image file for processing .
	 *
	 * @param string $file the file path to the image
	 * @return resource
	 */
	public static function open($file)
	{
	    if(!file_exists($file))
		{
		    return false;  
		} 
		
		$info = mime_content_type($file);
		
		$mime_types = array(
		    'image/png' => 'png',
		    'image/jpeg' => 'jpeg',
		    'image/gif' => 'gif',
		);
		
		if(empty($mime_types[$info]))
		{
		    return false;
		}
		
		if( ! in_array($mime_types[$info], array('jpg', 'jpeg', 'png', 'gif')))
		{ 
		    return false;
	    }
	    
		// Open the file using the correct function
		$function = 'imagecreatefrom'. $mime_types[$info];
		
		$image = $function($file);
		if($image)
		{
			return $image;
		}
	}
	
	/**
	 * Resize and crop the image to fix proportinally in the given dimensions .
	 *
	 * @param resource $image the image resource handle
	 * @param int $width the width
	 * @param int $height the height
	 * @param bool $center to crop from image center
	 * @return resource
	 */
	public static function resize($image, $width, $height, $center = FALSE)
	{
	    
		$x = imagesx($image);
		$y = imagesy($image);
		if($width == 0)
		{
		    $width = $x/($y/$height);
		}
		if($height == 0)
		{
		    $height = $y/($x/$width);
		}
		$small = min($x/$width, $y/$height);
		// Default CROP from top left
		$sx = $sy = 0;
		// Crop from image center?
		if($center)
		{
			if($y/$height > $x/$width)
			{
				$sy = $y/2-($height*$small)/2;
			}
			elseif($y/$height < $x/$width)
			{
				$sx = $x/2-($width*$small)/2;
			}
		}
		$new = imagecreatetruecolor($width, $height);
		self::alpha($new);
		// Crop and resize image
		imagecopyresampled($new, $image, 0, 0, $sx, $sy, $width, $height, $small*$width, $small*$height);
		return $new;
	}
	
	/**
	 * Preserve the alpha channel transparency in PNG images
	 *
	 * @param resource $image the image resource handle
	 */
	public static function alpha($image)
	{
		imagecolortransparent($image, imagecolorallocate($image, 0, 0, 0));
		imagealphablending($image, false);
		imagesavealpha($image, true);
	}
}