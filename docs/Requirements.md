# Requirements Document - KSF Wallet Core

## Business Logic Extracted from TeraWallet

### Functional Requirements

#### FR-1: Wallet Balance Management
- **FR-1.1**: System must retrieve current wallet balance for any user
- **FR-1.2**: System must calculate balance from transaction history (credit - debit)
- **FR-1.3**: Balance must support multiple currencies (single_base or per_currency mode)
- **FR-1.4**: System must cache balance in user_meta for performance

#### FR-2: Transaction Processing
- **FR-2.1**: System must support CREDIT transactions (add funds)
- **FR-2.2**: System must support DEBIT transactions (withdraw/spend)
- **FR-2.3**: System must prevent overdraft (debit > balance) with race-condition protection
- **FR-2.4**: System must use database-level locking (GET_LOCK) for concurrent requests
- **FR-2.5**: All transactions must be atomic (use database transactions)

#### FR-3: Transfer Between Users
- **FR-3.1**: System must support transferring funds between two users
- **FR-3.2**: Transfer must be atomic (both debit and credit succeed, or neither)
- **FR-3.3**: System must use deterministic lock ordering (min/max user_id) to prevent deadlocks
- **FR-3.4**: System must validate both wallets are not locked before transfer

#### FR-4: Cashback Engine
- **FR-4.1**: System must calculate cashback based on percentage of amount
- **FR-4.2**: System must support fixed amount cashback
- **FR-4.3**: System must enforce maximum cashback cap
- **FR-4.4**: Cashback rules: cart-wise, product-wise, category-wise

#### FR-5: Wallet Locking
- **FR-5.1**: System must allow locking a user's wallet (admin function)
- **FR-5.2**: Locked wallets must reject all credit/debit/transfer operations
- **FR-5.3**: System must allow unlocking a wallet

### Non-Functional Requirements

#### NFR-1: Performance
- **NFR-1.1**: Balance calculation must complete in <100ms for users with <10,000 transactions
- **NFR-1.2**: Transaction insert must complete in <50ms

#### NFR-2: Security
- **NFR-2.1**: All monetary calculations must use PHP float with proper rounding
- **NFR-2.2**: Database queries must use prepared statements (via Repository)
- **NFR-2.3**: Concurrent transactions must be serialized using DB locks (5 second timeout)

#### NFR-3: Compatibility
- **NFR-3.1**: Code must run on PHP 7.3+
- **NFR-3.2**: Framework-agnostic (no WordPress/WooCommerce dependencies)
- **NFR-3.3**: PSR-4 autoloading compliant

### Data Requirements

#### DR-1: Transaction Record
- user_id (int)
- type (enum: 'credit', 'debit')
- amount (float, positive)
- currency (string, ISO 4217)
- details (string, description)
- created_by (int, admin user who initiated)
- date (datetime)
- metadata (JSON, arbitrary key-value pairs)

#### DR-2: Balance Cache
- user_id (int)
- balance (float)
- currency (string)
- last_updated (datetime)
