<?php
class Swift_Mime_Headers_OpenDKIMHeader implements Swift_Mime_Header
{
    private $value;
    private $fieldName;
    public function __construct($name)
    {
        $this->fieldName = $name;
    }
    public function getFieldType()
    {
        return self::TYPE_TEXT;
    }
    public function setFieldBodyModel($model)
    {
        $this->setValue($model);
    }
    public function getFieldBodyModel()
    {
        return $this->getValue();
    }
    public function getValue()
    {
        return $this->value;
    }
    public function setValue($value)
    {
        $this->value = $value;
    }
    public function getFieldBody()
    {
        return $this->value;
    }
    public function toString()
    {
        return $this->fieldName.': '.$this->value."\r\n";
    }
    public function getFieldName()
    {
        return $this->fieldName;
    }
    public function setCharset($charset)
    {
    }
}
