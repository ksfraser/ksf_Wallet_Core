# Digital Wallet Core - Test Plan

## Document Information

| Field | Value |
|-------|-------|
| **Module Name** | ksf_Wallet_Core |
| **Document Type** | Test Plan |
| **Version** | 1.0.0 |

---

## 1. Test Objectives

| Objective | Description |
|-----------|-------------|
| **Credit Operations** | Verify credit increases balance |
| **Debit Operations** | Verify debit decreases balance |
| **Transfer Operations** | Verify atomic transfers |
| **Balance Validation** | Verify insufficient funds blocked |
| **Lock Security** | Verify locked wallets blocked |
| **Cashback Calculation** | Verify cashback calculations |

---

## 2. Test Environment

### 2.1 Test Configuration

| Component | Specification |
|-----------|---------------|
| PHP Version | 7.3+ |
| PHPUnit | 9.x |
| Mock Library | PHPUnit built-in mocks |

---

## 3. Test Scenarios

### 3.1 WalletManager Tests

#### WALLET-TEST-001: Get Balance

```php
public function testGetBalance(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('getBalance')->with(1, '')->willReturn(100.00);
    
    $wallet = new WalletManager($mockRepo);
    $balance = $wallet->getBalance(1);
    
    $this->assertEquals(100.00, $balance);
}
```

**Pass Criteria:** Balance returned correctly

---

#### WALLET-TEST-010: Credit Success

```php
public function testCreditSuccess(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('isLocked')->willReturn(false);
    $mockRepo->expects($this->once())
        ->method('saveTransaction')
        ->willReturn(1);
    
    $wallet = new WalletManager($mockRepo);
    $txnId = $wallet->credit(1, 50.00, 'Deposit');
    
    $this->assertEquals(1, $txnId);
}
```

**Pass Criteria:** Credit transaction created

---

#### WALLET-TEST-011: Credit Invalid Amount

```php
public function testCreditInvalidAmount(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $wallet = new WalletManager($mockRepo);
    
    $this->expectException(InvalidArgumentException::class);
    $wallet->credit(1, -50.00, 'Invalid');
}
```

**Pass Criteria:** InvalidArgumentException thrown

---

#### WALLET-TEST-012: Credit Locked Wallet

```php
public function testCreditLockedWallet(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('isLocked')->with(1)->willReturn(true);
    
    $wallet = new WalletManager($mockRepo);
    
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Wallet is locked');
    $wallet->credit(1, 50.00, 'Deposit');
}
```

**Pass Criteria:** RuntimeException thrown for locked wallet

---

#### WALLET-TEST-020: Debit Success

```php
public function testDebitSuccess(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('isLocked')->willReturn(false);
    $mockRepo->method('getBalance')->willReturn(100.00);
    $mockRepo->expects($this->once())
        ->method('saveTransaction')
        ->willReturn(2);
    
    $wallet = new WalletManager($mockRepo);
    $txnId = $wallet->debit(1, 50.00, 'Purchase');
    
    $this->assertEquals(2, $txnId);
}
```

**Pass Criteria:** Debit transaction created

---

#### WALLET-TEST-021: Debit Insufficient Funds

```php
public function testDebitInsufficientFunds(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('isLocked')->willReturn(false);
    $mockRepo->method('getBalance')->willReturn(30.00);
    
    $wallet = new WalletManager($mockRepo);
    
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Insufficient wallet balance');
    $wallet->debit(1, 50.00, 'Purchase');
}
```

**Pass Criteria:** RuntimeException thrown for insufficient balance

---

#### WALLET-TEST-030: Transfer Success

```php
public function testTransferSuccess(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('isLocked')->willReturn(false);
    $mockRepo->method('getBalance')->willReturn(100.00);
    $mockRepo->method('executeInTransaction')
        ->willReturnCallback(function($callback) {
            return $callback();
        });
    $mockRepo->method('saveTransaction')
        ->willReturnOnConsecutiveCalls(1, 2);
    
    $wallet = new WalletManager($mockRepo);
    $result = $wallet->transfer(1, 2, 50.00, 'Payment');
    
    $this->assertEquals(['debit_id' => 1, 'credit_id' => 2], $result);
}
```

**Pass Criteria:** Transfer completes with both IDs returned

---

#### WALLET-TEST-031: Transfer To Self

```php
public function testTransferToSelf(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $wallet = new WalletManager($mockRepo);
    
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Cannot transfer to same user');
    $wallet->transfer(1, 1, 50.00, 'Invalid');
}
```

**Pass Criteria:** InvalidArgumentException thrown for self-transfer

---

#### WALLET-TEST-040: Lock Wallet

```php
public function testLockWallet(): void
{
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->expects($this->once())
        ->method('setLocked')
        ->with(1, true)
        ->willReturn(true);
    
    $wallet = new WalletManager($mockRepo);
    $result = $wallet->lockWallet(1);
    
    $this->assertTrue($result);
}
```

**Pass Criteria:** Lock applied successfully

---

#### WALLET-TEST-050: Transaction History

