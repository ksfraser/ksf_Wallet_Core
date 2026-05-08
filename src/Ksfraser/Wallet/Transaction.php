<?php
namespace KsfraserWallet;

/**
 * Wallet Transaction Value Object
 */
class Transaction {
    
    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';
    
    /** @var int|null */
    private $id;
    
    /** @var int */
    private $userId;
    
    /** @var string */
    private $type;
    
    /** @var float */
    private $amount;
    
    /** @var string */
    private $details;
    
    /** @var string */
    private $currency;
    
    /** @var int */
    private $createdBy;
    
    /** @var string */
    private $date;
    
    /** @var array */
    private $metadata;
    
    /**
     * @param array $data
     */
    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
        $this->userId = $data['user_id'] ?? $data['userId'] ?? 0;
        $this->type = $data['type'] ?? self::TYPE_CREDIT;
        $this->amount = (float) ($data['amount'] ?? 0);
        $this->details = $data['details'] ?? '';
        $this->currency = $data['currency'] ?? '';
        $this->createdBy = $data['created_by'] ?? $data['createdBy'] ?? 0;
        $this->date = $data['date'] ?? date('Y-m-d H:i:s');
        $this->metadata = $data['metadata'] ?? [];
    }
    
    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }
    
    public function getUserId(): int { return $this->userId; }
    
    public function getType(): string { return $this->type; }
    
    public function getAmount(): float { return $this->amount; }
    
    public function getDetails(): string { return $this->details; }
    
    public function getCurrency(): string { return $this->currency; }
    
    public function getCreatedBy(): int { return $this->createdBy; }
    
    public function getDate(): string { return $this->date; }
    
    public function getMetadata(): array { return $this->metadata; }
    
    /**
     * Convert to array for storage
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'type' => $this->type,
            'amount' => $this->amount,
            'details' => $this->details,
            'currency' => $this->currency,
            'created_by' => $this->createdBy,
            'date' => $this->date,
            'metadata' => json_encode($this->metadata)
        ];
    }
}
