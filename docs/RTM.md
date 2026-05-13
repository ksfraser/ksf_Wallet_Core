# Requirements Traceability Matrix (RTM) - KSF Wallet Core

| Requirement ID | Requirement Description | Component | Test Case ID | Status |
|----------------|------------------------|-------------|---------------|--------|
| FR-1.1 | Retrieve wallet balance for any user | WalletManager::getBalance() | TC-001 | Pending |
| FR-1.2 | Calculate balance from transaction history | WalletRepositoryInterface::getBalance() | TC-002 | Pending |
| FR-1.3 | Support multiple currencies | WalletManager::getBalance($currency) | TC-003 | Pending |
| FR-2.1 | Support CREDIT transactions | WalletManager::credit() | TC-005 | Pending |
| FR-2.2 | Support DEBIT transactions | WalletManager::debit() | TC-006 | Pending |
| FR-2.3 | Prevent overdraft with race-condition protection | WalletManager::debit() | TC-007 | Pending |
| FR-2.4 | Database-level locking for concurrent requests | WalletRepositoryInterface::executeInTransaction() | TC-008 | Pending |
| FR-2.5 | Atomic transactions | WalletRepositoryInterface::executeInTransaction() | TC-009 | Pending |
| FR-3.1 | Transfer funds between users | WalletManager::transfer() | TC-010 | Pending |
| FR-3.2 | Atomic transfer (both succeed or neither) | WalletManager::transfer() | TC-011 | Pending |
| FR-3.3 | Deterministic lock ordering (deadlock prevention) | WalletManager::transfer() | TC-012 | Pending |
| FR-4.1 | Calculate cashback percentage | WalletManager::calculateCashback() | TC-014 | Pending |
| FR-4.2 | Support fixed amount cashback | WalletManager::calculateCashback() | TC-015 | Pending |
| FR-4.3 | Enforce maximum cashback cap | WalletManager::calculateCashback() | TC-016 | Pending |
| FR-5.1 | Lock user's wallet | WalletManager::lockWallet() | TC-018 | Pending |
| FR-5.2 | Reject operations on locked wallets | WalletManager::credit/debit/transfer() | TC-019 | Pending |
| FR-5.3 | Unlock wallet | WalletManager::unlockWallet() | TC-020 | Pending |
| NFR-1.1 | Balance calc performance | Performance test | TC-021 | Pending |
| NFR-1.2 | Transaction insert performance | Performance test | TC-022 | Pending |
| NFR-2.1 | Monetary calculations with proper rounding | WalletManager (all methods) | TC-023 | Pending |
| NFR-2.2 | Prepared statements in Repository | FA_WalletRepository | TC-024 | Pending |
| NFR-2.3 | Concurrent serialization with DB locks | WalletManager::debit/credit() | TC-025 | Pending |
| NFR-3.1 | PHP 7.3+ compatibility | All classes | TC-026 | Pending |
| NFR-3.2 | Framework-agnostic | No WordPress deps | TC-027 | Pending |
| NFR-3.3 | PSR-4 autoloading | composer.json | TC-028 | Pending |
| DR-1 | Transaction record structure | Transaction class | TC-029 | Pending |
| DR-2 | Balance cache structure | FA_WalletRepository | TC-030 | Pending |

## Test Case Summary
- Total Test Cases: 27
- Implemented: 0
- Pending: 27
- Pass Rate: TBD

*Document Version: 1.0.0*
*Last Updated: 2026-05-12*
