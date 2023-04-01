<?php
namespace TijsVerkoyen\CssToInlineStyles\Css;
use TijsVerkoyen\CssToInlineStyles\Css\Rule\Processor as RuleProcessor;
use TijsVerkoyen\CssToInlineStyles\Css\Rule\Rule;
class Processor
{
    public function getRules($css, $existingRules = array())
    {
        $css = $this->doCleanup($css);
        $rulesProcessor = new RuleProcessor();
        $rules = $rulesProcessor->splitIntoSeparateRules($css);
        return $rulesProcessor->convertArrayToObjects($rules, $existingRules);
    }
    public function getCssFromStyleTags($html)
    {
        $css = '';
        $matches = array();
        $htmlNoComments = preg_replace('|<!--.*?-->|s', '', $html);
        preg_match_all('|<style(?:\s.*)?>(.*)</style>|isU', $htmlNoComments, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                $css .= trim($match) . "\n";
            }
        }
        return $css;
    }
    private function doCleanup($css)
    {
        $css = preg_replace('/@charset "[^"]++";/', '', $css);
        $css = preg_replace('/@media [^{]*+{([^{}]++|{[^{}]*+})*+}/', '', $css);
        $css = str_replace(array("\r", "\n"), '', $css);
        $css = str_replace(array("\t"), ' ', $css);
        $css = str_replace('"', '\'', $css);
        $css = preg_replace('|/\*.*?\*/|', '', $css);
        $css = preg_replace('/\s\s++/', ' ', $css);
        $css = trim($css);
        return $css;
    }
}
