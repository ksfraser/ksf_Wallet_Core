<?php
namespace Ksf\Wallet;

/**
 * Interface for wallet data storage
 * Implement this for FA, WooCommerce, or any other backend
 */
interface WalletRepositoryInterface {
    
    /**
     * Get wallet balance for a user
     * 
     * @param int $userId
     * @param string $currency Optional currency filter
     * @return float
     */
    public function getBalance(int $userId, string $currency = ''): float;
    
    /**
     * Save a transaction (insert or update)
     * 
     * @param Transaction $transaction
     * @return int Transaction ID
     */
    public function saveTransaction(Transaction $transaction): int;
    
    /**
     * Get transactions for a user
     * 
     * @param int $userId
     * @param array $filters Optional filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTransactions(int $userId, array $filters = [], int $limit = 20, int $offset = 0): array;
    
    /**
     * Check if user's wallet is locked
     * 
     * @param int $userId
     * @return bool
     */
    public function isLocked(int $userId): bool;
    
    /**
     * Set wallet lock state
     * 
     * @param int $userId
     * @param bool $locked
     * @return bool
     */
    public function setLocked(int $userId, bool $locked): bool;
    
    /**
     * Execute a callable within a database transaction
     * 
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    public function executeInTransaction(callable $callback);
}
