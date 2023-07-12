<?php declare(strict_types=1);
namespace SebastianBergmann\Diff\Utils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
final class UnifiedDiffAssertTraitIntegrationTest extends TestCase
{
    use UnifiedDiffAssertTrait;
    private $filePatch;
    protected function setUp(): void
    {
        $this->filePatch = __DIR__ . '/../fixtures/out/patch.txt';
        $this->cleanUpTempFiles();
    }
    protected function tearDown(): void
    {
        $this->cleanUpTempFiles();
    }
    public function testValidPatches(string $fileFrom, string $fileTo): void
    {
        $command = \sprintf(
            'diff -u %s %s > %s',
            \escapeshellarg(\realpath($fileFrom)),
            \escapeshellarg(\realpath($fileTo)),
            \escapeshellarg($this->filePatch)
        );
        $p = new Process($command);
        $p->run();
        $exitCode = $p->getExitCode();
        if (0 === $exitCode) {
            $this->addToAssertionCount(1);
            return;
        }
        $this->assertSame(
            1, 
            $exitCode,
            \sprintf(
                "Command exec. was not successful:\n\"%s\"\nOutput:\n\"%s\"\nStdErr:\n\"%s\"\nExit code %d.\n",
                $command,
                $p->getOutput(),
                $p->getErrorOutput(),
                $p->getExitCode()
            )
        );
        $this->assertValidUnifiedDiffFormat(FileUtils::getFileContent($this->filePatch));
    }
    public function provideFilePairsCases(): array
    {
        $cases = [];
        $dir       = \realpath(__DIR__ . '/../fixtures/UnifiedDiffAssertTraitIntegrationTest');
        $dirLength = \strlen($dir);
        for ($i = 1;; ++$i) {
            $fromFile = \sprintf('%s/%d_a.txt', $dir, $i);
            $toFile   = \sprintf('%s/%d_b.txt', $dir, $i);
            if (!\file_exists($fromFile)) {
                break;
            }
            $this->assertFileExists($toFile);
            $cases[\sprintf("Diff file:\n\"%s\"\nvs.\n\"%s\"\n", \substr(\realpath($fromFile), $dirLength), \substr(\realpath($toFile), $dirLength))] = [$fromFile, $toFile];
        }
        $dir       = \realpath(__DIR__ . '/../../vendor');
        $dirLength = \strlen($dir);
        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS));
        $fromFile     = __FILE__;
        foreach ($fileIterator as $file) {
            if ('php' !== $file->getExtension()) {
                continue;
            }
            $toFile                                                                                                                                   = $file->getPathname();
            $cases[\sprintf("Diff file:\n\"%s\"\nvs.\n\"%s\"\n", \substr(\realpath($fromFile), $dirLength), \substr(\realpath($toFile), $dirLength))] = [$fromFile, $toFile];
            $fromFile                                                                                                                                 = $toFile;
        }
        return $cases;
    }
    private function cleanUpTempFiles(): void
    {
        @\unlink($this->filePatch);
    }
}
