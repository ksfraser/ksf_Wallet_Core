# Digital Wallet Core - Functional Requirements

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Wallet_Core |
| **Requirement Type** | Functional Requirements |
| **Version** | 1.0.0 |

---

## 1. Requirements Overview

This document defines the functional requirements for the Digital Wallet Core module.

---

## 2. Requirements Specification

### 2.1 Balance Operations

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-001 | The system SHALL retrieve wallet balance for a user | MUST | Balance |
| WALLET-FUNC-002 | The system SHALL support currency filtering | MAY | Balance |
| WALLET-FUNC-003 | The system SHALL return 0 for non-existent wallets | MUST | Balance |
| WALLET-FUNC-004 | The system SHALL calculate balance from transactions | MUST | Balance |

### 2.2 Credit Operations

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-010 | The system SHALL credit funds to wallet | MUST | Credit |
| WALLET-FUNC-011 | The system SHALL validate positive amount | MUST | Credit |
| WALLET-FUNC-012 | The system SHALL reject credit to locked wallet | MUST | Credit |
| WALLET-FUNC-013 | The system SHALL create transaction record | MUST | Credit |
| WALLET-FUNC-014 | The system SHALL store transaction details | MUST | Credit |
| WALLET-FUNC-015 | The system SHALL store transaction metadata | MAY | Credit |
| WALLET-FUNC-016 | The system SHALL return transaction ID | MUST | Credit |

### 2.3 Debit Operations

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-020 | The system SHALL debit funds from wallet | MUST | Debit |
| WALLET-FUNC-021 | The system SHALL validate positive amount | MUST | Debit |
| WALLET-FUNC-022 | The system SHALL validate sufficient balance | MUST | Debit |
| WALLET-FUNC-023 | The system SHALL reject debit from locked wallet | MUST | Debit |
| WALLET-FUNC-024 | The system SHALL create transaction record | MUST | Debit |
| WALLET-FUNC-025 | The system SHALL return transaction ID | MUST | Debit |

### 2.4 Transfer Operations

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-030 | The system SHALL transfer funds between wallets | MUST | Transfer |
| WALLET-FUNC-031 | The system SHALL prevent self-transfer | MUST | Transfer |
| WALLET-FUNC-032 | The system SHALL validate positive amount | MUST | Transfer |
| WALLET-FUNC-033 | The system SHALL validate sufficient balance | MUST | Transfer |
| WALLET-FUNC-034 | The system SHALL use atomic transaction | MUST | Transfer |
| WALLET-FUNC-035 | The system SHALL reject if either wallet locked | MUST | Transfer |
| WALLET-FUNC-036 | The system SHALL return debit and credit IDs | MUST | Transfer |

### 2.5 Wallet Security

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-040 | The system SHALL check wallet lock state | MUST | Security |
| WALLET-FUNC-041 | The system SHALL lock wallet | MUST | Security |
| WALLET-FUNC-042 | The system SHALL unlock wallet | MUST | Security |
| WALLET-FUNC-043 | The system SHALL block operations on locked wallets | MUST | Security |
| WALLET-FUNC-044 | The system SHALL support wallet initialization on first use | MUST | Security |

### 2.6 Transaction History

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-050 | The system SHALL retrieve transaction history | MUST | History |
| WALLET-FUNC-051 | The system SHALL filter by date range | MAY | History |
| WALLET-FUNC-052 | The system SHALL filter by transaction type | MAY | History |
| WALLET-FUNC-053 | The system SHALL support pagination | MUST | History |
| WALLET-FUNC-054 | The system SHALL order by date descending | MUST | History |
| WALLET-FUNC-055 | The system SHALL exclude deleted transactions | MUST | History |

### 2.7 Cashback Calculation

| Req ID | Requirement | Priority | Category |
|--------|-------------|----------|----------|
| WALLET-FUNC-060 | The system SHALL calculate percentage cashback | MUST | Cashback |
| WALLET-FUNC-061 | The system SHALL calculate fixed cashback | MUST | Cashback |
| WALLET-FUNC-062 | The system SHALL apply maximum cashback cap | MAY | Cashback |
| WALLET-FUNC-063 | The system SHALL round cashback to 2 decimals | MUST | Cashback |
| WALLET-FUNC-064 | The system SHALL return 0 for invalid rule type | MUST | Cashback |

---

## 3. Input Specifications

