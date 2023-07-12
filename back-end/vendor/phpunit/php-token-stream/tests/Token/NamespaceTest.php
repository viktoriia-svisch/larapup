<?php
use PHPUnit\Framework\TestCase;
class PHP_Token_NamespaceTest extends TestCase
{
    public function testGetName()
    {
        $tokenStream = new PHP_Token_Stream(
          TEST_FILES_PATH . 'classInNamespace.php'
        );
        foreach ($tokenStream as $token) {
            if ($token instanceof PHP_Token_NAMESPACE) {
                $this->assertSame('Foo\\Bar', $token->getName());
            }
        }
    }
    public function testGetStartLineWithUnscopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInNamespace.php');
        foreach ($tokenStream as $token) {
            if ($token instanceof PHP_Token_NAMESPACE) {
                $this->assertSame(2, $token->getLine());
            }
        }
    }
    public function testGetEndLineWithUnscopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInNamespace.php');
        foreach ($tokenStream as $token) {
            if ($token instanceof PHP_Token_NAMESPACE) {
                $this->assertSame(2, $token->getEndLine());
            }
        }
    }
    public function testGetStartLineWithScopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInScopedNamespace.php');
        foreach ($tokenStream as $token) {
            if ($token instanceof PHP_Token_NAMESPACE) {
                $this->assertSame(2, $token->getLine());
            }
        }
    }
    public function testGetEndLineWithScopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInScopedNamespace.php');
        foreach ($tokenStream as $token) {
            if ($token instanceof PHP_Token_NAMESPACE) {
                $this->assertSame(8, $token->getEndLine());
            }
        }
    }
}
