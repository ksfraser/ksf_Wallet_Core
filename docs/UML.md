# UML Documentation - KSF Wallet Core

## Class Diagram

```
+---------------------+          +---------------------+
|   WalletManager     |          |   Transaction       |
+---------------------+          +---------------------+
| -repository: Wallet |          | -id: int|null      |
| RepositoryInterface |          | -userId: int        |
| -minBalance: float  |          | -type: string       |
+---------------------+          | -amount: float      |
| +getBalance()       |          | -details: string    |
| +credit()           |          | -currency: string   |
| +debit()            |          | -createdBy: int     |
| +transfer()         |          | -date: string       |
| +calculateCashback()|          | -metadata: array    |
| +getTransactionHist()|          +---------------------+
| +lockWallet()       |          | +getId()            |
| +unlockWallet()     |          | +setId()            |
+---------------------+          | +getUserId()         |
          |                      | +getType()           |
          | uses                 | +getAmount()         |
          v                      | +getDetails()        |
+---------------------+          | +getCurrency()       |
| WalletRepository    |          | +getCreatedBy()      |
| Interface           |          | +getDate()           |
+---------------------+          | +getMetadata()       |
| +getBalance()       |          | +toArray()           |
| +saveTransaction()  |          +---------------------+
| +getTransactions()  |                   ^
| +isLocked()         |                   |
| +setLocked()        |                   |
| +executeInTransaction()|                |
+---------------------+          +---------------------+
          ^                      |   Transaction      |
          |                      |   (extends)        |
          |                      +---------------------+
+---------------------+          | -transaction_id    |
|   FA_Wallet        |          | -order_id          |
|   Repository        |          | -for: string       |
+---------------------+          +---------------------+
| +getBalance()       |
| +saveTransaction()  |
| +getTransactions()  |
| +isLocked()         |
| +setLocked()        |
| +executeInTransaction()|
+---------------------+
```

## Sequence Diagram: Credit Transaction

```
User -> WalletManager: credit(userId, amount, details)
WalletManager -> WalletManager: validate amount > 0
WalletManager -> WalletRepository: isLocked(userId)
WalletRepository --> WalletManager: false
WalletManager -> WalletRepository: executeInTransaction(callback)
WalletRepository -> WalletRepository: BEGIN TRANSACTION
WalletRepository -> WalletRepository: insert into fa_wallet_transactions
WalletRepository -> WalletRepository: COMMIT
WalletRepository --> WalletManager: transaction_id
WalletManager --> User: transaction_id
```

## Sequence Diagram: Transfer Between Users

```
User -> WalletManager: transfer(fromUserId, toUserId, amount)
WalletManager -> WalletManager: validate from != to
WalletManager -> WalletRepository: isLocked(fromUserId)
WalletRepository --> WalletManager: false
WalletManager -> WalletRepository: isLocked(toUserId)
WalletRepository --> WalletManager: false
WalletManager -> WalletRepository: executeInTransaction(callback)
WalletRepository -> WalletRepository: BEGIN TRANSACTION
WalletRepository -> WalletRepository: GET_LOCK(min_user, 5)
WalletRepository -> WalletRepository: GET_LOCK(max_user, 5)
WalletRepository -> WalletRepository: check balance >= amount
WalletRepository -> WalletRepository: insert DEBIT for fromUser
WalletRepository -> WalletRepository: insert CREDIT for toUser
WalletRepository -> WalletRepository: RELEASE_LOCK(max_user)
WalletRepository -> WalletRepository: RELEASE_LOCK(min_user)
WalletRepository -> WalletRepository: COMMIT
WalletRepository --> WalletManager: {debit_id, credit_id}
WalletManager --> User: {debit_id, credit_id}
```

## State Diagram: Transaction Lifecycle

```
[CREATED] --> [PENDING] : saveTransaction()
[PENDING] --> [COMPLETED] : COMMIT
[PENDING] --> [FAILED] : ROLLBACK
[COMPLETED] --> [DELETED] : soft delete (deleted=1)
[FAILED] --> [CREATED] : retry
```

## Database Schema (FA Tables)

```
fa_wallet_transactions
----------------------
- id (INT, PK, AUTO_INCREMENT)
- user_id (INT, NOT NULL)
- type (ENUM('credit','debit'), NOT NULL)
- amount (DECIMAL(10,2), NOT NULL)
- original_amount (DECIMAL(10,2))
- original_currency (VARCHAR(3))
- original_rate (DECIMAL(10,4), DEFAULT 1.0)
- mode (TINYINT, DEFAULT 0)  -- 0=single_base, 1=per_currency
- currency (VARCHAR(3), NOT NULL)
- details (TEXT)
- date (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- created_by (INT, NOT NULL)
- deleted (TINYINT, DEFAULT 0)

fa_wallet_balances (cache)
---------------------------
- user_id (INT, PK)
- balance (DECIMAL(10,2), DEFAULT 0)
- currency (VARCHAR(3), DEFAULT '')
- is_locked (TINYINT, DEFAULT 0)
- last_updated (DATETIME)
```

## Activity Diagram: Calculate Cashback

```
[Start] --> [Init cashback settings]
[Init cashback settings] --> [Check if cashback enabled]
[Check if cashback enabled] -->|No| [Return 0]
[Check if cashback enabled] -->|Yes| [Check rule type]
[Check rule type] -->|Product| [Get product cashback]
[Check rule type] -->|Category| [Get category cashback]
[Check rule type] -->|Cart| [Calculate cart cashback]
[Get product cashback] --> [Apply max cashback limit]
[Get category cashback] --> [Apply max cashback limit]
[Calculate cart cashback] --> [Apply max cashback limit]
[Apply max cashback limit] --> [Return cashback amount]
```
