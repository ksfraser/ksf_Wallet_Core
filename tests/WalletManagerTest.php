<?php
namespace Ksfraser\Wallet\Tests;

use PHPUnit\Framework\TestCase;
use Ksf\Wallet\WalletManager;
use Ksf\Wallet\Transaction;
use Ksf\Wallet\WalletRepositoryInterface;

/**
 * Mock Repository for testing
 */
class MockWalletRepository implements WalletRepositoryInterface {
    private $balances = [];
    private $transactions = [];
    private $locked = [];
    private $nextId = 1;
    
    public function getBalance(int $userId, string $currency = ''): float {
        return $this->balances[$userId] ?? 0;
    }
    
    public function saveTransaction(Transaction $transaction): int {
        $id = $transaction->getId() ?? $this->nextId++;
        $transaction->setId($id);
        $this->transactions[$id] = $transaction;
        
        // Update balance
        $amount = $transaction->getAmount();
        if ($transaction->getType() === Transaction::TYPE_DEBIT) {
            $amount = -$amount;
        }
        $this->balances[$transaction->getUserId()] = ($this->balances[$transaction->getUserId()] ?? 0) + $amount;
        
        return $id;
    }
    
    public function getTransactions(int $userId, array $filters = [], int $limit = 20, int $offset = 0): array {
        return array_filter($this->transactions, function($t) use ($userId) {
            return $t->getUserId() === $userId;
        });
    }
    
    public function isLocked(int $userId): bool {
        return $this->locked[$userId] ?? false;
    }
    
    public function setLocked(int $userId, bool $locked): bool {
        $this->locked[$userId] = $locked;
        return true;
    }
    
    public function executeInTransaction(callable $callback) {
        return $callback();
    }
}

/**
 * WalletManager Test Suite
 */
class WalletManagerTest extends TestCase {
    
    private $walletManager;
    private $repository;
    
    protected function setUp(): void {
        $this->repository = new MockWalletRepository();
        $this->walletManager = new WalletManager($this->repository);
    }
    
    /**
     * Test crediting wallet
     */
    public function testCreditWallet(): void {
        $transactionId = $this->walletManager->credit(1, 100.50, 'Test credit');
        
        $this->assertIsInt($transactionId);
        $this->assertEquals(100.50, $this->walletManager->getBalance(1));
    }
    
    /**
     * Test debiting wallet
     */
    public function testDebitWallet(): void {
        $this->walletManager->credit(1, 100, 'Initial credit');
        $transactionId = $this->walletManager->debit(1, 30, 'Test debit');
        
        $this->assertIsInt($transactionId);
        $this->assertEquals(70, $this->walletManager->getBalance(1));
    }
    
    /**
     * Test insufficient funds exception
     */
    public function testDebitInsufficientFunds(): void {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insufficient wallet balance');
        
        $this->walletManager->debit(1, 50, 'Test debit');
    }
    
    /**
     * Test transfer between users
     */
    public function testTransferBetweenUsers(): void {
        $this->walletManager->credit(1, 100, 'Initial credit for user 1');
        
        $result = $this->walletManager->transfer(1, 2, 40, 'Payment');
        
        $this->assertArrayHasKey('debit_id', $result);
        $this->assertArrayHasKey('credit_id', $result);
        $this->assertEquals(60, $this->walletManager->getBalance(1));
        $this->assertEquals(40, $this->walletManager->getBalance(2));
    }
    
    /**
     * Test transfer to same user throws exception
     */
    public function testTransferToSameUserThrowsException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot transfer to same user');
        
        $this->walletManager->transfer(1, 1, 50, 'Self transfer');
    }
    
    /**
     * Test locked wallet cannot be credited
     */
    public function testLockedWalletCannotBeCredited(): void {
        $this->walletManager->lockWallet(1);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Wallet is locked');
        
        $this->walletManager->credit(1, 100, 'Test credit');
    }
    
    /**
     * Test locked wallet cannot be debited
     */
    public function testLockedWalletCannotBeDebited(): void {
        $this->walletManager->credit(1, 100, 'Initial');
        $this->walletManager->lockWallet(1);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Wallet is locked');
        
        $this->walletManager->debit(1, 50, 'Test debit');
    }
    
    /**
     * Test cashback calculation - percentage
     */
    public function testCalculateCashbackPercentage(): void {
        $cashback = WalletManager::calculateCashback(100, 'percent', ['percentage' => 10], 0);
        $this->assertEquals(10, $cashback);
    }
    
    /**
     * Test cashback calculation - percentage with max
     */
    public function testCalculateCashbackPercentageWithMax(): void {
        $cashback = WalletManager::calculateCashback(100, 'percent', ['percentage' => 20], 15);
        $this->assertEquals(15, $cashback); // Capped at max
    }
    
    /**
     * Test cashback calculation - fixed amount
     */
    public function testCalculateCashbackFixed(): void {
        $cashback = WalletManager::calculateCashback(100, 'fixed', ['amount' => 25], 0);
        $this->assertEquals(25, $cashback);
    }
    
    /**
     * Test negative amount throws exception
     */
    public function testCreditNegativeAmountThrowsException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Credit amount must be positive');
        
        $this->walletManager->credit(1, -50, 'Negative credit');
    }
}
