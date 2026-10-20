# Digital Wallet Core - Architecture

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Wallet_Core |
| **Module Type** | Business Logic / Framework-Agnostic Core |
| **Version** | 1.0.0 |

---

## 1. Technical Architecture

### 1.1 Architecture Pattern

```
┌──────────────────────────────────────────────────────────────────────┐
│                       ksf_Wallet_Core                                │
│                    (Business Logic Layer)                            │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐      │
│  │  WalletManager   │  │   Transaction    │  │    FA_Wallet     │      │
│  │                  │  │    Entity        │  │   Repository     │      │
│  │ - credit()       │  │                  │  │   (Platform)     │      │
│  │ - debit()        │  │ - id             │  │                  │      │
│  │ - transfer()     │  │ - userId         │  │ - getBalance()   │      │
│  │ - getBalance()   │  │ - type           │  │ - saveTransaction│      │
│  │ - lockWallet()   │  │ - amount         │  │ - isLocked()     │      │
│  │ - calculateCashback │ - details     │  │ - getTransactions│      │
│  └────────┬─────────┘  │ - currency      │  └────────┬─────────┘      │
│           │            │ - date          │           │                │
│           │            │ - metadata      │           │                │
│           │            └──────────────────┘           │                │
│           │                                          │                │
│           └──────────────────┐                       │                │
│                              │ uses                   │                │
│                              ▼                        ▼                │
│  ┌──────────────────────────────────────────────────────────────┐    │
│  │              WalletRepositoryInterface                        │    │
│  │  Responsibilities:                                            │    │
│  │  - Data persistence abstraction                               │    │
│  │  - Platform-specific implementations                          │    │
│  │  - Balance calculation                                         │    │
│  │  - Transaction CRUD operations                                 │    │
│  └──────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────┘
```

### 1.2 Directory Structure

```
ksf_Wallet_Core/
├── src/Ksfraser/Wallet/
│   ├── WalletManager.php              # Core wallet operations
│   ├── Transaction.php               # Transaction entity
│   └── WalletRepositoryInterface.php # Repository interface
├── FA_WalletRepository.php           # FrontAccounting implementation
├── tests/
│   ├── Unit/
│   │   ├── WalletManagerTest.php
│   │   └── TransactionTest.php
│   └── fixtures/
├── docs/
├── ProjectDcs/
└── composer.json
```

### 1.3 Namespace Structure

```php
namespace Ksfraser\Wallet;           # Core classes
```

---

## 2. Class Diagrams

### 2.1 WalletManager

