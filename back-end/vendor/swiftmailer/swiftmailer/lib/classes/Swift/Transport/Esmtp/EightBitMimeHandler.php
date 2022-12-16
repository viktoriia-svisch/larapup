<?php
class Swift_Transport_Esmtp_EightBitMimeHandler implements Swift_Transport_EsmtpHandler
{
    protected $encoding;
    public function __construct(string $encoding = '8BITMIME')
    {
        $this->encoding = $encoding;
    }
    public function getHandledKeyword()
    {
        return '8BITMIME';
    }
    public function setKeywordParams(array $parameters)
    {
    }
    public function afterEhlo(Swift_Transport_SmtpAgent $agent)
    {
    }
    public function getMailParams()
    {
        return ['BODY='.$this->encoding];
    }
    public function getRcptParams()
    {
        return [];
    }
    public function onCommand(Swift_Transport_SmtpAgent $agent, $command, $codes = [], &$failedRecipients = null, &$stop = false)
    {
    }
    public function getPriorityOver($esmtpKeyword)
    {
        return 0;
    }
    public function exposeMixinMethods()
    {
        return [];
    }
    public function resetState()
    {
    }
}
