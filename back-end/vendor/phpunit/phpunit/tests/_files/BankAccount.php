<?php
class BankAccountException extends RuntimeException
{
}
class BankAccount
{
    protected $balance = 0;
    public function getBalance()
    {
        return $this->balance;
    }
    public function depositMoney($balance)
    {
        $this->setBalance($this->getBalance() + $balance);
        return $this->getBalance();
    }
    public function withdrawMoney($balance)
    {
        $this->setBalance($this->getBalance() - $balance);
        return $this->getBalance();
    }
    protected function setBalance($balance): void
    {
        if ($balance >= 0) {
            $this->balance = $balance;
        } else {
            throw new BankAccountException;
        }
    }
}