```
┌─────────────────────────────────────────────────────────────────┐
│                       WalletManager                              │
├─────────────────────────────────────────────────────────────────┤
│ - repository: WalletRepositoryInterface                         │
│ - minBalance: float                                              │
├─────────────────────────────────────────────────────────────────┤
│ + __construct(WalletRepositoryInterface repository)              │
│ + getBalance(int userId, string currency): float                 │
│ + credit(int userId, float amount, string details,              │
│           array metadata): int                                   │
│ + debit(int userId, float amount, string details,               │
│          array metadata): int                                     │
│ + transfer(int fromUserId, int toUserId, float amount,          │
│            string details, array metadata): array                │
│ + calculateCashback(float amount, string ruleType,             │
│                     array ruleConfig, float maxCashback): float  │
│ + getTransactionHistory(int userId, array filters,              │
│                         int limit, int offset): array            │
│ + lockWallet(int userId): bool                                    │
│ + unlockWallet(int userId): bool                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Transaction Entity

```
┌─────────────────────────────────────────────────────────────────┐
│                        Transaction                               │
├─────────────────────────────────────────────────────────────────┤
│ - id: ?int                                                       │
│ - userId: int                                                    │
│ - type: string                                                   │
│ - amount: float                                                  │
│ - details: string                                                │
│ - currency: string                                               │
│ - createdBy: int                                                 │
│ - date: string                                                   │
│ - metadata: array                                                │
├─────────────────────────────────────────────────────────────────┤
│ + TYPE_CREDIT = 'credit'                                        │
│ + TYPE_DEBIT = 'debit'                                          │
├─────────────────────────────────────────────────────────────────┤
│ + __construct(array data)                                        │
│ + getId(): ?int                                                  │
│ + setId(int id): void                                            │
│ + getUserId(): int                                               │
│ + getType(): string                                              │
│ + getAmount(): float                                             │
│ + getDetails(): string                                           │
│ + getCurrency(): string                                           │
│ + getCreatedBy(): int                                             │
│ + getDate(): string                                               │
│ + getMetadata(): array                                            │
│ + toArray(): array                                                │
└─────────────────────────────────────────────────────────────────┘
```

### 2.3 WalletRepositoryInterface

```
┌─────────────────────────────────────────────────────────────────┐
│              <<interface>> WalletRepositoryInterface           │
├─────────────────────────────────────────────────────────────────┤
│ + getBalance(int userId, string currency): float               │
│ + saveTransaction(Transaction transaction): int               │
│ + getTransactions(int userId, array filters,                  │
│                   int limit, int offset): array                 │
│ + isLocked(int userId): bool                                   │
│ + setLocked(int userId, bool locked): bool                     │
│ + executeInTransaction(callable callback): mixed              │
└─────────────────────────────────────────────────────────────────┘
                              ▲
                              │ implements
      ┌───────────────────────┴───────────────────────┐
      │                                               │
┌─────┴────────────────┐                    ┌────────┴────────┐
│ FA_WalletRepository  │                    │WooCommerce_    │
│                      │                    │WalletRepository│
├──────────────────────┤                    ├────────────────┤
│ - db                  │                    │ - db           │
│ - transactionsTable   │                    │ - tableName    │
│ - balanceTable       │                    │               │
├──────────────────────┤                    ├────────────────┤
│ + getBalance()       │                    │ + getBalance() │
│ + saveTransaction()  │                    │ + saveTransaction()
│ + isLocked()         │                    │ + isLocked()   │
└──────────────────────┘                    └────────────────┘
```

---

## 3. Data Flow

### 3.1 Credit Transaction Flow

```
┌──────────────┐    ┌──────────────────┐    ┌──────────────────┐
│   Caller    │    │   WalletManager │    │   Repository     │
└──────┬───────┘    └────────┬─────────┘    └───────┬──────────┘
       │                     │                        │
       │  credit(userId, amount, details)           │
       │───────────────────▶│                        │
       │                     │                        │
       │                     │  [Validate amount > 0] │
       │                     │                        │
       │                     │  isLocked(userId)       │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  [false]              │
       │                     │◀──────────────────────│
       │                     │                        │
       │                     │  new Transaction()     │
       │                     │                        │
       │                     │  saveTransaction()    │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  [transactionId]      │
       │                     │◀──────────────────────│
       │                     │                        │
       │  [transactionId]     │                        │
       │◀───────────────────│                        │
       │                     │                        │
```

### 3.2 Transfer Transaction Flow

```
┌──────────────┐    ┌──────────────────┐    ┌──────────────────┐
│   Caller    │    │   WalletManager │    │   Repository     │
└──────┬───────┘    └────────┬─────────┘    └───────┬──────────┘
       │                     │                        │
       │  transfer(from, to, amount)                │
       │───────────────────▶│                        │
       │                     │                        │
       │                     │  [Validate: not same user, amount > 0] │
       │                     │                        │
       │                     │  executeInTransaction()│
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  debit(from, ...)      │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  credit(to, ...)        │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  [debit_id, credit_id]│
       │                     │◀──────────────────────│
       │                     │                        │
       │  [debit_id, credit_id]                       │
       │◀───────────────────│                        │
       │                     │                        │
```

---

## 4. Transaction Data Structure

### 4.1 Transaction Entity

```php
class Transaction {
    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';
    
