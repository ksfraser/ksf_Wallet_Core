# Digital Wallet Core - Use Case Specification

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Wallet_Core |
| **Document Type** | Use Case Specification |
| **Version** | 1.0.0 |

---

## 1. Use Case Overview

| Use Case ID | UC-WALLET-001 |
|-------------|--------------|
| **Use Case Name** | Credit Wallet |
| **Primary Actor** | Customer / Admin / System |
| **Secondary Actors** | Wallet Repository |
| **Brief Description** | System adds funds to a customer's wallet |
| **Pre-condition** | User exists, wallet not locked |
| **Post-condition** | Balance increased, transaction recorded |

---

## 2. Use Cases

### 2.1 UC-WALLET-001: Credit Wallet

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Caller | Provides userId, amount, details, metadata |
| 2 | WalletManager | Validates amount > 0 |
| 3 | WalletManager | Checks isLocked(userId) |
| 4 | If not locked | Proceeds to credit |
| 5 | WalletManager | Creates Transaction with TYPE_CREDIT |
| 6 | WalletManager | Calls repository saveTransaction() |
| 7 | WalletManager | Returns transaction ID |

**Alternative Flows:**

| Step | Branch Condition | Action |
|------|------------------|--------|
| 2a | Amount <= 0 | Throw InvalidArgumentException |
| 3a | Wallet locked | Throw RuntimeException |

**Pre-conditions:**
- User exists in system
- Wallet not locked
- Amount is positive

**Post-conditions:**
- Transaction recorded
- Balance increased by amount
- Transaction ID returned

---

### 2.2 UC-WALLET-002: Debit Wallet

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Caller | Provides userId, amount, details, metadata |
| 2 | WalletManager | Validates amount > 0 |
| 3 | WalletManager | Checks isLocked(userId) |
| 4 | If not locked | Proceeds to debit |
| 5 | WalletManager | Gets current balance |
| 6 | WalletManager | Validates balance >= amount |
| 7 | WalletManager | Creates Transaction with TYPE_DEBIT |
| 8 | WalletManager | Calls repository saveTransaction() |
| 9 | WalletManager | Returns transaction ID |

**Alternative Flows:**

| Step | Branch Condition | Action |
|------|------------------|--------|
| 2a | Amount <= 0 | Throw InvalidArgumentException |
| 3a | Wallet locked | Throw RuntimeException |
| 6a | Insufficient balance | Throw RuntimeException |

**Pre-conditions:**
- User exists in system
- Wallet not locked
- Amount is positive
- Sufficient balance available

**Post-conditions:**
- Transaction recorded
- Balance decreased by amount
- Transaction ID returned

---

### 2.3 UC-WALLET-003: Transfer Funds

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Caller | Provides fromUserId, toUserId, amount, details, metadata |
| 2 | WalletManager | Validates fromUserId != toUserId |
| 3 | WalletManager | Validates amount > 0 |
| 4 | WalletManager | Checks isLocked(fromUserId) |
| 5 | WalletManager | Checks isLocked(toUserId) |
| 6 | If both unlocked | Proceeds to transfer |
| 7 | WalletManager | Calls repository executeInTransaction() |
| 8 | Within transaction | Calls debit(fromUserId, ...) |
| 9 | Within transaction | Calls credit(toUserId, ...) |
| 10 | Transaction complete | Returns debit and credit IDs |

**Alternative Flows:**

| Step | Branch Condition | Action |
|------|------------------|--------|
| 2a | Same user | Throw InvalidArgumentException |
| 3a | Amount <= 0 | Throw InvalidArgumentException |
| 4a | Sender locked | Throw RuntimeException |
| 5a | Receiver locked | Throw RuntimeException |
| 9a | Insufficient balance | Transaction rolled back |

**Pre-conditions:**
- Both users exist
- Neither wallet locked
- Amount is positive
- Sender has sufficient balance

**Post-conditions:**
- Sender balance decreased
- Receiver balance increased
- Both transactions recorded atomically
- Both transaction IDs returned

---

### 2.4 UC-WALLET-004: Check Balance

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Caller | Provides userId, optional currency |
| 2 | WalletManager | Calls repository getBalance() |
| 3 | WalletManager | Returns balance float |

**Pre-conditions:**
- User exists in system

**Post-conditions:**
- Current balance returned
- Balance calculated from transaction sum

---

### 2.5 UC-WALLET-005: Get Transaction History

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Caller | Provides userId, filters, limit, offset |
| 2 | WalletManager | Calls repository getTransactions() |
| 3 | WalletManager | Returns transaction array |

**Pre-conditions:**
- User exists in system

**Post-conditions:**
- Transaction list returned
- Sorted by date descending
- Filtered and paginated as requested

