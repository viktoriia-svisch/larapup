<?php
namespace phpDocumentor\Reflection;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use Webmozart\Assert\Assert;
final class DocBlockFactory implements DocBlockFactoryInterface
{
    private $descriptionFactory;
    private $tagFactory;
    public function __construct(DescriptionFactory $descriptionFactory, TagFactory $tagFactory)
    {
        $this->descriptionFactory = $descriptionFactory;
        $this->tagFactory = $tagFactory;
    }
    public static function createInstance(array $additionalTags = [])
    {
        $fqsenResolver = new FqsenResolver();
        $tagFactory = new StandardTagFactory($fqsenResolver);
        $descriptionFactory = new DescriptionFactory($tagFactory);
        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService(new TypeResolver($fqsenResolver));
        $docBlockFactory = new self($descriptionFactory, $tagFactory);
        foreach ($additionalTags as $tagName => $tagHandler) {
            $docBlockFactory->registerTagHandler($tagName, $tagHandler);
        }
        return $docBlockFactory;
    }
    public function create($docblock, Types\Context $context = null, Location $location = null)
    {
        if (is_object($docblock)) {
            if (!method_exists($docblock, 'getDocComment')) {
                $exceptionMessage = 'Invalid object passed; the given object must support the getDocComment method';
                throw new \InvalidArgumentException($exceptionMessage);
            }
            $docblock = $docblock->getDocComment();
        }
        Assert::stringNotEmpty($docblock);
        if ($context === null) {
            $context = new Types\Context('');
        }
        $parts = $this->splitDocBlock($this->stripDocComment($docblock));
        list($templateMarker, $summary, $description, $tags) = $parts;
        return new DocBlock(
            $summary,
            $description ? $this->descriptionFactory->create($description, $context) : null,
            array_filter($this->parseTagBlock($tags, $context), function ($tag) {
                return $tag instanceof Tag;
            }),
            $context,
            $location,
            $templateMarker === '#@+',
            $templateMarker === '#@-'
        );
    }
    public function registerTagHandler($tagName, $handler)
    {
        $this->tagFactory->registerTagHandler($tagName, $handler);
    }
    private function stripDocComment($comment)
    {
        $comment = trim(preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#u', '$1', $comment));
        if (substr($comment, -2) === '*/') {
            $comment = trim(substr($comment, 0, -2));
        }
        return str_replace(["\r\n", "\r"], "\n", $comment);
    }
    private function splitDocBlock($comment)
    {
        if (strpos($comment, '@') === 0) {
            return ['', '', '', $comment];
        }
        $comment = preg_replace('/\h*$/Sum', '', $comment);
        preg_match(
            '/
            \A
            (?:(\#\@\+|\#\@\-)\n?)?
            (?:
              (?! @\pL ) # The summary may not start with an @
              (
                [^\n.]+
                (?:
                  (?! \. \n | \n{2} )     # End summary upon a dot followed by newline or two newlines
                  [\n.] (?! [ \t]* @\pL ) # End summary when an @ is found as first character on a new line
                  [^\n.]+                 # Include anything else
                )*
                \.?
              )?
            )
            (?:
              \s*        # Some form of whitespace _must_ precede a description because a summary must be there
              (?! @\pL ) # The description may not start with an @
              (
                [^\n]+
                (?: \n+
                  (?! [ \t]* @\pL ) # End description when an @ is found as first character on a new line
                  [^\n]+            # Include anything else
                )*
              )
            )?
            (\s+ [\s\S]*)? # everything that follows
            /ux',
            $comment,
            $matches
        );
        array_shift($matches);
        while (count($matches) < 4) {
            $matches[] = '';
        }
        return $matches;
    }
    private function parseTagBlock($tags, Types\Context $context)
    {
        $tags = $this->filterTagBlock($tags);
        if (!$tags) {
            return [];
        }
        $result = $this->splitTagBlockIntoTagLines($tags);
        foreach ($result as $key => $tagLine) {
            $result[$key] = $this->tagFactory->create(trim($tagLine), $context);
        }
        return $result;
    }
    private function splitTagBlockIntoTagLines($tags)
    {
        $result = [];
        foreach (explode("\n", $tags) as $tag_line) {
            if (isset($tag_line[0]) && ($tag_line[0] === '@')) {
                $result[] = $tag_line;
            } else {
                $result[count($result) - 1] .= "\n" . $tag_line;
            }
        }
        return $result;
    }
    private function filterTagBlock($tags)
    {
        $tags = trim($tags);
        if (!$tags) {
            return null;
        }
        if ('@' !== $tags[0]) {
            throw new \LogicException('A tag block started with text instead of an at-sign(@): ' . $tags);
        }
        return $tags;
    }
}