### 3.1 Credit Input

```php
$transactionId = $wallet->credit(
    int $userId,           // Required: user ID
    float $amount,         // Required: positive amount
    string $details,       // Optional: transaction description
    array $metadata         // Optional: additional data
);
```

### 3.2 Debit Input

```php
$transactionId = $wallet->debit(
    int $userId,           // Required: user ID
    float $amount,         // Required: positive amount
    string $details,       // Optional: transaction description
    array $metadata         // Optional: additional data
);
```

### 3.3 Transfer Input

```php
$result = $wallet->transfer(
    int $fromUserId,       // Required: sender ID
    int $toUserId,         // Required: receiver ID
    float $amount,         // Required: positive amount
    string $details,       // Optional: transaction description
    array $metadata         // Optional: additional data
);
// Returns: ['debit_id' => int, 'credit_id' => int]
```

### 3.4 Cashback Input

```php
$cashback = WalletManager::calculateCashback(
    float $amount,         // Required: base amount
    string $ruleType,      // Required: 'percent' or 'fixed'
    array $ruleConfig,      // Required: rule configuration
    float $maxCashback     // Optional: maximum cap
);
```

---

## 4. Validation Rules

### 4.1 Amount Validation

| Rule | Condition | Error |
|------|-----------|-------|
| Credit amount | amount > 0 | InvalidArgumentException |
| Debit amount | amount > 0 | InvalidArgumentException |
| Transfer amount | amount > 0 | InvalidArgumentException |
| Debit sufficient balance | balance >= amount | RuntimeException |
| Transfer self-check | fromUserId != toUserId | InvalidArgumentException |

### 4.2 Wallet Lock Validation

| Rule | Condition | Error |
|------|-----------|-------|
| Credit locked | isLocked(userId) | RuntimeException |
| Debit locked | isLocked(userId) | RuntimeException |
| Transfer sender locked | isLocked(fromUserId) | RuntimeException |
| Transfer receiver locked | isLocked(toUserId) | RuntimeException |

---

## 5. Edge Cases

| ID | Scenario | Expected Behavior |
|----|----------|-------------------|
| EC-001 | Credit negative amount | Throw InvalidArgumentException |
| EC-002 | Debit negative amount | Throw InvalidArgumentException |
| EC-003 | Debit insufficient balance | Throw RuntimeException |
| EC-004 | Credit locked wallet | Throw RuntimeException |
| EC-005 | Debit locked wallet | Throw RuntimeException |
| EC-006 | Transfer to self | Throw InvalidArgumentException |
| EC-007 | Transfer with insufficient balance | Throw RuntimeException |
| EC-008 | Transfer locked sender | Throw RuntimeException |
| EC-009 | Transfer locked receiver | Throw RuntimeException |
| EC-010 | Zero credit amount | Throw InvalidArgumentException |
| EC-011 | Zero debit amount | Throw InvalidArgumentException |
| EC-012 | New user first transaction | Create wallet automatically |

---

## 6. Non-Functional Requirements

| Req ID | Requirement | Target |
|--------|-------------|--------|
| WALLET-PERF-001 | Transaction processing time | < 100ms |
| WALLET-PERF-002 | Balance calculation time | < 50ms |
| WALLET-SEC-001 | Atomic transfers | 100% |
| WALLET-SEC-002 | No negative balances | Enforced |
| WALLET-AUDIT-001 | Complete transaction history | All operations logged |

---

## 7. Requirements Traceability

| Requirement ID | Source | Design Element | Test Case |
|----------------|--------|----------------|----------|
| WALLET-FUNC-001 | Business Requirements | WalletManager::getBalance() | WALLET-TEST-001 |
| WALLET-FUNC-010 | Business Requirements | WalletManager::credit() | WALLET-TEST-010 |
| WALLET-FUNC-020 | Business Requirements | WalletManager::debit() | WALLET-TEST-020 |
| WALLET-FUNC-030 | Business Requirements | WalletManager::transfer() | WALLET-TEST-030 |
| WALLET-FUNC-040 | Business Requirements | WalletManager::lockWallet() | WALLET-TEST-040 |
| WALLET-FUNC-050 | Business Requirements | WalletManager::getTransactionHistory() | WALLET-TEST-050 |
| WALLET-FUNC-060 | Business Requirements | WalletManager::calculateCashback() | WALLET-TEST-060 |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*