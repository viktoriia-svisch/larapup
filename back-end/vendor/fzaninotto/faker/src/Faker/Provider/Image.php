<?php
namespace Faker\Provider;
class Image extends Base
{
    protected static $categories = array(
        'abstract', 'animals', 'business', 'cats', 'city', 'food', 'nightlife',
        'fashion', 'people', 'nature', 'sports', 'technics', 'transport'
    );
    public static function imageUrl($width = 640, $height = 480, $category = null, $randomize = true, $word = null, $gray = false)
    {
        $baseUrl = "https:
        $url = "{$width}/{$height}/";
        if ($gray) {
            $url = "gray/" . $url;
        }
        if ($category) {
            if (!in_array($category, static::$categories)) {
                throw new \InvalidArgumentException(sprintf('Unknown image category "%s"', $category));
            }
            $url .= "{$category}/";
            if ($word) {
                $url .= "{$word}/";
            }
        }
        if ($randomize) {
            $url .= '?' . static::randomNumber(5, true);
        }
        return $baseUrl . $url;
    }
    public static function image($dir = null, $width = 640, $height = 480, $category = null, $fullPath = true, $randomize = true, $word = null)
    {
        $dir = is_null($dir) ? sys_get_temp_dir() : $dir; 
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('Cannot write to directory "%s"', $dir));
        }
        $name = md5(uniqid(empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'], true));
        $filename = $name .'.jpg';
        $filepath = $dir . DIRECTORY_SEPARATOR . $filename;
        $url = static::imageUrl($width, $height, $category, $randomize, $word);
        if (function_exists('curl_exec')) {
            $fp = fopen($filepath, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
            fclose($fp);
            curl_close($ch);
            if (!$success) {
                unlink($filepath);
                return false;
            }
        } elseif (ini_get('allow_url_fopen')) {
            $success = copy($url, $filepath);
        } else {
            return new \RuntimeException('The image formatter downloads an image from a remote HTTP server. Therefore, it requires that PHP can request remote hosts, either via cURL or fopen()');
        }
        return $fullPath ? $filepath : $filename;
    }
}
