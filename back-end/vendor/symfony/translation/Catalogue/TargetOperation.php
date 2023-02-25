<?php
namespace Symfony\Component\Translation\Catalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
class TargetOperation extends AbstractOperation
{
    protected function processDomain($domain)
    {
        $this->messages[$domain] = [
            'all' => [],
            'new' => [],
            'obsolete' => [],
        ];
        $intlDomain = $domain.MessageCatalogueInterface::INTL_DOMAIN_SUFFIX;
        foreach ($this->source->all($domain) as $id => $message) {
            if ($this->target->has($id, $domain)) {
                $this->messages[$domain]['all'][$id] = $message;
                $this->result->add([$id => $message], $this->target->defines($id, $intlDomain) ? $intlDomain : $domain);
                if (null !== $keyMetadata = $this->source->getMetadata($id, $domain)) {
                    $this->result->setMetadata($id, $keyMetadata, $domain);
                }
            } else {
                $this->messages[$domain]['obsolete'][$id] = $message;
            }
        }
        foreach ($this->target->all($domain) as $id => $message) {
            if (!$this->source->has($id, $domain)) {
                $this->messages[$domain]['all'][$id] = $message;
                $this->messages[$domain]['new'][$id] = $message;
                $this->result->add([$id => $message], $this->target->defines($id, $intlDomain) ? $intlDomain : $domain);
                if (null !== $keyMetadata = $this->target->getMetadata($id, $domain)) {
                    $this->result->setMetadata($id, $keyMetadata, $domain);
                }
            }
        }
    }
}
