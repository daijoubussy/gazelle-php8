<?php

declare(strict_types=1);

namespace Gazelle\Application\Forum\DTOs;

/**
 * Post DTO
 */
final readonly class PostDTO
{
    public function __construct(
        public int $id,
        public int $topicId,
        public int $authorId,
        public string $body,
        public \DateTimeImmutable $addedTime,
        public ?int $editedUserId,
        public ?\DateTimeImmutable $editedTime
    ) {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            id: (int) $row['ID'],
            topicId: (int) $row['TopicID'],
            authorId: (int) $row['AuthorID'],
            body: (string) $row['Body'],
            addedTime: new \DateTimeImmutable($row['AddedTime'] ?? 'now'),
            editedUserId: $row['EditedUserID'] ? (int) $row['EditedUserID'] : null,
            editedTime: $row['EditedTime'] ? new \DateTimeImmutable($row['EditedTime']) : null
        );
    }

    /**
     * Check if the post has been edited
     */
    public function isEdited(): bool
    {
        return $this->editedTime !== null;
    }
}
