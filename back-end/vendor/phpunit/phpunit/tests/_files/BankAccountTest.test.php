<?php
use PHPUnit\Framework\TestCase;
class BankAccountWithCustomExtensionTest extends TestCase
{
    protected $ba;
    protected function setUp(): void
    {
        $this->ba = new BankAccount;
    }
    public function testBalanceIsInitiallyZero(): void
    {
        $this->assertEquals(0, $this->ba->getBalance());
    }
    public function testBalanceCannotBecomeNegative(): void
    {
        try {
            $this->ba->withdrawMoney(1);
        } catch (BankAccountException $e) {
            $this->assertEquals(0, $this->ba->getBalance());
            return;
        }
        $this->fail();
    }
    public function testBalanceCannotBecomeNegative2(): void
    {
        try {
            $this->ba->depositMoney(-1);
        } catch (BankAccountException $e) {
            $this->assertEquals(0, $this->ba->getBalance());
            return;
        }
        $this->fail();
    }
}
