<?php
namespace Illuminate\Database\Concerns;
use Closure;
use Exception;
use Throwable;
trait ManagesTransactions
{
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($currentAttempt = 1; $currentAttempt <= $attempts; $currentAttempt++) {
            $this->beginTransaction();
            try {
                return tap($callback($this), function () {
                    $this->commit();
                });
            }
            catch (Exception $e) {
                $this->handleTransactionException(
                    $e, $currentAttempt, $attempts
                );
            } catch (Throwable $e) {
                $this->rollBack();
                throw $e;
            }
        }
    }
    protected function handleTransactionException($e, $currentAttempt, $maxAttempts)
    {
        if ($this->causedByDeadlock($e) &&
            $this->transactions > 1) {
            $this->transactions--;
            throw $e;
        }
        $this->rollBack();
        if ($this->causedByDeadlock($e) &&
            $currentAttempt < $maxAttempts) {
            return;
        }
        throw $e;
    }
    public function beginTransaction()
    {
        $this->createTransaction();
        $this->transactions++;
        $this->fireConnectionEvent('beganTransaction');
    }
    protected function createTransaction()
    {
        if ($this->transactions == 0) {
            try {
                $this->getPdo()->beginTransaction();
            } catch (Exception $e) {
                $this->handleBeginTransactionException($e);
            }
        } elseif ($this->transactions >= 1 && $this->queryGrammar->supportsSavepoints()) {
            $this->createSavepoint();
        }
    }
    protected function createSavepoint()
    {
        $this->getPdo()->exec(
            $this->queryGrammar->compileSavepoint('trans'.($this->transactions + 1))
        );
    }
    protected function handleBeginTransactionException($e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->reconnect();
            $this->pdo->beginTransaction();
        } else {
            throw $e;
        }
    }
    public function commit()
    {
        if ($this->transactions == 1) {
            $this->getPdo()->commit();
        }
        $this->transactions = max(0, $this->transactions - 1);
        $this->fireConnectionEvent('committed');
    }
    public function rollBack($toLevel = null)
    {
        $toLevel = is_null($toLevel)
                    ? $this->transactions - 1
                    : $toLevel;
        if ($toLevel < 0 || $toLevel >= $this->transactions) {
            return;
        }
        try {
            $this->performRollBack($toLevel);
        } catch (Exception $e) {
            $this->handleRollBackException($e);
        }
        $this->transactions = $toLevel;
        $this->fireConnectionEvent('rollingBack');
    }
    protected function performRollBack($toLevel)
    {
        if ($toLevel == 0) {
            $this->getPdo()->rollBack();
        } elseif ($this->queryGrammar->supportsSavepoints()) {
            $this->getPdo()->exec(
                $this->queryGrammar->compileSavepointRollBack('trans'.($toLevel + 1))
            );
        }
    }
    protected function handleRollBackException($e)
    {
        if ($this->causedByLostConnection($e)) {
            $this->transactions = 0;
        }
        throw $e;
    }
    public function transactionLevel()
    {
        return $this->transactions;
    }
}
