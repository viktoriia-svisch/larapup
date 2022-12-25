<?php
namespace Ramsey\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
class DegradedUuid extends Uuid
{
    public function getDateTime()
    {
        if ($this->getVersion() != 1) {
            throw new UnsupportedOperationException('Not a time-based UUID');
        }
        $time = $this->converter->fromHex($this->getTimestampHex());
        $ts = new \Moontoast\Math\BigNumber($time, 20);
        $ts->subtract('122192928000000000');
        $ts->divide('10000000.0');
        $ts->round();
        $unixTime = $ts->getValue();
        return new \DateTime("@{$unixTime}");
    }
    public function getFields()
    {
        throw new UnsatisfiedDependencyException(
            'Cannot call ' . __METHOD__ . ' on a 32-bit system, since some '
            . 'values overflow the system max integer value'
            . '; consider calling getFieldsHex instead'
        );
    }
    public function getNode()
    {
        throw new UnsatisfiedDependencyException(
            'Cannot call ' . __METHOD__ . ' on a 32-bit system, since node '
            . 'is an unsigned 48-bit integer and can overflow the system '
            . 'max integer value'
            . '; consider calling getNodeHex instead'
        );
    }
    public function getTimeLow()
    {
        throw new UnsatisfiedDependencyException(
            'Cannot call ' . __METHOD__ . ' on a 32-bit system, since time_low '
            . 'is an unsigned 32-bit integer and can overflow the system '
            . 'max integer value'
            . '; consider calling getTimeLowHex instead'
        );
    }
    public function getTimestamp()
    {
        if ($this->getVersion() != 1) {
            throw new UnsupportedOperationException('Not a time-based UUID');
        }
        throw new UnsatisfiedDependencyException(
            'Cannot call ' . __METHOD__ . ' on a 32-bit system, since timestamp '
            . 'is an unsigned 60-bit integer and can overflow the system '
            . 'max integer value'
            . '; consider calling getTimestampHex instead'
        );
    }
}
