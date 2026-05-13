# Digital Wallet Core - Business Requirements

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Wallet_Core |
| **Module Type** | Business Logic / Framework-Agnostic Core |
| **Business Domain** | E-commerce / Finance - Digital Wallet Management |
| **Version** | 1.0.0 |
| **Last Updated** | 2026-05-13 |

---

## 1. Project Overview

### 1.1 Purpose

The Digital Wallet Core module (`ksf_Wallet_Core`) provides a framework-agnostic digital wallet system extracted from TeraWallet's core business logic. It enables e-commerce platforms to manage customer wallet balances, process transactions, handle fund transfers, and implement cashback programs.

### 1.2 Problem Statement

E-commerce platforms need sophisticated wallet functionality to increase customer engagement and loyalty:

1. **Balance Management**: Track customer wallet balances in real-time
2. **Credit Operations**: Add funds via deposits, refunds, cashback, admin credits
3. **Debit Operations**: Deduct funds for purchases, withdrawals, fees
4. **Fund Transfers**: Enable peer-to-peer transfers between users
5. **Balance Locking**: Prevent transactions on locked accounts (fraud prevention)
6. **Transaction History**: Complete audit trail of all wallet operations
7. **Cashback Programs**: Configurable cashback rules based on purchase amounts

### 1.3 Business Context

This module serves as the **business logic layer** in the KSFII architecture:

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   UI Adapter    │────▶│  Business Core  │◀────│  Platform DB    │
│ (ksf_FA_Wallet) │     │ (ksf_Wallet)    │     │   Adapter       │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

---

## 2. Scope

### 2.1 In Scope

#### Core Wallet Operations
- Balance inquiry (get current balance)
- Credit (add funds to wallet)
- Debit (remove funds from wallet)
- Transfer (move funds between wallets)
- Balance locking/unlocking

#### Transaction Management
- Transaction creation and storage
- Transaction history retrieval
- Filtering by date range
- Filtering by transaction type
- Pagination support

#### Cashback Calculation
- Percentage-based cashback
- Fixed amount cashback
- Maximum cashback cap
- Cart-level cashback
- Product-level cashback
- Category-level cashback

#### Account Security
- Wallet lock state management
- Insufficient funds validation
- Locked account blocking

### 2.2 Out of Scope

- Payment gateway integration
- Currency conversion
- Withdrawal to bank account
- Admin wallet management UI
- Email notifications
- API rate limiting
- Multi-currency support (single currency)
- Refund to original payment method
- Partial refunds

---

## 3. Features

### 3.1 Balance Operations

| Feature | Description |
|---------|-------------|
| `getBalance()` | Retrieve current wallet balance for user |
| Currency Filter | Optional currency filtering |
| Real-time Balance | Balance updated immediately after transaction |

### 3.2 Transaction Operations

| Feature | Description |
|---------|-------------|
| `credit()` | Add funds to wallet |
| `debit()` | Remove funds from wallet |
| `transfer()` | Move funds between wallets |
| Transaction Details | Store description and metadata |
| Audit Trail | Complete transaction history |

### 3.3 Transaction Types

| Type | Constant | Description |
|------|----------|-------------|
| Credit | TYPE_CREDIT | Funds added to wallet |
| Debit | TYPE_DEBIT | Funds removed from wallet |

### 3.4 Security Features

| Feature | Description |
|---------|-------------|
| `isLocked()` | Check if wallet is locked |
| `lockWallet()` | Lock wallet for security |
| `unlockWallet()` | Unlock wallet |
| Insufficient Funds | Throw exception on insufficient balance |
| Locked Account | Throw exception on locked account transactions |

### 3.5 Cashback System

| Feature | Description |
|---------|-------------|
| `calculateCashback()` | Calculate cashback based on rules |
| Percentage Cashback | Percentage of cart/order amount |
| Fixed Cashback | Fixed amount per transaction |
| Maximum Cap | Optional maximum cashback limit |

### 3.6 Transaction History

| Feature | Description |
|---------|-------------|
| `getTransactionHistory()` | Get paginated transaction list |
| Date Filtering | Filter by date_from and date_to |
| Type Filtering | Filter by transaction type |
| Pagination | Configurable limit and offset |

---

## 4. Integration Dependencies

### 4.1 Repository Interface

```php
interface WalletRepositoryInterface {
    public function getBalance(int $userId, string $currency = ''): float;
    public function saveTransaction(Transaction $transaction): int;
    public function getTransactions(int $userId, array $filters = [], int $limit = 20, int $offset = 0): array;
    public function isLocked(int $userId): bool;
    public function setLocked(int $userId, bool $locked): bool;
    public function executeInTransaction(callable $callback);
}
```

### 4.2 Platform Implementation

```php
// FrontAccounting implementation
class FA_WalletRepository implements WalletRepositoryInterface {
    private $db;              // ADV_Query instance
    private $transactionsTable;
    private $balanceTable;
}
```

### 4.3 Internal Module Dependencies

| Module | Purpose | Dependency Type |
|--------|---------|-----------------|
| `ksf_CRM` | Customer/user management | Optional |
| Platform Adapter | Database persistence | Required |

---

## 5. User Stories

### 5.1 Customer Wallet Top-Up

> **As a** customer  
> **I want to** add funds to my wallet  
> **So that I** can pay for orders using wallet balance

**Acceptance Criteria:**
- Credit increases wallet balance
- Transaction recorded with details
- Balance available immediately

### 5.2 Wallet Payment

> **As a** customer  
> **I want to** pay for my order using wallet funds  
> **So that I** can use store credits for purchases

**Acceptance Criteria:**
- Debit reduces wallet balance
- Insufficient funds shown if balance too low
- Transaction recorded

### 5.3 Peer Transfer

> **As a** customer  
> **I want to** transfer funds to another user  
> **So that I** can send money to friends/family

**Acceptance Criteria:**
- Transfer debits sender wallet
- Transfer credits receiver wallet
- Atomic transaction (both or neither)
- Cannot transfer to self

### 5.4 Cashback Earning

> **As a** customer  
> **I want to** earn cashback on purchases  
> **So that I** get rewarded for shopping

**Acceptance Criteria:**
- Cashback calculated based on rules
- Maximum cap applied when configured
- Cashback credited to wallet

---

## 6. Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Transaction processing | < 100ms | Performance profiling |
| Balance accuracy | 100% | Reconciliation |
| Atomic transfers | 100% | Transaction integrity |
| Concurrent access | Safe | Thread safety |

---

## 7. Glossary

| Term | Definition |
|------|------------|
| **Wallet** | Customer's store credit balance |
| **Credit** | Transaction that adds funds |
| **Debit** | Transaction that removes funds |
| **Transfer** | Movement of funds between wallets |
| **Cashback** | Reward credited based on purchase |
| **Transaction** | Record of wallet operation |
| **Locked Wallet** | Wallet temporarily blocked from transactions |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*