---

### 2.6 UC-WALLET-006: Lock Wallet

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | Admin | Provides userId |
| 2 | WalletManager | Calls repository setLocked(userId, true) |
| 3 | WalletManager | Returns success boolean |

**Pre-conditions:**
- Admin has permission

**Post-conditions:**
- Wallet locked
- All operations blocked until unlocked

---

### 2.7 UC-WALLET-007: Calculate Cashback

**Basic Flow:**

| Step | Actor | Action |
|------|-------|--------|
| 1 | System | Provides amount, ruleType, ruleConfig, maxCashback |
| 2 | WalletManager | Switches on ruleType |
| 3 | If percent | Calculates amount * (percentage / 100) |
| 4 | If fixed | Returns fixed amount from config |
| 5 | If maxCashback | Applies cap if result exceeds |
| 6 | Returns | Rounded cashback amount |

**Pre-conditions:**
- Rule type is 'percent' or 'fixed'

**Post-conditions:**
- Cashback amount calculated and returned

---

## 3. Actor Definitions

| Actor | Role | System Interface |
|-------|------|------------------|
| **Customer** | End user initiating wallet operations | Via platform UI |
| **Admin** | Staff managing wallets | Admin interface |
| **System** | Automated processes (cashback, refunds) | Programmatic API |
| **Wallet Repository** | Data persistence layer | Platform adapter |

---

## 4. Sequence Diagrams

### 4.1 Transfer Funds Sequence

```
┌──────────────┐    ┌──────────────────┐    ┌──────────────────┐
│   Caller    │    │   WalletManager │    │   Repository     │
└──────┬───────┘    └────────┬─────────┘    └───────┬──────────┘
       │                     │                        │
       │  transfer(from, to, amount)                │
       │───────────────────▶│                        │
       │                     │                        │
       │                     │  validate(same user)  │
       │                     │                        │
       │                     │  [OK]                  │
       │                     │                        │
       │                     │  isLocked(from)        │
       │                     │──────────────────────▶│
       │                     │◀──────────────────────│
       │                     │                        │
       │                     │  [NOT locked]          │
       │                     │                        │
       │                     │  isLocked(to)          │
       │                     │──────────────────────▶│
       │                     │◀──────────────────────│
       │                     │                        │
       │                     │  [NOT locked]          │
       │                     │                        │
       │                     │  executeInTransaction()│
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  debit(from, ...)      │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  [OK]                  │
       │                     │                        │
       │                     │  credit(to, ...)       │
       │                     │──────────────────────▶│
       │                     │                        │
       │                     │  [OK]                  │
       │                     │◀──────────────────────│
       │                     │                        │
       │  [debit_id, credit_id]                      │
       │◀───────────────────│                        │
       │                     │                        │
```

---

## 5. Activity Diagrams

### 5.1 Credit Flow

```
┌─────────────┐
│   Start     │
└──────┬──────┘
       │
       ▼
┌─────────────────────┐
│  Receive credit()   │
│  call               │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Amount > 0?         │
└──────────┬──────────┘
           │
      ┌────┴────┐
      │ Yes     │ No
      ▼         ▼
┌───────────┐ ┌─────────────────────────┐
│ Continue  │ │ throw InvalidArgument   │
└─────┬─────┘ └─────────────────────────┘
      │
      ▼
┌─────────────────────┐
│  Wallet locked?      │
└──────────┬──────────┘
           │
      ┌────┴────┐
      │ No      │ Yes
      ▼         ▼
┌───────────┐ ┌─────────────────────────┐
│ Continue  │ │ throw RuntimeException  │
└─────┬─────┘ └─────────────────────────┘
      │
      ▼
┌─────────────────────┐
│  Create Transaction  │
│  TYPE_CREDIT        │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  saveTransaction()  │
└──────────┬──────────┘
           │
           ▼
┌─────────────────────┐
│  Return transaction │
│  ID                 │
└──────────┬──────────┘
           │
           ▼
       ┌───────────┐
       │   End     │
       └───────────┘
```

---

## 6. Use Case Summary

| UC ID | Use Case Name | Primary Actor | Priority |
|-------|--------------|----------------|----------|
| UC-WALLET-001 | Credit Wallet | Customer/Admin | Critical |
| UC-WALLET-002 | Debit Wallet | Customer/Admin | Critical |
| UC-WALLET-003 | Transfer Funds | Customer | Critical |
| UC-WALLET-004 | Check Balance | Customer/Admin | High |
| UC-WALLET-005 | Get Transaction History | Customer/Admin | High |
| UC-WALLET-006 | Lock Wallet | Admin | High |
| UC-WALLET-007 | Calculate Cashback | System | Medium |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*