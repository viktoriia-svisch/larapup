<?php
class Swift_Signers_OpenDKIMSignerTest extends \SwiftMailerTestCase
{
    protected function setUp()
    {
        if (!extension_loaded('opendkim')) {
            $this->markTestSkipped(
                'Need OpenDKIM extension run these tests.'
             );
        }
    }
    public function testBasicSigningHeaderManipulation()
    {
    }
    public function testSigningDefaults()
    {
    }
    public function testSigning256()
    {
    }
    public function testSigningRelaxedRelaxed256()
    {
    }
    public function testSigningRelaxedSimple256()
    {
    }
    public function testSigningSimpleRelaxed256()
    {
    }
}
