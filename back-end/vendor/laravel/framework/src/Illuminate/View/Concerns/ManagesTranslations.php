<?php
namespace Illuminate\View\Concerns;
trait ManagesTranslations
{
    protected $translationReplacements = [];
    public function startTranslation($replacements = [])
    {
        ob_start();
        $this->translationReplacements = $replacements;
    }
    public function renderTranslation()
    {
        return $this->container->make('translator')->getFromJson(
            trim(ob_get_clean()), $this->translationReplacements
        );
    }
}
