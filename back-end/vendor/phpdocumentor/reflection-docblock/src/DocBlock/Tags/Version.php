<?php
namespace phpDocumentor\Reflection\DocBlock\Tags;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;
final class Version extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'version';
    const REGEX_VECTOR = '(?:
        \d\S*
        |
        [^\s\:]+\:\s*\$[^\$]+\$
    )';
    private $version = '';
    public function __construct($version = null, Description $description = null)
    {
        Assert::nullOrStringNotEmpty($version);
        $this->version = $version;
        $this->description = $description;
    }
    public static function create($body, DescriptionFactory $descriptionFactory = null, TypeContext $context = null)
    {
        Assert::nullOrString($body);
        if (empty($body)) {
            return new static();
        }
        $matches = [];
        if (!preg_match('/^(' . self::REGEX_VECTOR . ')\s*(.+)?$/sux', $body, $matches)) {
            return null;
        }
        return new static(
            $matches[1],
            $descriptionFactory->create(isset($matches[2]) ? $matches[2] : '', $context)
        );
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function __toString()
    {
        return $this->version . ($this->description ? ' ' . $this->description->render() : '');
    }
}
