<?php
namespace Psy\Output;
use Symfony\Component\Console\Output\StreamOutput;
class PassthruPager extends StreamOutput implements OutputPager
{
    public function __construct(StreamOutput $output)
    {
        parent::__construct($output->getStream());
    }
    public function close()
    {
    }
}
