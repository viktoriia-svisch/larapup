<?php
namespace Illuminate\View\Engines;
use Illuminate\Contracts\View\Engine;
class FileEngine implements Engine
{
    public function get($path, array $data = [])
    {
        return file_get_contents($path);
    }
}
