<?php
namespace Symfony\Component\Translation\Extractor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;
class PhpExtractor extends AbstractFileExtractor implements ExtractorInterface
{
    const MESSAGE_TOKEN = 300;
    const METHOD_ARGUMENTS_TOKEN = 1000;
    const DOMAIN_TOKEN = 1001;
    private $prefix = '';
    protected $sequences = [
        [
            '->',
            'trans',
            '(',
            self::MESSAGE_TOKEN,
            ',',
            self::METHOD_ARGUMENTS_TOKEN,
            ',',
            self::DOMAIN_TOKEN,
        ],
        [
            '->',
            'transChoice',
            '(',
            self::MESSAGE_TOKEN,
            ',',
            self::METHOD_ARGUMENTS_TOKEN,
            ',',
            self::METHOD_ARGUMENTS_TOKEN,
            ',',
            self::DOMAIN_TOKEN,
        ],
        [
            '->',
            'trans',
            '(',
            self::MESSAGE_TOKEN,
        ],
        [
            '->',
            'transChoice',
            '(',
            self::MESSAGE_TOKEN,
        ],
    ];
    public function extract($resource, MessageCatalogue $catalog)
    {
        $files = $this->extractFiles($resource);
        foreach ($files as $file) {
            $this->parseTokens(token_get_all(file_get_contents($file)), $catalog);
            gc_mem_caches();
        }
    }
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    protected function normalizeToken($token)
    {
        if (isset($token[1]) && 'b"' !== $token) {
            return $token[1];
        }
        return $token;
    }
    private function seekToNextRelevantToken(\Iterator $tokenIterator)
    {
        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $t = $tokenIterator->current();
            if (T_WHITESPACE !== $t[0]) {
                break;
            }
        }
    }
    private function skipMethodArgument(\Iterator $tokenIterator)
    {
        $openBraces = 0;
        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $t = $tokenIterator->current();
            if ('[' === $t[0] || '(' === $t[0]) {
                ++$openBraces;
            }
            if (']' === $t[0] || ')' === $t[0]) {
                --$openBraces;
            }
            if ((0 === $openBraces && ',' === $t[0]) || (-1 === $openBraces && ')' === $t[0])) {
                break;
            }
        }
    }
    private function getValue(\Iterator $tokenIterator)
    {
        $message = '';
        $docToken = '';
        $docPart = '';
        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $t = $tokenIterator->current();
            if ('.' === $t) {
                continue;
            }
            if (!isset($t[1])) {
                break;
            }
            switch ($t[0]) {
                case T_START_HEREDOC:
                    $docToken = $t[1];
                    break;
                case T_ENCAPSED_AND_WHITESPACE:
                case T_CONSTANT_ENCAPSED_STRING:
                    if ('' === $docToken) {
                        $message .= PhpStringTokenParser::parse($t[1]);
                    } else {
                        $docPart = $t[1];
                    }
                    break;
                case T_END_HEREDOC:
                    $message .= PhpStringTokenParser::parseDocString($docToken, $docPart);
                    $docToken = '';
                    $docPart = '';
                    break;
                case T_WHITESPACE:
                    break;
                default:
                    break 2;
            }
        }
        return $message;
    }
    protected function parseTokens($tokens, MessageCatalogue $catalog)
    {
        $tokenIterator = new \ArrayIterator($tokens);
        for ($key = 0; $key < $tokenIterator->count(); ++$key) {
            foreach ($this->sequences as $sequence) {
                $message = '';
                $domain = 'messages';
                $tokenIterator->seek($key);
                foreach ($sequence as $sequenceKey => $item) {
                    $this->seekToNextRelevantToken($tokenIterator);
                    if ($this->normalizeToken($tokenIterator->current()) === $item) {
                        $tokenIterator->next();
                        continue;
                    } elseif (self::MESSAGE_TOKEN === $item) {
                        $message = $this->getValue($tokenIterator);
                        if (\count($sequence) === ($sequenceKey + 1)) {
                            break;
                        }
                    } elseif (self::METHOD_ARGUMENTS_TOKEN === $item) {
                        $this->skipMethodArgument($tokenIterator);
                    } elseif (self::DOMAIN_TOKEN === $item) {
                        $domain = $this->getValue($tokenIterator);
                        break;
                    } else {
                        break;
                    }
                }
                if ($message) {
                    $catalog->set($message, $this->prefix.$message, $domain);
                    break;
                }
            }
        }
    }
    protected function canBeExtracted($file)
    {
        return $this->isFile($file) && 'php' === pathinfo($file, PATHINFO_EXTENSION);
    }
    protected function extractFromDirectory($directory)
    {
        $finder = new Finder();
        return $finder->files()->name('*.php')->in($directory);
    }
}
