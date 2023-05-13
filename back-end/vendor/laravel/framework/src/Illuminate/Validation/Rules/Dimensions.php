<?php
namespace Illuminate\Validation\Rules;
class Dimensions
{
    protected $constraints = [];
    public function __construct(array $constraints = [])
    {
        $this->constraints = $constraints;
    }
    public function width($value)
    {
        $this->constraints['width'] = $value;
        return $this;
    }
    public function height($value)
    {
        $this->constraints['height'] = $value;
        return $this;
    }
    public function minWidth($value)
    {
        $this->constraints['min_width'] = $value;
        return $this;
    }
    public function minHeight($value)
    {
        $this->constraints['min_height'] = $value;
        return $this;
    }
    public function maxWidth($value)
    {
        $this->constraints['max_width'] = $value;
        return $this;
    }
    public function maxHeight($value)
    {
        $this->constraints['max_height'] = $value;
        return $this;
    }
    public function ratio($value)
    {
        $this->constraints['ratio'] = $value;
        return $this;
    }
    public function __toString()
    {
        $result = '';
        foreach ($this->constraints as $key => $value) {
            $result .= "$key=$value,";
        }
        return 'dimensions:'.substr($result, 0, -1);
    }
}
