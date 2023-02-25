<?php
namespace Faker\Test\Provider;
use Faker\Provider\Image;
use PHPUnit\Framework\TestCase;
class ImageTest extends TestCase
{
    public function testImageUrlUses640x680AsTheDefaultSize()
    {
        $this->assertRegExp('#^https:
    }
    public function testImageUrlAcceptsCustomWidthAndHeight()
    {
        $this->assertRegExp('#^https:
    }
    public function testImageUrlAcceptsCustomCategory()
    {
        $this->assertRegExp('#^https:
    }
    public function testImageUrlAcceptsCustomText()
    {
        $this->assertRegExp('#^https:
    }
    public function testImageUrlAddsARandomGetParameterByDefault()
    {
        $url = Image::imageUrl(800, 400);
        $splitUrl = preg_split('/\?/', $url);
        $this->assertEquals(count($splitUrl), 2);
        $this->assertRegexp('#\d{5}#', $splitUrl[1]);
    }
    public function testUrlWithDimensionsAndBadCategory()
    {
        Image::imageUrl(800, 400, 'bullhonky');
    }
    public function testDownloadWithDefaults()
    {
        $url = "http:
        $curlPing = curl_init($url);
        curl_setopt($curlPing, CURLOPT_TIMEOUT, 5);
        curl_setopt($curlPing, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curlPing, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curlPing);
        $httpCode = curl_getinfo($curlPing, CURLINFO_HTTP_CODE);
        curl_close($curlPing);
        if ($httpCode < 200 | $httpCode > 300) {
            $this->markTestSkipped("LoremPixel is offline, skipping image download");
        }
        $file = Image::image(sys_get_temp_dir());
        $this->assertFileExists($file);
        if (function_exists('getimagesize')) {
            list($width, $height, $type, $attr) = getimagesize($file);
            $this->assertEquals(640, $width);
            $this->assertEquals(480, $height);
            $this->assertEquals(constant('IMAGETYPE_JPEG'), $type);
        } else {
            $this->assertEquals('jpg', pathinfo($file, PATHINFO_EXTENSION));
        }
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
