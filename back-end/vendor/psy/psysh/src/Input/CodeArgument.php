<?php
namespace Psy\Input;
use Symfony\Component\Console\Input\InputArgument;
class CodeArgument extends InputArgument
{
    public function __construct($name, $mode = null, $description = '', $default = null)
    {
        if ($mode & InputArgument::IS_ARRAY) {
            throw new \InvalidArgumentException('Argument mode IS_ARRAY is not valid');
        }
        parent::__construct($name, $mode, $description, $default);
    }
}
