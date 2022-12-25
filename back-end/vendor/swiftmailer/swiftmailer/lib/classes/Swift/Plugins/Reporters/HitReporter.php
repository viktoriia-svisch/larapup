<?php
class Swift_Plugins_Reporters_HitReporter implements Swift_Plugins_Reporter
{
    private $failures = [];
    private $failures_cache = [];
    public function notify(Swift_Mime_SimpleMessage $message, $address, $result)
    {
        if (self::RESULT_FAIL == $result && !isset($this->failures_cache[$address])) {
            $this->failures[] = $address;
            $this->failures_cache[$address] = true;
        }
    }
    public function getFailedRecipients()
    {
        return $this->failures;
    }
    public function clear()
    {
        $this->failures = $this->failures_cache = [];
    }
}