```php
public function testGetTransactionHistory(): void
{
    $transactions = [
        ['id' => 1, 'type' => 'credit', 'amount' => 100.00],
        ['id' => 2, 'type' => 'debit', 'amount' => 50.00]
    ];
    
    $mockRepo = $this->createMock(WalletRepositoryInterface::class);
    $mockRepo->method('getTransactions')
        ->with(1, ['type' => 'credit'], 20, 0)
        ->willReturn($transactions);
    
    $wallet = new WalletManager($mockRepo);
    $result = $wallet->getTransactionHistory(1, ['type' => 'credit']);
    
    $this->assertCount(2, $result);
}
```

**Pass Criteria:** Transactions returned with filters

---

### 3.2 Cashback Tests

#### WALLET-TEST-060: Percentage Cashback

```php
public function testPercentageCashback(): void
{
    $cashback = WalletManager::calculateCashback(
        100.00,
        'percent',
        ['percentage' => 10]
    );
    
    $this->assertEquals(10.00, $cashback);
}
```

**Pass Criteria:** 10% of 100 = 10

---

#### WALLET-TEST-061: Fixed Cashback

```php
public function testFixedCashback(): void
{
    $cashback = WalletManager::calculateCashback(
        100.00,
        'fixed',
        ['amount' => 5.00]
    );
    
    $this->assertEquals(5.00, $cashback);
}
```

**Pass Criteria:** Fixed 5 returned

---

#### WALLET-TEST-062: Cashback With Max Cap

```php
public function testCashbackWithMaxCap(): void
{
    $cashback = WalletManager::calculateCashback(
        500.00,
        'percent',
        ['percentage' => 10],
        25.00  // max cashback
    );
    
    $this->assertEquals(25.00, $cashback); // 10% of 500 = 50, capped at 25
}
```

**Pass Criteria:** Capped at maximum

---

#### WALLET-TEST-063: Cashback Rounding

```php
public function testCashbackRounding(): void
{
    $cashback = WalletManager::calculateCashback(
        33.33,
        'percent',
        ['percentage' => 10]
    );
    
    $this->assertEquals(3.33, $cashback); // 10% of 33.33 = 3.333, rounded to 3.33
}
```

**Pass Criteria:** Rounded to 2 decimals

---

### 3.3 Transaction Entity Tests

#### WALLET-TEST-070: Create Transaction

```php
public function testCreateTransaction(): void
{
    $txn = new Transaction([
        'user_id' => 1,
        'type' => Transaction::TYPE_CREDIT,
        'amount' => 100.00,
        'details' => 'Test transaction'
    ]);
    
    $this->assertEquals(1, $txn->getUserId());
    $this->assertEquals('credit', $txn->getType());
    $this->assertEquals(100.00, $txn->getAmount());
}
```

**Pass Criteria:** Transaction created with correct values

---

#### WALLET-TEST-071: Transaction To Array

```php
public function testTransactionToArray(): void
{
    $txn = new Transaction([
        'user_id' => 1,
        'type' => Transaction::TYPE_CREDIT,
        'amount' => 100.00
    ]);
    
    $array = $txn->toArray();
    
    $this->assertEquals(1, $array['user_id']);
    $this->assertEquals('credit', $array['type']);
}
```

**Pass Criteria:** Array conversion works

---

## 4. Test Data

### 4.1 Standard Test Data

```php
$validTransaction = [
    'user_id' => 1,
    'type' => 'credit',
    'amount' => 100.00,
    'details' => 'Test transaction',
    'currency' => 'USD',
    'created_by' => 1,
    'date' => '2026-05-13 10:00:00',
    'metadata' => []
];
```

### 4.2 Edge Case Test Data

| Scenario | Amount | Balance | Locked | Expected |
|----------|--------|---------|-------|----------|
| Valid credit | 50.00 | 100.00 | false | Success |
| Negative credit | -50.00 | 100.00 | false | Exception |
| Zero credit | 0 | 100.00 | false | Exception |
| Sufficient balance debit | 50.00 | 100.00 | false | Success |
| Insufficient balance debit | 150.00 | 100.00 | false | Exception |
| Credit locked wallet | 50.00 | 100.00 | true | Exception |
| Transfer valid | 50.00 | 100.00 | false | Success |
| Transfer self | 50.00 | 100.00 | false | Exception |
| Transfer insufficient | 150.00 | 100.00 | false | Exception |

---

## 5. Pass Criteria Summary

| Test Category | Pass Criteria |
|--------------|---------------|
| Balance | Correct balance returned |
| Credit | Transaction created, balance increased |
| Debit | Transaction created, balance decreased |
| Transfer | Both transactions created atomically |
| Validation | Invalid inputs rejected with exceptions |
| Lock Security | Locked wallets blocked |
| Cashback | Correct calculations with caps |
| Transaction Entity | All getters work, toArray works |

---

## 6. Coverage Targets

| Component | Target Coverage |
|-----------|-----------------|
| WalletManager | 100% |
| Transaction | 100% |
| calculateCashback | 100% |

---

*Document Version: 1.0.0*  
*Author: KSFII Development Team*