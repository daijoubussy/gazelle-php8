<?php

declare(strict_types=1);

namespace Gazelle\Domain\Forum;

use Gazelle\Domain\Common\AggregateRoot;

/**
 * Forum Aggregate Root
 *
 * Represents a forum category.
 */
final class Forum extends AggregateRoot
{
    private function __construct(
        private readonly ForumId $id,
        private readonly int $categoryId,
        private readonly int $sort,
        private string $name,
        private string $description,
        private int $minClassRead,
        private int $minClassWrite,
        private int $minClassCreate,
        private bool $autoLock,
        private int $autoLockWeeks,
        private int $numTopics,
        private int $numPosts,
        private ?int $lastPostId,
        private ?int $lastPostAuthorId,
        private ?int $lastPostTopicId,
        private ?\DateTimeImmutable $lastPostTime
    ) {}

    public static function create(
        ForumId $id,
        int $categoryId,
        string $name,
        string $description,
        int $minClassRead = 0,
        int $minClassWrite = 0,
        int $minClassCreate = 0,
        int $sort = 0
    ): self {
        return new self(
            id: $id,
            categoryId: $categoryId,
            sort: $sort,
            name: $name,
            description: $description,
            minClassRead: $minClassRead,
            minClassWrite: $minClassWrite,
            minClassCreate: $minClassCreate,
            autoLock: false,
            autoLockWeeks: 0,
            numTopics: 0,
            numPosts: 0,
            lastPostId: null,
            lastPostAuthorId: null,
            lastPostTopicId: null,
            lastPostTime: null
        );
    }

    /**
     * Reconstitute from database
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ForumId::fromInt((int) $data['ID']),
            categoryId: (int) $data['CategoryID'],
            sort: (int) $data['Sort'],
            name: (string) $data['Name'],
            description: (string) $data['Description'],
            minClassRead: (int) $data['MinClassRead'],
            minClassWrite: (int) $data['MinClassWrite'],
            minClassCreate: (int) $data['MinClassCreate'],
            autoLock: (bool) $data['AutoLock'],
            autoLockWeeks: (int) $data['AutoLockWeeks'],
            numTopics: (int) $data['NumTopics'],
            numPosts: (int) $data['NumPosts'],
            lastPostId: $data['LastPostID'] ? (int) $data['LastPostID'] : null,
            lastPostAuthorId: $data['LastPostAuthorID'] ? (int) $data['LastPostAuthorID'] : null,
            lastPostTopicId: $data['LastPostTopicID'] ? (int) $data['LastPostTopicID'] : null,
            lastPostTime: $data['LastPostTime']
                ? new \DateTimeImmutable($data['LastPostTime'])
                : null
        );
    }

    public function id(): ForumId
    {
        return $this->id;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function minClassRead(): int
    {
        return $this->minClassRead;
    }

    public function minClassWrite(): int
    {
        return $this->minClassWrite;
    }

    public function minClassCreate(): int
    {
        return $this->minClassCreate;
    }

    public function numTopics(): int
    {
        return $this->numTopics;
    }

    public function numPosts(): int
    {
        return $this->numPosts;
    }

    public function lastPostTime(): ?\DateTimeImmutable
    {
        return $this->lastPostTime;
    }

    /**
     * Check if a user class can read this forum
     */
    public function canRead(int $userClass): bool
    {
        return $userClass >= $this->minClassRead;
    }

    /**
     * Check if a user class can write to this forum
     */
    public function canWrite(int $userClass): bool
    {
        return $userClass >= $this->minClassWrite;
    }

    /**
     * Check if a user class can create topics in this forum
     */
    public function canCreate(int $userClass): bool
    {
        return $userClass >= $this->minClassCreate;
    }

    /**
     * Update forum name
     */
    public function rename(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Update forum description
     */
    public function updateDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Update access levels
     */
    public function updateAccessLevels(
        int $minClassRead,
        int $minClassWrite,
        int $minClassCreate
    ): void {
        $this->minClassRead = $minClassRead;
        $this->minClassWrite = $minClassWrite;
        $this->minClassCreate = $minClassCreate;
    }

    /**
     * Record a new post
     */
    public function recordPost(
        int $postId,
        int $authorId,
        int $topicId,
        \DateTimeImmutable $time
    ): void {
        $this->numPosts++;
        $this->lastPostId = $postId;
        $this->lastPostAuthorId = $authorId;
        $this->lastPostTopicId = $topicId;
        $this->lastPostTime = $time;
    }

    /**
     * Record a new topic
     */
    public function recordTopic(): void
    {
        $this->numTopics++;
    }
}
