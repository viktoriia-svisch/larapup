<?php
namespace Symfony\Component\VarDumper\Tests\Command\Descriptor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;
use Symfony\Component\VarDumper\Dumper\CliDumper;
class CliDescriptorTest extends TestCase
{
    private static $timezone;
    public static function setUpBeforeClass()
    {
        self::$timezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
    }
    public static function tearDownAfterClass()
    {
        date_default_timezone_set(self::$timezone);
    }
    public function testDescribe(array $context, string $expectedOutput)
    {
        $output = new BufferedOutput();
        $descriptor = new CliDescriptor(new CliDumper(function ($s) {
            return $s;
        }));
        $descriptor->describe($output, new Data([[123]]), $context + ['timestamp' => 1544804268.3668], 1);
        $this->assertStringMatchesFormat(trim($expectedOutput), str_replace(PHP_EOL, "\n", trim($output->fetch())));
    }
    public function provideContext()
    {
        yield 'source' => [
            [
                'source' => [
                    'name' => 'CliDescriptorTest.php',
                    'line' => 30,
                    'file' => '/Users/ogi/symfony/src/Symfony/Component/VarDumper/Tests/Command/Descriptor/CliDescriptorTest.php',
                ],
            ],
            <<<TXT
Received from client #1
-----------------------
 -------- --------------------------------------------------------------------------------------------------- 
  date     Fri, 14 Dec 2018 16:17:48 +0000                                                                    
  source   CliDescriptorTest.php on line 30                                                                   
  file     /Users/ogi/symfony/src/Symfony/Component/VarDumper/Tests/Command/Descriptor/CliDescriptorTest.php  
 -------- ---------------------------------------------------------------------------------------------------
TXT
        ];
        yield 'source full' => [
            [
                'source' => [
                    'name' => 'CliDescriptorTest.php',
                    'line' => 30,
                    'file_relative' => 'src/Symfony/Component/VarDumper/Tests/Command/Descriptor/CliDescriptorTest.php',
                    'file' => '/Users/ogi/symfony/src/Symfony/Component/VarDumper/Tests/Command/Descriptor/CliDescriptorTest.php',
                    'file_link' => 'phpstorm:
                ],
            ],
            <<<TXT
Received from client #1
-----------------------
 -------- -------------------------------------------------------------------------------- 
  date     Fri, 14 Dec 2018 16:17:48 +0000                                                 
  source   CliDescriptorTest.php on line 30                                                
  file     src/Symfony/Component/VarDumper/Tests/Command/Descriptor/CliDescriptorTest.php  
 -------- -------------------------------------------------------------------------------- 
Open source in your IDE/browser:
phpstorm:
TXT
        ];
        yield 'cli' => [
            [
                'cli' => [
                    'identifier' => 'd8bece1c',
                    'command_line' => 'bin/phpunit',
                ],
            ],
            <<<TXT
$ bin/phpunit
-------------
 ------ --------------------------------- 
  date   Fri, 14 Dec 2018 16:17:48 +0000  
 ------ ---------------------------------
TXT
        ];
        yield 'request' => [
            [
                'request' => [
                    'identifier' => 'd8bece1c',
                    'controller' => new Data([['FooController.php']]),
                    'method' => 'GET',
                    'uri' => 'http:
                ],
            ],
            <<<TXT
GET http:
------------------------
 ------------ --------------------------------- 
  date         Fri, 14 Dec 2018 16:17:48 +0000  
  controller   "FooController.php"              
 ------------ --------------------------------- 
TXT
        ];
    }
}
