<?php
namespace Symfony\Component\HttpKernel\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class Esi extends AbstractSurrogate
{
    public function getName()
    {
        return 'esi';
    }
    public function addSurrogateControl(Response $response)
    {
        if (false !== strpos($response->getContent(), '<esi:include')) {
            $response->headers->set('Surrogate-Control', 'content="ESI/1.0"');
        }
    }
    public function renderIncludeTag($uri, $alt = null, $ignoreErrors = true, $comment = '')
    {
        $html = sprintf('<esi:include src="%s"%s%s />',
            $uri,
            $ignoreErrors ? ' onerror="continue"' : '',
            $alt ? sprintf(' alt="%s"', $alt) : ''
        );
        if (!empty($comment)) {
            return sprintf("<esi:comment text=\"%s\" />\n%s", $comment, $html);
        }
        return $html;
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
        $content = preg_replace('#<esi\:remove>.*?</esi\:remove>#s', '', $content);
        $content = preg_replace('#<esi\:comment[^>]+>#s', '', $content);
        $chunks = preg_split('#<esi\:include\s+(.*?)\s*(?:/|</esi\:include)>#', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $chunks[0] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[0]);
        $i = 1;
        while (isset($chunks[$i])) {
            $options = [];
            preg_match_all('/(src|onerror|alt)="([^"]*?)"/', $chunks[$i], $matches, PREG_SET_ORDER);
            foreach ($matches as $set) {
                $options[$set[1]] = $set[2];
            }
            if (!isset($options['src'])) {
                throw new \RuntimeException('Unable to process an ESI tag without a "src" attribute.');
            }
            $chunks[$i] = sprintf('<?php echo $this->surrogate->handle($this, %s, %s, %s) ?>'."\n",
                var_export($options['src'], true),
                var_export(isset($options['alt']) ? $options['alt'] : '', true),
                isset($options['onerror']) && 'continue' === $options['onerror'] ? 'true' : 'false'
            );
            ++$i;
            $chunks[$i] = str_replace($this->phpEscapeMap[0], $this->phpEscapeMap[1], $chunks[$i]);
            ++$i;
        }
        $content = implode('', $chunks);
        $response->setContent($content);
        $response->headers->set('X-Body-Eval', 'ESI');
        $this->removeFromControl($response);
    }
}
