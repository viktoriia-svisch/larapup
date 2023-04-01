<?php
abstract class Swift_Transport_AbstractSmtpTest extends \SwiftMailerTestCase
{
    abstract protected function getTransport($buf);
    public function testStartAccepts220ServiceGreeting()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $this->finishBuffer($buf);
        try {
            $this->assertFalse($smtp->isStarted(), '%s: SMTP should begin non-started');
            $smtp->start();
            $this->assertTrue($smtp->isStarted(), '%s: start() should have started connection');
        } catch (Exception $e) {
            $this->fail('220 is a valid SMTP greeting and should be accepted');
        }
    }
    public function testBadGreetingCausesException()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("554 I'm busy\r\n");
        $this->finishBuffer($buf);
        try {
            $this->assertFalse($smtp->isStarted(), '%s: SMTP should begin non-started');
            $smtp->start();
            $this->fail('554 greeting indicates an error and should cause an exception');
        } catch (Swift_TransportException $e) {
            $this->assertFalse($smtp->isStarted(), '%s: start() should have failed');
        }
    }
    public function testStartSendsHeloToInitiate()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^HELO example.org\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 ServerName'."\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
        } catch (Exception $e) {
            $this->fail('Starting SMTP should send HELO and accept 250 response');
        }
    }
    public function testInvalidHeloResponseCausesException()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^HELO example.org\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('504 WTF'."\r\n");
        $this->finishBuffer($buf);
        try {
            $this->assertFalse($smtp->isStarted(), '%s: SMTP should begin non-started');
            $smtp->start();
            $this->fail('Non 250 HELO response should raise Exception');
        } catch (Swift_TransportException $e) {
            $this->assertFalse($smtp->isStarted(), '%s: SMTP start() should have failed');
        }
    }
    public function testDomainNameIsPlacedInHelo()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("HELO mydomain.com\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 ServerName'."\r\n");
        $this->finishBuffer($buf);
        $smtp->setLocalDomain('mydomain.com');
        $smtp->start();
    }
    public function testSuccessfulMailCommand()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<me@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn("250 OK\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
        } catch (Exception $e) {
            $this->fail('MAIL FROM should accept a 250 response');
        }
    }
    public function testInvalidResponseCodeFromMailCausesException()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<me@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('553 Bad'."\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
            $this->fail('MAIL FROM should accept a 250 response');
        } catch (Swift_TransportException $e) {
        }
    }
    public function testSenderIsPreferredOverFrom()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getSender')
                ->once()
                ->andReturn(['another@domain.com' => 'Someone']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<another@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testReturnPathIsPreferredOverSender()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getSender')
                ->once()
                ->andReturn(['another@domain.com' => 'Someone']);
        $message->shouldReceive('getReturnPath')
                ->once()
                ->andReturn('more@domain.com');
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<more@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testSuccessfulRcptCommandWith250Response()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<me@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
        } catch (Exception $e) {
            $this->fail('RCPT TO should accept a 250 response');
        }
    }
    public function testUtf8AddressWithIdnEncoder()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@dömain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bär' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<me@xn--dmain-jua.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@xn--br-via>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testUtf8AddressWithUtf8Encoder()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf, null, new Swift_AddressEncoder_Utf8AddressEncoder());
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['më@dömain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['föö@bär' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<më@dömain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<föö@bär>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testNonEncodableSenderCausesException()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['më@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
            $this->fail('më@domain.com cannot be encoded (not observed)');
        } catch (Swift_AddressEncoderException $e) {
            $this->assertEquals('më@domain.com', $e->getAddress());
        }
    }
    public function testMailFromCommandIsOnlySentOncePerMessage()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<me@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->never()
            ->with("MAIL FROM:<me@domain.com>\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testMultipleRecipientsSendsMultipleRcpt()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn([
                    'foo@bar' => null,
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                    'tëst@domain' => 'Test user',
                ]);
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<zip@button>\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<test@domain>\r\n")
            ->andReturn(3);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(3)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testCcRecipientsSendsMultipleRcpt()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('getCc')
                ->once()
                ->andReturn([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<zip@button>\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<test@domain>\r\n")
            ->andReturn(3);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(3)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testSendReturnsNumberOfSuccessfulRecipients()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('getCc')
                ->once()
                ->andReturn([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<zip@button>\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('501 Nobody here'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<test@domain>\r\n")
            ->andReturn(3);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(3)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $this->assertEquals(2, $smtp->send($message),
            '%s: 1 of 3 recipients failed so 2 should be returned'
            );
    }
    public function testRsetIsSentIfNoSuccessfulRecipients()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('503 Bad'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RSET\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $this->assertEquals(0, $smtp->send($message),
            '%s: 1 of 1 recipients failed so 0 should be returned'
            );
    }
    public function testSuccessfulDataCommand()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("DATA\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('354 Go ahead'."\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
        } catch (Exception $e) {
            $this->fail('354 is the expected response to DATA');
        }
    }
    public function testBadDataResponseCausesException()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("DATA\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('451 Bad'."\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
            $this->fail('354 is the expected response to DATA (not observed)');
        } catch (Swift_TransportException $e) {
        }
    }
    public function testMessageIsStreamedToBufferForData()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("DATA\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('354 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("\r\n.\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testBadResponseAfterDataTransmissionCausesException()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->once()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->once()
                ->andReturn(['foo@bar' => null]);
        $buf->shouldReceive('write')
            ->once()
            ->with("DATA\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('354 OK'."\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("\r\n.\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn('554 Error'."\r\n");
        $this->finishBuffer($buf);
        try {
            $smtp->start();
            $smtp->send($message);
            $this->fail('250 is the expected response after a DATA transmission (not observed)');
        } catch (Swift_TransportException $e) {
        }
    }
    public function testBccRecipientsAreRemovedFromHeaders()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->zeroOrMoreTimes()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->zeroOrMoreTimes()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('getBcc')
                ->zeroOrMoreTimes()
                ->andReturn([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $message->shouldReceive('setBcc')
                ->once()
                ->with([]);
        $message->shouldReceive('setBcc')
                ->zeroOrMoreTimes();
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testEachBccRecipientIsSentASeparateMessage()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->zeroOrMoreTimes()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->zeroOrMoreTimes()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('getBcc')
                ->zeroOrMoreTimes()
                ->andReturn([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $message->shouldReceive('setBcc')
                ->atLeast()->once()
                ->with([]);
        $message->shouldReceive('setBcc')
                ->once()
                ->with(['zip@button' => 'Zip Button']);
        $message->shouldReceive('setBcc')
                ->once()
                ->with(['test@domain' => 'Test user']);
        $message->shouldReceive('setBcc')
                ->atLeast()->once()
                ->with([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $buf->shouldReceive('write')->once()->with("MAIL FROM:<me@domain.com>\r\n")->andReturn(1);
        $buf->shouldReceive('readLine')->once()->with(1)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("RCPT TO:<foo@bar>\r\n")->andReturn(2);
        $buf->shouldReceive('readLine')->once()->with(2)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("DATA\r\n")->andReturn(3);
        $buf->shouldReceive('readLine')->once()->with(3)->andReturn("354 OK\r\n");
        $buf->shouldReceive('write')->once()->with("\r\n.\r\n")->andReturn(4);
        $buf->shouldReceive('readLine')->once()->with(4)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("MAIL FROM:<me@domain.com>\r\n")->andReturn(5);
        $buf->shouldReceive('readLine')->once()->with(5)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("RCPT TO:<zip@button>\r\n")->andReturn(6);
        $buf->shouldReceive('readLine')->once()->with(6)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("DATA\r\n")->andReturn(7);
        $buf->shouldReceive('readLine')->once()->with(7)->andReturn("354 OK\r\n");
        $buf->shouldReceive('write')->once()->with("\r\n.\r\n")->andReturn(8);
        $buf->shouldReceive('readLine')->once()->with(8)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("MAIL FROM:<me@domain.com>\r\n")->andReturn(9);
        $buf->shouldReceive('readLine')->once()->with(9)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("RCPT TO:<test@domain>\r\n")->andReturn(10);
        $buf->shouldReceive('readLine')->once()->with(10)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("DATA\r\n")->andReturn(11);
        $buf->shouldReceive('readLine')->once()->with(11)->andReturn("354 OK\r\n");
        $buf->shouldReceive('write')->once()->with("\r\n.\r\n")->andReturn(12);
        $buf->shouldReceive('readLine')->once()->with(12)->andReturn("250 OK\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $this->assertEquals(3, $smtp->send($message));
    }
    public function testMessageStateIsRestoredOnFailure()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->zeroOrMoreTimes()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->zeroOrMoreTimes()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('getBcc')
                ->zeroOrMoreTimes()
                ->andReturn([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $message->shouldReceive('setBcc')
                ->once()
                ->with([]);
        $message->shouldReceive('setBcc')
                ->once()
                ->with([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $buf->shouldReceive('write')
            ->once()
            ->with("MAIL FROM:<me@domain.com>\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("RCPT TO:<foo@bar>\r\n")
            ->andReturn(2);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(2)
            ->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with("DATA\r\n")
            ->andReturn(3);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(3)
            ->andReturn("451 No\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        try {
            $smtp->send($message);
            $this->fail('A bad response was given so exception is expected');
        } catch (Swift_TransportException $e) {
        }
    }
    public function testStopSendsQuitCommand()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('write')
            ->once()
            ->with("QUIT\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn("221 Bye\r\n");
        $buf->shouldReceive('terminate')
            ->once();
        $this->finishBuffer($buf);
        $this->assertFalse($smtp->isStarted());
        $smtp->start();
        $this->assertTrue($smtp->isStarted());
        $smtp->stop();
        $this->assertFalse($smtp->isStarted());
    }
    public function testBufferCanBeFetched()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $ref = $smtp->getBuffer();
        $this->assertEquals($buf, $ref);
    }
    public function testBufferCanBeWrittenToUsingExecuteCommand()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with("FOO\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with(1)
            ->andReturn("250 OK\r\n");
        $res = $smtp->executeCommand("FOO\r\n");
        $this->assertEquals("250 OK\r\n", $res);
    }
    public function testResponseCodesAreValidated()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with("FOO\r\n")
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with(1)
            ->andReturn("551 Not ok\r\n");
        try {
            $smtp->executeCommand("FOO\r\n", [250, 251]);
            $this->fail('A 250 or 251 response was needed but 551 was returned.');
        } catch (Swift_TransportException $e) {
        }
    }
    public function testFailedRecipientsCanBeCollectedByReference()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->zeroOrMoreTimes()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->zeroOrMoreTimes()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('getBcc')
                ->zeroOrMoreTimes()
                ->andReturn([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $message->shouldReceive('setBcc')
                ->atLeast()->once()
                ->with([]);
        $message->shouldReceive('setBcc')
                ->once()
                ->with(['zip@button' => 'Zip Button']);
        $message->shouldReceive('setBcc')
                ->once()
                ->with(['test@domain' => 'Test user']);
        $message->shouldReceive('setBcc')
                ->atLeast()->once()
                ->with([
                    'zip@button' => 'Zip Button',
                    'test@domain' => 'Test user',
                ]);
        $buf->shouldReceive('write')->once()->with("MAIL FROM:<me@domain.com>\r\n")->andReturn(1);
        $buf->shouldReceive('readLine')->once()->with(1)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("RCPT TO:<foo@bar>\r\n")->andReturn(2);
        $buf->shouldReceive('readLine')->once()->with(2)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("DATA\r\n")->andReturn(3);
        $buf->shouldReceive('readLine')->once()->with(3)->andReturn("354 OK\r\n");
        $buf->shouldReceive('write')->once()->with("\r\n.\r\n")->andReturn(4);
        $buf->shouldReceive('readLine')->once()->with(4)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("MAIL FROM:<me@domain.com>\r\n")->andReturn(5);
        $buf->shouldReceive('readLine')->once()->with(5)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("RCPT TO:<zip@button>\r\n")->andReturn(6);
        $buf->shouldReceive('readLine')->once()->with(6)->andReturn("500 Bad\r\n");
        $buf->shouldReceive('write')->once()->with("RSET\r\n")->andReturn(7);
        $buf->shouldReceive('readLine')->once()->with(7)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("MAIL FROM:<me@domain.com>\r\n")->andReturn(9);
        $buf->shouldReceive('readLine')->once()->with(9)->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')->once()->with("RCPT TO:<test@domain>\r\n")->andReturn(10);
        $buf->shouldReceive('readLine')->once()->with(10)->andReturn("500 Bad\r\n");
        $buf->shouldReceive('write')->once()->with("RSET\r\n")->andReturn(11);
        $buf->shouldReceive('readLine')->once()->with(11)->andReturn("250 OK\r\n");
        $this->finishBuffer($buf);
        $smtp->start();
        $this->assertEquals(1, $smtp->send($message, $failures));
        $this->assertEquals(['zip@button', 'test@domain'], $failures,
            '%s: Failures should be caught in an array'
            );
    }
    public function testSendingRegeneratesMessageId()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $message = $this->createMessage();
        $message->shouldReceive('getFrom')
                ->zeroOrMoreTimes()
                ->andReturn(['me@domain.com' => 'Me']);
        $message->shouldReceive('getTo')
                ->zeroOrMoreTimes()
                ->andReturn(['foo@bar' => null]);
        $message->shouldReceive('generateId')
                ->once();
        $this->finishBuffer($buf);
        $smtp->start();
        $smtp->send($message);
    }
    public function testPing()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^NOOP\r\n$~D')
            ->andReturn(1);
        $buf->shouldReceive('readLine')
            ->once()
            ->with(1)
            ->andReturn('250 OK'."\r\n");
        $this->finishBuffer($buf);
        $this->assertTrue($smtp->ping());
    }
    public function testPingOnDeadConnection()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $buf->shouldReceive('initialize')
            ->once();
        $buf->shouldReceive('readLine')
            ->once()
            ->with(0)
            ->andReturn("220 some.server.tld bleh\r\n");
        $buf->shouldReceive('write')
            ->once()
            ->with('~^NOOP\r\n$~D')
            ->andThrow('Swift_TransportException');
        $this->finishBuffer($buf);
        $smtp->start();
        $this->assertTrue($smtp->isStarted());
        $this->assertFalse($smtp->ping());
        $this->assertFalse($smtp->isStarted());
    }
    public function testSetLocalDomain()
    {
        $buf = $this->getBuffer();
        $smtp = $this->getTransport($buf);
        $smtp->setLocalDomain('example.com');
        $this->assertEquals('example.com', $smtp->getLocalDomain());
        $smtp->setLocalDomain('192.168.0.1');
        $this->assertEquals('[192.168.0.1]', $smtp->getLocalDomain());
        $smtp->setLocalDomain('[192.168.0.1]');
        $this->assertEquals('[192.168.0.1]', $smtp->getLocalDomain());
        $smtp->setLocalDomain('fd00::');
        $this->assertEquals('[IPv6:fd00::]', $smtp->getLocalDomain());
        $smtp->setLocalDomain('[IPv6:fd00::]');
        $this->assertEquals('[IPv6:fd00::]', $smtp->getLocalDomain());
    }
    protected function getBuffer()
    {
        return $this->getMockery('Swift_Transport_IoBuffer')->shouldIgnoreMissing();
    }
    protected function createMessage()
    {
        return $this->getMockery('Swift_Mime_SimpleMessage')->shouldIgnoreMissing();
    }
    protected function finishBuffer($buf)
    {
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with(0)
            ->andReturn('220 server.com foo'."\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with('~^(EH|HE)LO .*?\r\n$~D')
            ->andReturn($x = uniqid('', true));
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with($x)
            ->andReturn('250 ServerName'."\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with('~^MAIL FROM:<.*?>\r\n$~D')
            ->andReturn($x = uniqid('', true));
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with($x)
            ->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with('~^RCPT TO:<.*?>\r\n$~D')
            ->andReturn($x = uniqid('', true));
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with($x)
            ->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with("DATA\r\n")
            ->andReturn($x = uniqid('', true));
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with($x)
            ->andReturn("354 OK\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with("\r\n.\r\n")
            ->andReturn($x = uniqid('', true));
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with($x)
            ->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->with("RSET\r\n")
            ->andReturn($x = uniqid('', true));
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->with($x)
            ->andReturn("250 OK\r\n");
        $buf->shouldReceive('write')
            ->zeroOrMoreTimes()
            ->andReturn(false);
        $buf->shouldReceive('readLine')
            ->zeroOrMoreTimes()
            ->andReturn(false);
    }
}