    private ?int $id;
    private int $userId;
    private string $type;
    private float $amount;
    private string $details;
    private string $currency;
    private int $createdBy;
    private string $date;
    private array $metadata;
}
```

### 4.2 Database Table Structure (FA Implementation)

```sql
CREATE TABLE ksf_wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    details TEXT,
    currency VARCHAR(3) DEFAULT '',
    created_by INT NOT NULL,
    date DATETIME NOT NULL,
    metadata JSON,
    deleted TINYINT(1) DEFAULT 0,
    INDEX idx_user_date (user_id, date),
    INDEX idx_type (type)
);

CREATE TABLE ksf_wallet_balances (
    user_id INT PRIMARY KEY,
    is_locked BOOLEAN DEFAULT FALSE,
    updated_at DATETIME
);
```

---

## 5. Error Handling

### 5.1 Exception Scenarios

| Scenario | Exception Type | Message |
|----------|----------------|---------|
| Negative credit amount | InvalidArgumentException | Credit amount must be positive |
| Negative debit amount | InvalidArgumentException | Debit amount must be positive |
| Negative transfer amount | InvalidArgumentException | Transfer amount must be positive |
| Transfer to self | InvalidArgumentException | Cannot transfer to same user |
| Locked wallet (credit) | RuntimeException | Wallet is locked for user |
| Locked wallet (debit) | RuntimeException | Wallet is locked for user |
| Insufficient balance | RuntimeException | Insufficient wallet balance |
| Locked wallet (transfer) | RuntimeException | One or both wallets are locked |

### 5.2 Error Flow

```
Credit(userId, -100)
        │
        ▼
    ┌───────────┐
    │ amount <= 0│
    └─────┬─────┘
          │
          │ Yes
          ▼
    ┌─────────────────────────────────┐
    │ throw InvalidArgumentException  │
    │ "Credit amount must be positive"│
    └─────────────────────────────────┘
```

---

## 6. Cashback Calculation

### 6.1 Cashback Rule Types

```php
// Percentage-based
WalletManager::calculateCashback(100.00, 'percent', ['percentage' => 5]);
// Returns: 5.00 (5% of 100)

// Fixed amount
WalletManager::calculateCashback(100.00, 'fixed', ['amount' => 10.00]);
// Returns: 10.00

// With maximum cap
WalletManager::calculateCashback(500.00, 'percent', ['percentage' => 10], 25.00);
// Returns: 25.00 (capped)
```

### 6.2 Cashback Algorithm

```
┌──────────────────────────────────────┐
│ Calculate Cashback                   │
├──────────────────────────────────────┤
│ Input: amount, ruleType, ruleConfig, │
│        maxCashback                   │
└──────────────┬───────────────────────┘
               │
               ▼
        ┌─────────────┐
        │ ruleType   │
        │ == 'percent'│
        └─────┬───────┘
              │
       ┌─────┴─────┐
       │ Yes      │ No
       ▼          ▼
   ┌─────────┐ ┌─────────────┐
   │ amount  │ │ ruleType   │
   │ * (pct/ │ │ == 'fixed' │
   │   100) │ └──────┬──────┘
   └────┬────┘        │
        │        ┌────┴────┐
        │        │ Yes    │ No
        │        ▼        ▼
        │   ┌─────────┐ ┌──────┐
        │   │ config │ │ ret  │
        │   │ ['amt']│ │ 0    │
        │   └────┬────┘ └──┬───┘
        │        │         │
        └────┬───┴────┬────┘
             │        │
             ▼        ▼
        ┌─────────────────┐
        │  if maxCashback │
        │  > 0 AND result │
        │  > maxCashback  │
        └────┬────────────┘
             │
             │ Yes
             ▼
        ┌─────────────┐
        │ result =    │
        │ maxCashback │
        └──────┬───────┘
              │
              ▼
        ┌─────────────┐
        │ Return      │
        │ result      │
        └─────────────┘
```

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*