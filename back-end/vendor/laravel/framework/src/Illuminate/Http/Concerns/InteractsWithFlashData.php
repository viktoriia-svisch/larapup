<?php
namespace Illuminate\Http\Concerns;
trait InteractsWithFlashData
{
    public function old($key = null, $default = null)
    {
        return $this->hasSession() ? $this->session()->getOldInput($key, $default) : $default;
    }
    public function flash()
    {
        $this->session()->flashInput($this->input());
    }
    public function flashOnly($keys)
    {
        $this->session()->flashInput(
            $this->only(is_array($keys) ? $keys : func_get_args())
        );
    }
    public function flashExcept($keys)
    {
        $this->session()->flashInput(
            $this->except(is_array($keys) ? $keys : func_get_args())
        );
    }
    public function flush()
    {
        $this->session()->flashInput([]);
    }
}
