# AGENTS.md - ksf_Wallet_Core

## Architecture Overview

**Business Logic Repo** (framework-agnostic, PHP 7.3+)

This repo contains the CORE WALLET ENGINE extracted from TeraWallet/WooCommerce, refactored into clean PHP classes.

### Core Principles
- **SOLID**: Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion
- **DRY**: Don't Repeat Yourself - extract reusable logic
- **TDD**: Test-Driven Development - write tests first
- **DI**: Dependency Injection - inject dependencies, don't hardcode
- **SRP**: Single Responsibility Principle - each class has one reason to change

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

## Coding Standards

### PHP Compatibility
- **Target**: PHP 7.3+ (with eye to PHP 8.x upgrades)
- Use `declare(strict_types=1);` at top of all PHP files
- Avoid PHP 8+ features until we drop PHP 7.3 support

### Naming Conventions
- **Interfaces**: `WalletServiceInterface`
- **Services**: `WalletService`
- **Value Objects**: `Money`, `WalletId`
- **Exceptions**: `InsufficientFundsException`

### Documentation (UML/BABOK)
```php
/**
 * Add funds to wallet
 * 
 * @param WalletId $walletId The wallet identifier
 * @param Money $amount Amount to add
 * @return Transaction The created transaction
 * 
 * @UML Note: See ProjectDocs/UML.md - Wallet top-up sequence diagram
 * @BABOK Related: BR-001 Wallet Management, BR-002 Fund Top-up
 */
public function addFunds(WalletId $walletId, Money $amount): Transaction;
```

## Testing Strategy

### TDD Red-Green-Refactor
1. **RED**: Write failing test
2. **GREEN**: Write minimal code to pass
3. **REFACTOR**: Improve code while keeping tests green

### Test Structure
```php
namespace Tests\Unit;

class WalletServiceTest extends \PHPUnit\Framework\TestCase
{
    private $repository;
    private $walletService;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(TransactionRepositoryInterface::class);
        $this->walletService = new WalletService($this->repository);
    }

    public function testCanAddFundsToWallet(): void
    {
        // Arrange
        $walletId = new WalletId(1);
        $amount = new Money(10000, 'USD'); // $100.00 in cents
        
        $this->repository->expects($this->once())
            ->method('save')
            ->willReturn(true);
        
        // Act
        $transaction = $this->walletService->addFunds($walletId, $amount);
        
        // Assert
        $this->assertEquals(10000, $transaction->getAmount()->getAmount());
        $this->assertEquals('credit', $transaction->getType());
    }

    public function testCannotAddNegativeAmount(): void
    {
        $this->expectException(InvalidAmountException::class);
        
        $walletId = new WalletId(1);
        $amount = new Money(-100, 'USD');
        
        $this->walletService->addFunds($walletId, $amount);
    }
}
```

## Design Patterns Used

### Strategy Pattern
- Payment methods (Wallet, Credit Card, Bank Transfer)
- Cashback calculation strategies

### Repository Pattern
- Data access abstraction (DB-agnostic)
- Allows swapping MySQL, PostgreSQL, or in-memory for tests

### Observer Pattern
- Event-driven architecture for wallet transactions
- Observers: Email notification, SMS alert, ledger update

## Version Tagging

Follow Semantic Versioning (SemVer): `MAJOR.MINOR.PATCH`
- **MAJOR**: Incompatible API changes
- **MINOR**: New functionality (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

```bash
git tag -a v1.0.0 -m "Initial release with wallet functionality"
git push origin v1.0.0
```

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
