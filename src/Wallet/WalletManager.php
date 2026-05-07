<?php
namespace Ksf\Wallet;

/**
 * Framework-agnostic Wallet Manager
 * Extracted from TeraWallet's core business logic
 */
class WalletManager {
    
    /** @var WalletRepositoryInterface */
    private $repository;
    
    /** @var float */
    private $minBalance = 0;
    
    /**
     * @param WalletRepositoryInterface $repository
     */
    public function __construct(WalletRepositoryInterface $repository) {
        $this->repository = $repository;
    }
    
    /**
     * Get wallet balance for a user
     * 
     * @param int $userId
     * @param string $currency Optional currency code
     * @return float
     */
    public function getBalance(int $userId, string $currency = ''): float {
        return $this->repository->getBalance($userId, $currency);
    }
    
    /**
     * Credit wallet (add funds)
     * 
     * @param int $userId
     * @param float $amount
     * @param string $details Transaction description
     * @param array $metadata Additional transaction data
     * @return int Transaction ID
     * @throws \Exception On insufficient funds or locked account
     */
    public function credit(int $userId, float $amount, string $details = '', array $metadata = []): int {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Credit amount must be positive');
        }
        
        if ($this->repository->isLocked($userId)) {
            throw new \RuntimeException('Wallet is locked for user: ' . $userId);
        }
        
        $transaction = new Transaction([
            'user_id' => $userId,
            'type' => Transaction::TYPE_CREDIT,
            'amount' => $amount,
            'details' => $details,
            'currency' => $metadata['currency'] ?? '',
            'created_by' => $metadata['created_by'] ?? $userId,
            'metadata' => $metadata
        ]);
        
        return $this->repository->saveTransaction($transaction);
    }
    
    /**
     * Debit wallet (withdraw/spend funds)
     * 
     * @param int $userId
     * @param float $amount
     * @param string $details Transaction description
     * @param array $metadata Additional transaction data
     * @return int Transaction ID
     * @throws \Exception On insufficient funds or locked account
     */
    public function debit(int $userId, float $amount, string $details = '', array $metadata = []): int {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Debit amount must be positive');
        }
        
        if ($this->repository->isLocked($userId)) {
            throw new \RuntimeException('Wallet is locked for user: ' . $userId);
        }
        
        $balance = $this->repository->getBalance($userId, $metadata['currency'] ?? '');
        
        if ($balance < $amount) {
            throw new \RuntimeException('Insufficient wallet balance. Available: ' . $balance . ', Requested: ' . $amount);
        }
        
        $transaction = new Transaction([
            'user_id' => $userId,
            'type' => Transaction::TYPE_DEBIT,
            'amount' => $amount,
            'details' => $details,
            'currency' => $metadata['currency'] ?? '',
            'created_by' => $metadata['created_by'] ?? $userId,
            'metadata' => $metadata
        ]);
        
        return $this->repository->saveTransaction($transaction);
    }
    
    /**
     * Transfer funds between two users
     * 
     * @param int $fromUserId
     * @param int $toUserId
     * @param float $amount
     * @param string $details Transaction description
     * @param array $metadata Additional transaction data
     * @return array ['debit_id' => int, 'credit_id' => int]
     * @throws \Exception On any failure
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, string $details = '', array $metadata = []): array {
        if ($fromUserId === $toUserId) {
            throw new \InvalidArgumentException('Cannot transfer to same user');
        }
        
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be positive');
        }
        
        if ($this->repository->isLocked($fromUserId) || $this->repository->isLocked($toUserId)) {
            throw new \RuntimeException('One or both wallets are locked');
        }
        
        // Use database transaction for atomicity
        return $this->repository->executeInTransaction(function() use ($fromUserId, $toUserId, $amount, $details, $metadata) {
            $debitId = $this->debit($fromUserId, $amount, $details ?: "Transfer to user $toUserId", $metadata);
            $creditId = $this->credit($toUserId, $amount, $details ?: "Transfer from user $fromUserId", $metadata);
            
            return ['debit_id' => $debitId, 'credit_id' => $creditId];
        });
    }
    
    /**
     * Calculate cashback amount based on rules
     * 
     * @param float $amount Base amount
     * @param string $ruleType 'cart'|'product'|'category'
     * @param array $ruleConfig Rule configuration
     * @param float $maxCashback Optional maximum cashback
     * @return float Cashback amount
     */
    public static function calculateCashback(float $amount, string $ruleType, array $ruleConfig, float $maxCashback = 0): float {
        $cashback = 0;
        
        switch ($ruleType) {
            case 'percent':
                $cashback = $amount * ($ruleConfig['percentage'] / 100);
                break;
                
            case 'fixed':
                $cashback = $ruleConfig['amount'] ?? 0;
                break;
                
            default:
                return 0;
        }
        
        // Apply maximum cap if set
        if ($maxCashback > 0 && $cashback > $maxCashback) {
            $cashback = $maxCashback;
        }
        
        return round($cashback, 2);
    }
    
    /**
     * Get transaction history for a user
     * 
     * @param int $userId
     * @param array $filters Optional filters (date_from, date_to, type)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTransactionHistory(int $userId, array $filters = [], int $limit = 20, int $offset = 0): array {
        return $this->repository->getTransactions($userId, $filters, $limit, $offset);
    }
    
    /**
     * Lock a user's wallet
     * 
     * @param int $userId
     * @return bool
     */
    public function lockWallet(int $userId): bool {
        return $this->repository->setLocked($userId, true);
    }
    
    /**
     * Unlock a user's wallet
     * 
     * @param int $userId
     * @return bool
     */
    public function unlockWallet(int $userId): bool {
        return $this->repository->setLocked($userId, false);
    }
}
