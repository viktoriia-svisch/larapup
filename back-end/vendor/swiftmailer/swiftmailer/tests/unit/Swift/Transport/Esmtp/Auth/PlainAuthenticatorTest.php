<?php
class Swift_Transport_Esmtp_Auth_PlainAuthenticatorTest extends \SwiftMailerTestCase
{
    private $agent;
    protected function setUp()
    {
        $this->agent = $this->getMockery('Swift_Transport_SmtpAgent')->shouldIgnoreMissing();
    }
    public function testKeywordIsPlain()
    {
        $login = $this->getAuthenticator();
        $this->assertEquals('PLAIN', $login->getAuthKeyword());
    }
    public function testSuccessfulAuthentication()
    {
        $plain = $this->getAuthenticator();
        $this->agent->shouldReceive('executeCommand')
             ->once()
             ->with('AUTH PLAIN '.base64_encode(
                        'jack'.chr(0).'jack'.chr(0).'pass'
                    )."\r\n", [235]);
        $this->assertTrue($plain->authenticate($this->agent, 'jack', 'pass'),
            '%s: The buffer accepted all commands authentication should succeed'
            );
    }
    public function testAuthenticationFailureSendRset()
    {
        $plain = $this->getAuthenticator();
        $this->agent->shouldReceive('executeCommand')
             ->once()
             ->with('AUTH PLAIN '.base64_encode(
                        'jack'.chr(0).'jack'.chr(0).'pass'
                    )."\r\n", [235])
             ->andThrow(new Swift_TransportException(''));
        $this->agent->shouldReceive('executeCommand')
             ->once()
             ->with("RSET\r\n", [250]);
        $plain->authenticate($this->agent, 'jack', 'pass');
    }
    private function getAuthenticator()
    {
        return new Swift_Transport_Esmtp_Auth_PlainAuthenticator();
    }
}
