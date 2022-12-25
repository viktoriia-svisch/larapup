<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class Ssi extends AbstractSurrogate
{
    public function getName()
    {
        return 'ssi';
    }
    public function addSurrogateControl(Response $response)
    {
        if (false !== strpos($response->getContent(), '<!--#include')) {
            $response->headers->set('Surrogate-Control', 'content="SSI/1.0"');
        }
    }
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = true, $comment = '')
    {
        return sprintf('<!--#include virtual="%s" -->', $uri);
    }
    public function process(Request $request, Response $response)
    {
        $type = $response->headers->get('Content-Type');
        if (empty($type)) {
            $type = 'text/html';
        }
        $parts = explode(';', $type);
        if (!\in_array($parts[0], $this->contentTypes)) {
            return $response;
        }
        $content = $response->getContent();
        $chunks = preg_split('#<!--\#include\s+(.*?)\s*-->#', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $chunks[0] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[0]);
        $i = 1;
        while (isset($chunks[$i])) {
            $options = [];
            preg_match_all('/(virtual)="([^"]*?)"/', $chunks[$i], $matches, PREG_SET_ORDER);
            foreach ($matches as $set) {
                $options[$set[1]] = $set[2];
            }
            if (!isset($options['virtual'])) {
                throw new \RuntimeException('Unable to process an SSI tag without a "virtual" attribute.');
            }
            $chunks[$i] = sprintf('<?php echo $this->surrogate->handle($this, %s, \'\', false) ?>'."\n",
                var_export($options['virtual'], true)
            );
            ++$i;
            $chunks[$i] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[$i]);
            ++$i;
        }
        $content = implode('', $chunks);
        $response->setContent($content);
        $response->headers->set('X-Body-Eval', 'SSI');
        $this->removeFromControl($response);
    }
}
