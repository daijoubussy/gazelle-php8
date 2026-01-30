<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Transfer Stats Value Object
 *
 * Tracks user upload/download statistics.
 */
final readonly class TransferStats extends ValueObject
{
    private function __construct(
        private int $uploaded,
        private int $downloaded,
        private int $bonusPoints
    ) {
        if ($uploaded < 0) {
            throw new \InvalidArgumentException('Uploaded bytes cannot be negative');
        }
        if ($downloaded < 0) {
            throw new \InvalidArgumentException('Downloaded bytes cannot be negative');
        }
        if ($bonusPoints < 0) {
            throw new \InvalidArgumentException('Bonus points cannot be negative');
        }
    }

    public static function create(
        int $uploaded = 0,
        int $downloaded = 0,
        int $bonusPoints = 0
    ): self {
        return new self($uploaded, $downloaded, $bonusPoints);
    }

    public static function zero(): self
    {
        return new self(0, 0, 0);
    }

    public function uploaded(): int
    {
        return $this->uploaded;
    }

    public function downloaded(): int
    {
        return $this->downloaded;
    }

    public function bonusPoints(): int
    {
        return $this->bonusPoints;
    }

    /**
     * Calculate ratio (returns INF if no download)
     */
    public function ratio(): float
    {
        if ($this->downloaded === 0) {
            return $this->uploaded > 0 ? INF : 0.0;
        }

        return $this->uploaded / $this->downloaded;
    }

    /**
     * Check if ratio is above minimum
     */
    public function hasRequiredRatio(float $minimum): bool
    {
        return $this->ratio() >= $minimum;
    }

    /**
     * Add upload bytes
     */
    public function withAddedUpload(int $bytes): self
    {
        return new self(
            $this->uploaded + $bytes,
            $this->downloaded,
            $this->bonusPoints
        );
    }

    /**
     * Add download bytes
     */
    public function withAddedDownload(int $bytes): self
    {
        return new self(
            $this->uploaded,
            $this->downloaded + $bytes,
            $this->bonusPoints
        );
    }

    /**
     * Add bonus points
     */
    public function withAddedBonusPoints(int $points): self
    {
        return new self(
            $this->uploaded,
            $this->downloaded,
            $this->bonusPoints + $points
        );
    }

    /**
     * Spend bonus points
     */
    public function withSpentBonusPoints(int $points): self
    {
        if ($points > $this->bonusPoints) {
            throw new \InvalidArgumentException('Insufficient bonus points');
        }

        return new self(
            $this->uploaded,
            $this->downloaded,
            $this->bonusPoints - $points
        );
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self
            && $this->uploaded === $other->uploaded
            && $this->downloaded === $other->downloaded
            && $this->bonusPoints === $other->bonusPoints;
    }
}
