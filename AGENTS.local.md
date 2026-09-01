<!-- Repo-specific appendix to the shared AGENTS.md. Generic conventions live in AGENTS_ARCH.md (hardlinked). -->

# AGENTS.local.md — ksf_Wallet_Core
> Repo-specific overrides for `ksfraser/ksf-wallet-core`. Core principles (SOLID, DRY, TDD) cannot be overridden.
---
## Architecture Overview
**Business Logic Repo** (framework-agnostic, PHP 7.3+)
This repo contains the CORE WALLET ENGINE extracted from TeraWallet/WooCommerce, refactored into clean PHP classes.
## Repository Structure
```
ksf_Wallet_Core/
├── src/
│   ├── Contracts/         # Interfaces
│   │   ├── WalletServiceInterface.php
│   │   ├── TransactionRepositoryInterface.php
│   │   └── LockServiceInterface.php
│   ├── Services/          # Business logic services
│   │   ├── WalletService.php
│   │   ├── TransactionService.php
│   │   ├── CashbackService.php
│   │   └── LockService.php
│   ├── Models/            # Domain models
│   │   ├── Wallet.php
│   │   ├── Transaction.php
│   │   └── TransactionType.php
│   ├── ValueObjects/     # Immutable value objects
│   │   ├── Money.php
│   │   ├── WalletId.php
│   │   └── UserId.php
│   └── Exceptions/       # Custom exceptions
│       ├── InsufficientFundsException.php
│       ├── WalletLockedException.php
│       └── InvalidAmountException.php
├── tests/
│   ├── Unit/              # PHPUnit tests
│   │   ├── WalletServiceTest.php
│   │   ├── TransactionServiceTest.php
│   │   └── ValueObjects/
│   └── Integration/      # Integration tests
├── ProjectDocs/            # Project documentation
│   ├── Requirements.md
│   ├── RTM.md             # Requirements Traceability Matrix
│   ├── BABOK.md          # Business Analysis Body of Knowledge
│   └── UML.md            # UML diagrams
└── composer.json
```
## Design Patterns Used
### Strategy Pattern
- Payment methods (Wallet, Credit Card, Bank Transfer)
- Cashback calculation strategies
### Observer Pattern
- Event-driven architecture for wallet transactions
- Observers: Email notification, SMS alert, ledger update
## Composer/Packagist
```json
{
    "name": "ksfraser/ksf-wallet-core",
    "description": "Wallet business logic (framework-agnostic, extracted from TeraWallet)",
    "type": "library",
    "require": {
        "php": ">=7.3",
        "ext-json": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0"
    },
    "autoload": {
        "psr-4": {
            "Ksf\\Wallet\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```
## RTM (Requirements Traceability Matrix)
See `ProjectDocs/RTM.md` for full traceability:
- Requirement ID → Test Case ID → Code File → Version
| Req ID | Description | Test Case | Code File | Version |
|--------|-------------|-----------|----------|---------|
| REQ-001 | Wallet top-up | testCanAddFundsToWallet | src/Services/WalletService.php | v1.0.0 |
| REQ-002 | Partial payments | testCanPayPartially | src/Services/PaymentService.php | v1.0.0 |
| REQ-003 | Cashback rules | testCalculatesCashback | src/Services/CashbackService.php | v1.1.0 |
## BABOK Alignment
See `ProjectDocs/BABOK.md` for business analysis alignment:
- Stakeholder needs → Solution approach → Acceptance criteria
### Business Requirements (BABOK)
- **BR-001**: Wallet Management - Users can store funds in digital wallet
- **BR-002**: Fund Top-up - Users can add funds via payment gateways
- **BR-003**: Partial Payments - Users can combine wallet balance with other methods
- **BR-004**: Cashback Engine - Rewards based on Cart/Product/Category rules
## UML Documentation
See `ProjectDocs/UML.md` for:
- Class diagrams
- Sequence diagrams
- Component diagrams
### Example: Wallet Top-up Sequence
```
User -> Frontend: Initiate top-up
Frontend -> PaymentGateway: Process payment
PaymentGateway -> WalletService: Credit wallet
WalletService -> TransactionRepository: Save transaction
WalletService -> Observer: Notify (email, ledger)
WalletService -> Frontend: Return success
```
## WooCommerce Drop-in Replacement (Bonus)
To make this a drop-in replacement for TeraWallet:
- Implement same REST API endpoints: `wc/v3/wallet`
- Use same database table names: `woo_wallet_transactions`
- Support same shortcodes: `[wallet_balance]`, `[wallet_topup]`
- Same meta keys: `_woo_wallet_balance`
**Note**: This is a nice-to-have, NOT a must-have. Focus on FA integration first.
