<?php

declare(strict_types=1);

namespace Gazelle\Application\Forum\DTOs;

/**
 * Topic DTO
 */
final readonly class TopicDTO
{
    public function __construct(
        public int $id,
        public int $forumId,
        public string $title,
        public int $authorId,
        public bool $isLocked,
        public bool $isSticky,
        public int $numPosts,
        public ?int $lastPostId,
        public ?int $lastPostAuthorId,
        public ?\DateTimeImmutable $lastPostTime,
        public \DateTimeImmutable $createdTime
    ) {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            id: (int) $row['ID'],
            forumId: (int) $row['ForumID'],
            title: (string) $row['Title'],
            authorId: (int) $row['AuthorID'],
            isLocked: (bool) $row['IsLocked'],
            isSticky: (bool) $row['IsSticky'],
            numPosts: (int) $row['NumPosts'],
            lastPostId: $row['LastPostID'] ? (int) $row['LastPostID'] : null,
            lastPostAuthorId: $row['LastPostAuthorID'] ? (int) $row['LastPostAuthorID'] : null,
            lastPostTime: $row['LastPostTime'] ? new \DateTimeImmutable($row['LastPostTime']) : null,
            createdTime: new \DateTimeImmutable($row['CreatedTime'] ?? 'now')
        );
    }
}
