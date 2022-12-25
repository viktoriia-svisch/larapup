<?php
use PHPUnit\Framework\TestCase;
class BankAccountTest extends TestCase
{
    private $ba;
    protected function setUp(): void
    {
        $this->ba = new BankAccount;
    }
    public function testBalanceIsInitiallyZero(): void
    {
        $ba = new BankAccount;
        $balance = $ba->getBalance();
        $this->assertEquals(0, $balance);
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
