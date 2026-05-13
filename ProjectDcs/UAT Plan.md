# Digital Wallet Core - UAT Plan

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Wallet_Core |
| **Document Type** | User Acceptance Test Plan |
| **Version** | 1.0.0 |

---

## 1. UAT Objectives

| Objective | Description | Success Criteria |
|-----------|-------------|------------------|
| **Credit Operations** | Verify wallet credits work correctly | Balance increases |
| **Debit Operations** | Verify wallet debits work correctly | Balance decreases |
| **Transfer Operations** | Verify transfers are atomic | Both balances updated |
| **Balance Validation** | Verify insufficient funds blocked | Appropriate error |
| **Lock Security** | Verify wallet locking works | Operations blocked |
| **Transaction History** | Verify audit trail | All transactions logged |

---

## 2. UAT Scope

### 2.1 In Scope

- Credit operations
- Debit operations
- Transfer operations
- Balance checks
- Wallet locking
- Transaction history
- Cashback calculations

### 2.2 Out of Scope

- Payment gateway integration
- Admin UI components
- Currency conversion
- API rate limiting

---

## 3. Test Environments

| Environment | Purpose | Data |
|-------------|---------|------|
| **Development** | Initial testing | Test data |
| **Staging** | Full UAT | Realistic test data |

---

## 4. UAT Scenarios

### 4.1 Scenario: Credit Wallet

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-001 |
| **Scenario Name** | Credit Customer Wallet |
| **Priority** | Critical |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select test customer | Customer identified |
| 2 | Check initial balance | Current balance recorded |
| 3 | Execute credit of $100 | Credit operation completed |
| 4 | Check new balance | Balance increased by $100 |
| 5 | Check transaction history | Transaction recorded |

**Pass Criteria:**
- [ ] Credit operation completed
- [ ] Balance increased correctly
- [ ] Transaction recorded with details

---

### 4.2 Scenario: Debit Wallet

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-002 |
| **Scenario Name** | Debit Customer Wallet |
| **Priority** | Critical |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select test customer with balance | Customer identified |
| 2 | Check initial balance | Current balance recorded |
| 3 | Execute debit of $50 | Debit operation completed |
| 4 | Check new balance | Balance decreased by $50 |
| 5 | Verify insufficient funds message | Shown when balance insufficient |

**Pass Criteria:**
- [ ] Debit operation completed
- [ ] Balance decreased correctly
- [ ] Insufficient funds message when applicable

---

### 4.3 Scenario: Transfer Funds

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-003 |
| **Scenario Name** | Transfer Between Wallets |
| **Priority** | Critical |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select sender customer | Sender identified |
| 2 | Select receiver customer | Receiver identified |
| 3 | Check both initial balances | Both balances recorded |
| 4 | Execute transfer of $50 | Transfer operation completed |
| 5 | Check sender new balance | Decreased by $50 |
| 6 | Check receiver new balance | Increased by $50 |
| 7 | Verify both transaction records | Both logged |

**Pass Criteria:**
- [ ] Sender balance decreased
- [ ] Receiver balance increased
- [ ] Both transactions recorded
- [ ] Atomic operation confirmed

---

### 4.4 Scenario: Insufficient Funds Rejection

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-004 |
| **Scenario Name** | Reject Debit with Insufficient Balance |
| **Priority** | High |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select test customer | Customer identified |
| 2 | Set balance to $25 | Balance set |
| 3 | Attempt debit of $50 | Debit attempted |
| 4 | Verify error message | "Insufficient funds" shown |
| 5 | Check balance unchanged | Still $25 |

**Pass Criteria:**
- [ ] Operation rejected
- [ ] Error message displayed
- [ ] Balance unchanged

---

### 4.5 Scenario: Lock Wallet

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-005 |
| **Scenario Name** | Lock and Unlock Wallet |
| **Priority** | High |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Select test customer | Customer identified |
| 2 | Lock wallet | Lock operation completed |
| 3 | Attempt credit | Operation blocked |
| 4 | Verify error message | "Wallet is locked" shown |
| 5 | Unlock wallet | Unlock operation completed |
| 6 | Attempt credit again | Operation succeeds |

**Pass Criteria:**
- [ ] Lock applied
- [ ] Operations blocked when locked
- [ ] Unlock restores operations

---

### 4.6 Scenario: Transaction History

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-006 |
| **Scenario Name** | View Transaction History |
| **Priority** | Medium |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Create multiple transactions | Credit, debit, transfer |
| 2 | View transaction history | All transactions displayed |
| 3 | Filter by credit type | Only credits shown |
| 4 | Filter by date range | Correct date filter applied |
| 5 | Verify pagination | Limits applied correctly |

**Pass Criteria:**
- [ ] All transactions displayed
- [ ] Filters work correctly
- [ ] Pagination works

---

### 4.7 Scenario: Cashback Calculation

| Field | Value |
|-------|-------|
| **Scenario ID** | UAT-WALLET-007 |
| **Scenario Name** | Calculate Cashback |
| **Priority** | Medium |

**Steps to Execute:**

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Calculate percentage cashback (10% of $100) | $10 returned |
| 2 | Calculate fixed cashback ($5 per order) | $5 returned |
| 3 | Calculate cashback with cap ($10% of $500, cap $25) | $25 returned (capped) |
| 4 | Verify rounding | 2 decimal places |

**Pass Criteria:**
- [ ] Percentage calculated correctly
- [ ] Fixed amount correct
- [ ] Cap applied correctly
- [ ] Rounding correct

---

## 5. Acceptance Criteria Checklist

### 5.1 Functional Acceptance

| Criterion | Description | Status |
|-----------|-------------|--------|
| FA-001 | Credit adds funds to wallet | [ ] |
| FA-002 | Debit removes funds from wallet | [ ] |
| FA-003 | Transfer moves funds atomically | [ ] |
| FA-004 | Insufficient funds rejected | [ ] |
| FA-005 | Locked wallet blocked | [ ] |
| FA-006 | Transaction history complete | [ ] |
| FA-007 | Cashback calculated correctly | [ ] |

### 5.2 Technical Acceptance

| Criterion | Description | Status |
|-----------|-------------|--------|
| TA-001 | Unit tests passing | [ ] |
| TA-002 | Transactions atomic | [ ] |
| TA-003 | Balance accurate | [ ] |

---

## 6. Sign-Off Requirements

### 6.1 UAT Completion Criteria

| Milestone | Criteria | Sign-off By |
|-----------|----------|-------------|
| Credit/Debit | Both operations verified | QA Lead |
| Transfer | Atomicity confirmed | QA Lead |
| Security | Lock/unlock verified | Security |
| Final Acceptance | All criteria passed | Product Owner |

### 6.2 Sign-Off Template

```
UAT Sign-Off Certification

Module: ksf_Wallet_Core
Version: 1.0.0
UAT Date: ________________

I certify that the UAT has been completed for ksf_Wallet_Core.
All critical and high priority scenarios have passed.

Sign-off Authorizations:

Product Owner: _________________ Date: _______
QA Lead: _________________ Date: _______
Development Lead: _________________ Date: _______
```

---

## 7. Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Race conditions in concurrent transactions | Medium | High | Database locking |
| Incorrect balance calculation | Low | Critical | Unit tests |
| Failed atomic transfer (partial commit) | Low | Critical | Database transaction rollback |
| Float precision issues | Low | Medium | Decimal handling |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*