<?php
namespace phpDocumentor\Reflection\DocBlock\Tags;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Fqsen as FqsenRef;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;
class See extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'see';
    protected $refers = null;
    public function __construct(Reference $refers, Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }
    public static function create(
        $body,
        FqsenResolver $resolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    ) {
        Assert::string($body);
        Assert::allNotNull([$resolver, $descriptionFactory]);
        $parts       = preg_split('/\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;
        if (preg_match('/\w:\/\/\w/i', $parts[0])) {
            return new static(new Url($parts[0]), $description);
        }
        return new static(new FqsenRef($resolver->resolve($parts[0], $context)), $description);
    }
    public function getReference()
    {
        return $this->refers;
    }
    public function __toString()
    {
        return $this->refers . ($this->description ? ' ' . $this->description->render() : '');
    }
}
