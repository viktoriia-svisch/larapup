<?php
namespace Symfony\Component\Translation\Catalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
interface OperationInterface
{
    public function getDomains();
    public function getMessages($domain);
    public function getNewMessages($domain);
    public function getObsoleteMessages($domain);
    public function getResult();
}
