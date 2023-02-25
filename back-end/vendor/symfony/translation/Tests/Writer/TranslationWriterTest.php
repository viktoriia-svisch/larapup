<?php
namespace Symfony\Component\Translation\Tests\Writer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Dumper\DumperInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
class TranslationWriterTest extends TestCase
{
    public function testWrite()
    {
        $dumper = $this->getMockBuilder('Symfony\Component\Translation\Dumper\DumperInterface')->getMock();
        $dumper
            ->expects($this->once())
            ->method('dump');
        $writer = new TranslationWriter();
        $writer->addDumper('test', $dumper);
        $writer->write(new MessageCatalogue('en'), 'test');
    }
    public function testDisableBackup()
    {
        $nonBackupDumper = new NonBackupDumper();
        $backupDumper = new BackupDumper();
        $writer = new TranslationWriter();
        $writer->addDumper('non_backup', $nonBackupDumper);
        $writer->addDumper('backup', $backupDumper);
        $writer->disableBackup();
        $this->assertFalse($backupDumper->backup, 'backup can be disabled if setBackup() method does exist');
    }
}
class NonBackupDumper implements DumperInterface
{
    public function dump(MessageCatalogue $messages, $options = [])
    {
    }
}
class BackupDumper implements DumperInterface
{
    public $backup = true;
    public function dump(MessageCatalogue $messages, $options = [])
    {
    }
    public function setBackup($backup)
    {
        $this->backup = $backup;
    }
}
