<?php
namespace Symfony\Component\Translation\Loader;
class PoFileLoader extends FileLoader
{
    protected function loadResource($resource)
    {
        $stream = fopen($resource, 'r');
        $defaults = [
            'ids' => [],
            'translated' => null,
        ];
        $messages = [];
        $item = $defaults;
        $flags = [];
        while ($line = fgets($stream)) {
            $line = trim($line);
            if ('' === $line) {
                if (!\in_array('fuzzy', $flags)) {
                    $this->addMessage($messages, $item);
                }
                $item = $defaults;
                $flags = [];
            } elseif ('#,' === substr($line, 0, 2)) {
                $flags = array_map('trim', explode(',', substr($line, 2)));
            } elseif ('msgid "' === substr($line, 0, 7)) {
                $this->addMessage($messages, $item);
                $item = $defaults;
                $item['ids']['singular'] = substr($line, 7, -1);
            } elseif ('msgstr "' === substr($line, 0, 8)) {
                $item['translated'] = substr($line, 8, -1);
            } elseif ('"' === $line[0]) {
                $continues = isset($item['translated']) ? 'translated' : 'ids';
                if (\is_array($item[$continues])) {
                    end($item[$continues]);
                    $item[$continues][key($item[$continues])] .= substr($line, 1, -1);
                } else {
                    $item[$continues] .= substr($line, 1, -1);
                }
            } elseif ('msgid_plural "' === substr($line, 0, 14)) {
                $item['ids']['plural'] = substr($line, 14, -1);
            } elseif ('msgstr[' === substr($line, 0, 7)) {
                $size = strpos($line, ']');
                $item['translated'][(int) substr($line, 7, 1)] = substr($line, $size + 3, -1);
            }
        }
        if (!\in_array('fuzzy', $flags)) {
            $this->addMessage($messages, $item);
        }
        fclose($stream);
        return $messages;
    }
    private function addMessage(array &$messages, array $item)
    {
        if (\is_array($item['translated'])) {
            $messages[stripcslashes($item['ids']['singular'])] = stripcslashes($item['translated'][0]);
            if (isset($item['ids']['plural'])) {
                $plurals = $item['translated'];
                ksort($plurals);
                end($plurals);
                $count = key($plurals);
                $empties = array_fill(0, $count + 1, '-');
                $plurals += $empties;
                ksort($plurals);
                $messages[stripcslashes($item['ids']['plural'])] = stripcslashes(implode('|', $plurals));
            }
        } elseif (!empty($item['ids']['singular'])) {
            $messages[stripcslashes($item['ids']['singular'])] = stripcslashes($item['translated']);
        }
    }
}
