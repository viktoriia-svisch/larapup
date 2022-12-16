<?php
namespace Illuminate\Http\Testing;
use Illuminate\Http\UploadedFile;
class File extends UploadedFile
{
    public $name;
    public $tempFile;
    public $sizeToReport;
    public function __construct($name, $tempFile)
    {
        $this->name = $name;
        $this->tempFile = $tempFile;
        parent::__construct(
            $this->tempFilePath(), $name, $this->getMimeType(),
            null, true
        );
    }
    public static function create($name, $kilobytes = 0)
    {
        return (new FileFactory)->create($name, $kilobytes);
    }
    public static function image($name, $width = 10, $height = 10)
    {
        return (new FileFactory)->image($name, $width, $height);
    }
    public function size($kilobytes)
    {
        $this->sizeToReport = $kilobytes * 1024;
        return $this;
    }
    public function getSize()
    {
        return $this->sizeToReport ?: parent::getSize();
    }
    public function getMimeType()
    {
        return MimeType::from($this->name);
    }
    protected function tempFilePath()
    {
        return stream_get_meta_data($this->tempFile)['uri'];
    }
}
