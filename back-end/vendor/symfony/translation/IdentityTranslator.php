<?php
namespace Symfony\Component\Translation;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;
class IdentityTranslator implements LegacyTranslatorInterface, TranslatorInterface
{
    use TranslatorTrait;
    private $selector;
    public function __construct(MessageSelector $selector = null)
    {
        $this->selector = $selector;
        if (__CLASS__ !== \get_class($this)) {
            @trigger_error(sprintf('Calling "%s()" is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        }
    }
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2, use the trans() one instead with a "%%count%%" parameter.', __METHOD__), E_USER_DEPRECATED);
        if ($this->selector) {
            return strtr($this->selector->choose((string) $id, $number, $locale ?: $this->getLocale()), $parameters);
        }
        return $this->trans($id, ['%count%' => $number] + $parameters, $domain, $locale);
    }
    private function getPluralizationRule(int $number, string $locale): int
    {
        return PluralizationRules::get($number, $locale, false);
    }
}
