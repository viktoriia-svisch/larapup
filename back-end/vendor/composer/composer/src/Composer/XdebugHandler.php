<?php
namespace Composer;
use Symfony\Component\Console\Output\OutputInterface;
trigger_error('The ' . __NAMESPACE__ . '\XdebugHandler class is deprecated, use Composer\XdebugHandler\XdebugHandler instead,', E_USER_DEPRECATED);
class XdebugHandler extends XdebugHandler\XdebugHandler
{
    const ENV_ALLOW = 'COMPOSER_ALLOW_XDEBUG';
    const ENV_VERSION = 'COMPOSER_XDEBUG_VERSION';
    public function __construct(OutputInterface $output)
    {
        parent::__construct('composer', '--ansi');
    }
}
