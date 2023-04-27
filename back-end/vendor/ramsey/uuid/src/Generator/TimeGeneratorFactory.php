<?php
namespace Ramsey\Uuid\Generator;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Provider\NodeProviderInterface;
use Ramsey\Uuid\Provider\TimeProviderInterface;
class TimeGeneratorFactory
{
    private $nodeProvider;
    private $timeConverter;
    private $timeProvider;
    public function __construct(
        NodeProviderInterface $nodeProvider,
        TimeConverterInterface $timeConverter,
        TimeProviderInterface $timeProvider
    ) {
        $this->nodeProvider = $nodeProvider;
        $this->timeConverter = $timeConverter;
        $this->timeProvider = $timeProvider;
    }
    public function getGenerator()
    {
        return new DefaultTimeGenerator(
            $this->nodeProvider,
            $this->timeConverter,
            $this->timeProvider
        );
    }
}
