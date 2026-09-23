<?php

namespace App\Services\Payment;

class PaymentResult
{
    public const STATUS_SUCCESS = 'success';

    public const STATUS_PENDING = 'pending';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    public function __construct(
        protected string $status,
        protected ?string $reference = null,
        protected ?string $transactionId = null,
        protected ?string $message = null,
        protected array $payload = []
    ) {}

    public static function success(string $reference, ?string $transactionId = null, string $message = 'Payment completed successfully.', array $payload = []): self
    {
        return new self(self::STATUS_SUCCESS, $reference, $transactionId, $message, $payload);
    }

    public static function pending(string $reference, ?string $transactionId = null, string $message = 'Payment is pending verification.', array $payload = []): self
    {
        return new self(self::STATUS_PENDING, $reference, $transactionId, $message, $payload);
    }

    public static function failed(string $reference, string $message = 'Payment failed.', array $payload = []): self
    {
        return new self(self::STATUS_FAILED, $reference, null, $message, $payload);
    }

    public static function expired(string $reference, string $message = 'Payment expired.', array $payload = []): self
    {
        return new self(self::STATUS_EXPIRED, $reference, null, $message, $payload);
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
