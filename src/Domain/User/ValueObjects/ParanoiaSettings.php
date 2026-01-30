<?php

declare(strict_types=1);

namespace Gazelle\Domain\User\ValueObjects;

use Gazelle\Domain\Common\ValueObject;

/**
 * Paranoia Settings Value Object
 *
 * Represents user privacy settings for various profile elements.
 */
final readonly class ParanoiaSettings extends ValueObject
{
    /**
     * @param array<string> $hiddenElements Elements hidden from other users
     */
    private function __construct(
        private array $hiddenElements
    ) {}

    /**
     * Create from serialized database value
     */
    public static function fromSerialized(?string $serialized): self
    {
        if ($serialized === null || $serialized === '') {
            return new self([]);
        }

        $data = @unserialize($serialized);

        if (!is_array($data)) {
            return new self([]);
        }

        return new self(array_values(array_filter($data, 'is_string')));
    }

    /**
     * Create with no hidden elements (public profile)
     */
    public static function public(): self
    {
        return new self([]);
    }

    /**
     * Create with all elements hidden (maximum privacy)
     */
    public static function maximum(): self
    {
        return new self([
            'downloaded',
            'uploaded',
            'ratio',
            'bonuspoints',
            'lastseen',
            'requiredratio',
            'invitedcount',
            'artistsadded',
            'notifications',
            'torrentcomments',
            'collages',
            'collagecontribs',
            'requestsfilled',
            'requestsvoted',
            'uploads',
            'uniquegroups',
            'perfectflacs',
            'seeding',
            'leeching',
            'snatched',
            'invited',
            'hide_donor_heart',
        ]);
    }

    /**
     * Check if a specific element is hidden
     */
    public function isHidden(string $element): bool
    {
        return in_array($element, $this->hiddenElements, true);
    }

    /**
     * Check if donor heart should be shown
     */
    public function showDonorHeart(): bool
    {
        return !$this->isHidden('hide_donor_heart');
    }

    /**
     * Check if download stats are visible
     */
    public function showDownloadStats(): bool
    {
        return !$this->isHidden('downloaded') && !$this->isHidden('ratio');
    }

    /**
     * Check if upload stats are visible
     */
    public function showUploadStats(): bool
    {
        return !$this->isHidden('uploaded');
    }

    /**
     * Get all hidden elements
     *
     * @return array<string>
     */
    public function hiddenElements(): array
    {
        return $this->hiddenElements;
    }

    /**
     * Add an element to hidden list
     */
    public function withHidden(string $element): self
    {
        if ($this->isHidden($element)) {
            return $this;
        }

        return new self([...$this->hiddenElements, $element]);
    }

    /**
     * Remove an element from hidden list
     */
    public function withVisible(string $element): self
    {
        if (!$this->isHidden($element)) {
            return $this;
        }

        return new self(
            array_values(array_filter(
                $this->hiddenElements,
                fn(string $e) => $e !== $element
            ))
        );
    }

    /**
     * Serialize for database storage
     */
    public function serialize(): string
    {
        return serialize($this->hiddenElements);
    }

    public function equals(ValueObject $other): bool
    {
        if (!$other instanceof self) {
            return false;
        }

        $a = $this->hiddenElements;
        $b = $other->hiddenElements;
        sort($a);
        sort($b);

        return $a === $b;
    }
}
