<?php
namespace Nexmo\Application;
use Nexmo\Entity\FilterInterface;
class Filter implements FilterInterface
{
    const FORMAT = 'Y:m:d:H:i:s';
    protected $start;
    protected $end;
    public function __construct(\DateTime $start, \DateTime $end)
    {
        if($start < $end){
            $this->start = $start;
            $this->end = $end;
        } else {
            $this->start = $end;
            $this->end = $start;
        }
    }
    public function getQuery()
    {
        return [
            'date' => $this->start->format(self::FORMAT) . '-' . $this->end->format(self::FORMAT)
        ];
    }
}
