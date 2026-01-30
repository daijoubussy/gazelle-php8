<?php

declare(strict_types=1);

namespace Gazelle\Application\Forum\DTOs;

/**
 * Forum DTO
 */
final readonly class ForumDTO
{
    public function __construct(
        public int $id,
        public int $categoryId,
        public int $sort,
        public string $name,
        public string $description,
        public int $minClassRead,
        public int $minClassWrite,
        public int $minClassCreate,
        public bool $autoLock,
        public int $autoLockWeeks,
        public int $numTopics,
        public int $numPosts,
        public ?int $lastPostId,
        public ?int $lastPostAuthorId,
        public ?int $lastPostTopicId,
        public ?\DateTimeImmutable $lastPostTime
    ) {}

    /**
     * @param array<string, mixed> $row
     */
    public static function fromDatabaseRow(array $row): self
    {
        return new self(
            id: (int) $row['ID'],
            categoryId: (int) $row['CategoryID'],
            sort: (int) $row['Sort'],
            name: (string) $row['Name'],
            description: (string) $row['Description'],
            minClassRead: (int) $row['MinClassRead'],
            minClassWrite: (int) $row['MinClassWrite'],
            minClassCreate: (int) $row['MinClassCreate'],
            autoLock: (bool) $row['AutoLock'],
            autoLockWeeks: (int) $row['AutoLockWeeks'],
            numTopics: (int) $row['NumTopics'],
            numPosts: (int) $row['NumPosts'],
            lastPostId: $row['LastPostID'] ? (int) $row['LastPostID'] : null,
            lastPostAuthorId: $row['LastPostAuthorID'] ? (int) $row['LastPostAuthorID'] : null,
            lastPostTopicId: $row['LastPostTopicID'] ? (int) $row['LastPostTopicID'] : null,
            lastPostTime: $row['LastPostTime'] ? new \DateTimeImmutable($row['LastPostTime']) : null
        );
    }
}
