<?php
namespace Ksf\Wallet;

/**
 * FrontAccounting implementation of WalletRepositoryInterface
 */
class FA_WalletRepository implements WalletRepositoryInterface {
    
    /** @var \ADV_Query */
    private $db;
    
    /** @var string */
    private $transactionsTable = 'fa_wallet_transactions';
    
    /** @var string */
    private $balanceTable = 'fa_wallet_balances';
    
    /**
     * @param \ADV_Query $db FA database connection wrapper
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getBalance(int $userId, string $currency = ''): float {
        $sql = "SELECT COALESCE(SUM(CASE WHEN type='credit' THEN amount ELSE -amount END), 0) as balance 
                FROM {$this->transactionsTable} 
                WHERE user_id=" . (int)$userId . " AND deleted=0";
        
        if ($currency !== '') {
            $sql .= " AND currency=" . $this->db->escape($currency);
        }
        
        $result = $this->db->query($sql);
        $row = $this->db->fetch_assoc($result);
        
        return (float)($row['balance'] ?? 0);
    }
    
    public function saveTransaction(Transaction $transaction): int {
        if ($transaction->getId()) {
            // Update existing
            $sql = "UPDATE {$this->transactionsTable} SET 
                    type=" . $this->db->escape($transaction->getType()) . ",
                    amount=" . (float)$transaction->getAmount() . ",
                    details=" . $this->db->escape($transaction->getDetails()) . ",
                    currency=" . $this->db->escape($transaction->getCurrency()) . ",
                    metadata=" . $this->db->escape(json_encode($transaction->getMetadata())) . "
                    WHERE id=" . (int)$transaction->getId();
            $this->db->query($sql);
            return $transaction->getId();
        } else {
            // Insert new
            $sql = "INSERT INTO {$this->transactionsTable} 
                    (user_id, type, amount, details, currency, created_by, date, metadata)
                    VALUES (
                    " . (int)$transaction->getUserId() . ",
                    " . $this->db->escape($transaction->getType()) . ",
                    " . (float)$transaction->getAmount() . ",
                    " . $this->db->escape($transaction->getDetails()) . ",
                    " . $this->db->escape($transaction->getCurrency()) . ",
                    " . (int)$transaction->getCreatedBy() . ",
                    " . $this->db->escape($transaction->getDate()) . ",
                    " . $this->db->escape(json_encode($transaction->getMetadata())) . "
                    )";
            $this->db->query($sql);
            return $this->db->insert_id();
        }
    }
    
    public function getTransactions(int $userId, array $filters = [], int $limit = 20, int $offset = 0): array {
        $sql = "SELECT * FROM {$this->transactionsTable} 
                WHERE user_id=" . (int)$userId . " AND deleted=0";
        
        if (!empty($filters['type'])) {
            $sql .= " AND type=" . $this->db->escape($filters['type']);
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND date >= " . $this->db->escape($filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND date <= " . $this->db->escape($filters['date_to']);
        }
        
        $sql .= " ORDER BY date DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $result = $this->db->query($sql);
        $transactions = [];
        while ($row = $this->db->fetch_assoc($result)) {
            $transactions[] = $row;
        }
        return $transactions;
    }
    
    public function isLocked(int $userId): bool {
        $sql = "SELECT is_locked FROM {$this->balanceTable} WHERE user_id=" . (int)$userId;
        $result = $this->db->query($sql);
        $row = $this->db->fetch_assoc($result);
        return (bool)($row['is_locked'] ?? false);
    }
    
    public function setLocked(int $userId, bool $locked): bool {
        $sql = "UPDATE {$this->balanceTable} SET is_locked=" . (int)$locked . " WHERE user_id=" . (int)$userId;
        return (bool)$this->db->query($sql);
    }
    
    public function executeInTransaction(callable $callback) {
        $this->db->begin_transaction();
        try {
            $result = $callback();
            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
