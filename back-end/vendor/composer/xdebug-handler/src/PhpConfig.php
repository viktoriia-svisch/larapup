<?php
namespace Composer\XdebugHandler;
class PhpConfig
{
    public function useOriginal()
    {
        $this->getDataAndReset();
        return array();
    }
    public function useStandard()
    {
        if ($data = $this->getDataAndReset()) {
            return array('-n', '-c', $data['tmpIni']);
        }
        return array();
    }
    public function usePersistent()
    {
        if ($data = $this->getDataAndReset()) {
            Process::setEnv('PHPRC', $data['tmpIni']);
            Process::setEnv('PHP_INI_SCAN_DIR', '');
        }
        return array();
    }
    private function getDataAndReset()
    {
        if ($data = XdebugHandler::getRestartSettings()) {
            Process::setEnv('PHPRC', $data['phprc']);
            Process::setEnv('PHP_INI_SCAN_DIR', $data['scanDir']);
        }
        return $data;
    }
}
