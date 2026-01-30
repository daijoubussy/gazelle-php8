<?php

declare(strict_types=1);

namespace Gazelle\Application\Forum\Services;

use Gazelle\Application\Forum\DTOs\ForumDTO;
use Gazelle\Application\Forum\DTOs\TopicDTO;
use Gazelle\Application\Forum\DTOs\PostDTO;
use Gazelle\Domain\Forum\Forum;
use Gazelle\Domain\Forum\ForumId;
use Gazelle\Domain\User\UserId;
use Gazelle\Infrastructure\Cache\CacheInterface;
use Gazelle\Infrastructure\Persistence\DatabaseConnection;

/**
 * Forum Service
 *
 * Application service for forum operations.
 * Replaces legacy Forums class.
 */
final readonly class ForumService
{
    private const CACHE_FORUMS = 'forums_list';
    private const CACHE_FORUM = 'forum_%d';
    private const CACHE_TOPIC = 'forum_topic_%d';
    private const CACHE_DURATION = 3600;

    public function __construct(
        private DatabaseConnection $db,
        private CacheInterface $cache
    ) {}

    /**
     * Get all forums
     * Replaces Forums::get_forums()
     *
     * @return array<ForumDTO>
     */
    public function getForums(): array
    {
        $cached = $this->cache->get(self::CACHE_FORUMS);
        if (is_array($cached)) {
            return $cached;
        }

        $forums = $this->db->query("
            SELECT
                f.ID, f.CategoryID, f.Sort, f.Name, f.Description,
                f.MinClassRead, f.MinClassWrite, f.MinClassCreate,
                f.AutoLock, f.AutoLockWeeks,
                f.NumTopics, f.NumPosts,
                f.LastPostID, f.LastPostAuthorID, f.LastPostTopicID, f.LastPostTime
            FROM forums f
            ORDER BY f.CategoryID ASC, f.Sort ASC
        ");

        $result = [];
        while ($row = $forums->fetchAssoc()) {
            $result[] = ForumDTO::fromDatabaseRow($row);
        }

        $this->cache->set(self::CACHE_FORUMS, $result, self::CACHE_DURATION);

        return $result;
    }

    /**
     * Get a forum by ID
     */
    public function getForum(ForumId $id): ?ForumDTO
    {
        $cacheKey = sprintf(self::CACHE_FORUM, $id->value());

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof ForumDTO) {
            return $cached;
        }

        $result = $this->db->query("
            SELECT
                f.ID, f.CategoryID, f.Sort, f.Name, f.Description,
                f.MinClassRead, f.MinClassWrite, f.MinClassCreate,
                f.AutoLock, f.AutoLockWeeks,
                f.NumTopics, f.NumPosts,
                f.LastPostID, f.LastPostAuthorID, f.LastPostTopicID, f.LastPostTime
            FROM forums f
            WHERE f.ID = ?
        ", [$id->value()]);

        $row = $result->fetchAssoc();
        if ($row === null) {
            return null;
        }

        $dto = ForumDTO::fromDatabaseRow($row);
        $this->cache->set($cacheKey, $dto, self::CACHE_DURATION);

        return $dto;
    }

    /**
     * Get topics in a forum
     *
     * @return array<TopicDTO>
     */
    public function getTopics(
        ForumId $forumId,
        int $limit = 50,
        int $offset = 0
    ): array {
        $result = $this->db->query("
            SELECT
                t.ID, t.ForumID, t.Title, t.AuthorID,
                t.IsLocked, t.IsSticky, t.NumPosts,
                t.LastPostID, t.LastPostAuthorID, t.LastPostTime,
                t.CreatedTime
            FROM forums_topics t
            WHERE t.ForumID = ?
            ORDER BY t.IsSticky DESC, t.LastPostTime DESC
            LIMIT ? OFFSET ?
        ", [$forumId->value(), $limit, $offset]);

        $topics = [];
        while ($row = $result->fetchAssoc()) {
            $topics[] = TopicDTO::fromDatabaseRow($row);
        }

        return $topics;
    }

    /**
     * Get a topic by ID
     */
    public function getTopic(int $topicId): ?TopicDTO
    {
        $cacheKey = sprintf(self::CACHE_TOPIC, $topicId);

        $cached = $this->cache->get($cacheKey);
        if ($cached instanceof TopicDTO) {
            return $cached;
        }

        $result = $this->db->query("
            SELECT
                t.ID, t.ForumID, t.Title, t.AuthorID,
                t.IsLocked, t.IsSticky, t.NumPosts,
                t.LastPostID, t.LastPostAuthorID, t.LastPostTime,
                t.CreatedTime
            FROM forums_topics t
            WHERE t.ID = ?
        ", [$topicId]);

        $row = $result->fetchAssoc();
        if ($row === null) {
            return null;
        }

        $dto = TopicDTO::fromDatabaseRow($row);
        $this->cache->set($cacheKey, $dto, self::CACHE_DURATION);

        return $dto;
    }

    /**
     * Get posts in a topic
     *
     * @return array<PostDTO>
     */
    public function getPosts(
        int $topicId,
        int $limit = 25,
        int $offset = 0
    ): array {
        $result = $this->db->query("
            SELECT
                p.ID, p.TopicID, p.AuthorID, p.Body,
                p.AddedTime, p.EditedUserID, p.EditedTime
            FROM forums_posts p
            WHERE p.TopicID = ?
            ORDER BY p.ID ASC
            LIMIT ? OFFSET ?
        ", [$topicId, $limit, $offset]);

        $posts = [];
        while ($row = $result->fetchAssoc()) {
            $posts[] = PostDTO::fromDatabaseRow($row);
        }

        return $posts;
    }

    /**
     * Create a new topic
     */
    public function createTopic(
        ForumId $forumId,
        UserId $authorId,
        string $title,
        string $body
    ): int {
        $this->db->beginTransaction();

        try {
            // Create topic
            $this->db->execute("
                INSERT INTO forums_topics (ForumID, Title, AuthorID, CreatedTime)
                VALUES (?, ?, ?, NOW())
            ", [$forumId->value(), $title, $authorId->value()]);

            $topicId = $this->db->lastInsertId();

            // Create first post
            $this->db->execute("
                INSERT INTO forums_posts (TopicID, AuthorID, Body, AddedTime)
                VALUES (?, ?, ?, NOW())
            ", [$topicId, $authorId->value(), $body]);

            $postId = $this->db->lastInsertId();

            // Update topic with last post info
            $this->db->execute("
                UPDATE forums_topics
                SET LastPostID = ?, LastPostAuthorID = ?, LastPostTime = NOW(), NumPosts = 1
                WHERE ID = ?
            ", [$postId, $authorId->value(), $topicId]);

            // Update forum stats
            $this->db->execute("
                UPDATE forums
                SET NumTopics = NumTopics + 1, NumPosts = NumPosts + 1,
                    LastPostID = ?, LastPostAuthorID = ?,
                    LastPostTopicID = ?, LastPostTime = NOW()
                WHERE ID = ?
            ", [$postId, $authorId->value(), $topicId, $forumId->value()]);

            $this->db->commit();

            // Invalidate caches
            $this->cache->delete(self::CACHE_FORUMS);
            $this->cache->delete(sprintf(self::CACHE_FORUM, $forumId->value()));

            return $topicId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Create a new post in a topic
     */
    public function createPost(
        int $topicId,
        UserId $authorId,
        string $body
    ): int {
        $topic = $this->getTopic($topicId);
        if ($topic === null) {
            throw new \InvalidArgumentException('Topic not found');
        }

        if ($topic->isLocked) {
            throw new \RuntimeException('Topic is locked');
        }

        $this->db->beginTransaction();

        try {
            // Create post
            $this->db->execute("
                INSERT INTO forums_posts (TopicID, AuthorID, Body, AddedTime)
                VALUES (?, ?, ?, NOW())
            ", [$topicId, $authorId->value(), $body]);

            $postId = $this->db->lastInsertId();

            // Update topic
            $this->db->execute("
                UPDATE forums_topics
                SET LastPostID = ?, LastPostAuthorID = ?, LastPostTime = NOW(),
                    NumPosts = NumPosts + 1
                WHERE ID = ?
            ", [$postId, $authorId->value(), $topicId]);

            // Update forum
            $this->db->execute("
                UPDATE forums
                SET NumPosts = NumPosts + 1,
                    LastPostID = ?, LastPostAuthorID = ?,
                    LastPostTopicID = ?, LastPostTime = NOW()
                WHERE ID = ?
            ", [$postId, $authorId->value(), $topicId, $topic->forumId]);

            $this->db->commit();

            // Invalidate caches
            $this->cache->delete(sprintf(self::CACHE_TOPIC, $topicId));
            $this->cache->delete(sprintf(self::CACHE_FORUM, $topic->forumId));
            $this->cache->delete(self::CACHE_FORUMS);

            return $postId;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lock a topic
     */
    public function lockTopic(int $topicId): void
    {
        $this->db->execute("
            UPDATE forums_topics SET IsLocked = 1 WHERE ID = ?
        ", [$topicId]);

        $this->cache->delete(sprintf(self::CACHE_TOPIC, $topicId));
    }

    /**
     * Unlock a topic
     */
    public function unlockTopic(int $topicId): void
    {
        $this->db->execute("
            UPDATE forums_topics SET IsLocked = 0 WHERE ID = ?
        ", [$topicId]);

        $this->cache->delete(sprintf(self::CACHE_TOPIC, $topicId));
    }

    /**
     * Sticky a topic
     */
    public function stickyTopic(int $topicId): void
    {
        $this->db->execute("
            UPDATE forums_topics SET IsSticky = 1 WHERE ID = ?
        ", [$topicId]);

        $this->cache->delete(sprintf(self::CACHE_TOPIC, $topicId));
    }

    /**
     * Check if user can access a forum
     */
    public function canAccess(ForumDTO $forum, int $userClass): bool
    {
        return $userClass >= $forum->minClassRead;
    }

    /**
     * Check if user can post in a forum
     */
    public function canPost(ForumDTO $forum, int $userClass): bool
    {
        return $userClass >= $forum->minClassWrite;
    }

    /**
     * Check if user can create topics in a forum
     */
    public function canCreateTopic(ForumDTO $forum, int $userClass): bool
    {
        return $userClass >= $forum->minClassCreate;
    }
}
