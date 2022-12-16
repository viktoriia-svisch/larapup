<?php
namespace Symfony\Component\Translation\Catalogue;
use Symfony\Component\Translation\Exception\InvalidArgumentException;
use Symfony\Component\Translation\Exception\LogicException;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
abstract class AbstractOperation implements OperationInterface
{
    protected $source;
    protected $target;
    protected $result;
    private $domains;
    protected $messages;
    public function __construct(MessageCatalogueInterface $source, MessageCatalogueInterface $target)
    {
        if ($source->getLocale() !== $target->getLocale()) {
            throw new LogicException('Operated catalogues must belong to the same locale.');
        }
        $this->source = $source;
        $this->target = $target;
        $this->result = new MessageCatalogue($source->getLocale());
        $this->messages = [];
    }
    public function getDomains()
    {
        if (null === $this->domains) {
            $this->domains = array_values(array_unique(array_merge($this->source->getDomains(), $this->target->getDomains())));
        }
        return $this->domains;
    }
    public function getMessages($domain)
    {
        if (!\in_array($domain, $this->getDomains())) {
            throw new InvalidArgumentException(sprintf('Invalid domain: %s.', $domain));
        }
        if (!isset($this->messages[$domain]['all'])) {
            $this->processDomain($domain);
        }
        return $this->messages[$domain]['all'];
    }
    public function getNewMessages($domain)
    {
        if (!\in_array($domain, $this->getDomains())) {
            throw new InvalidArgumentException(sprintf('Invalid domain: %s.', $domain));
        }
        if (!isset($this->messages[$domain]['new'])) {
            $this->processDomain($domain);
        }
        return $this->messages[$domain]['new'];
    }
    public function getObsoleteMessages($domain)
    {
        if (!\in_array($domain, $this->getDomains())) {
            throw new InvalidArgumentException(sprintf('Invalid domain: %s.', $domain));
        }
        if (!isset($this->messages[$domain]['obsolete'])) {
            $this->processDomain($domain);
        }
        return $this->messages[$domain]['obsolete'];
    }
    public function getResult()
    {
        foreach ($this->getDomains() as $domain) {
            if (!isset($this->messages[$domain])) {
                $this->processDomain($domain);
            }
        }
        return $this->result;
    }
    abstract protected function processDomain($domain);
}
