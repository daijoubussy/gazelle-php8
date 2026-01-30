<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\External;

use Gazelle\Domain\User\UserId;

/**
 * Push Notification Service
 *
 * Sends push notifications to users.
 * Replaces legacy PushServer class.
 */
final readonly class PushNotificationService
{
    public function __construct(
        private string $apiUrl,
        private string $apiKey,
        private int $timeout = 10
    ) {}

    /**
     * Send a push notification to a user
     */
    public function send(
        UserId $userId,
        string $title,
        string $message,
        ?string $url = null
    ): bool {
        $payload = [
            'user_id' => $userId->value(),
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'timestamp' => time(),
        ];

        return $this->request('push', $payload);
    }

    /**
     * Send a notification about a new torrent
     */
    public function notifyNewTorrent(
        UserId $userId,
        string $artist,
        string $title,
        int $torrentId
    ): bool {
        return $this->send(
            $userId,
            'New Torrent Upload',
            "{$artist} - {$title}",
            "/torrents.php?id={$torrentId}"
        );
    }

    /**
     * Send a notification about a new private message
     */
    public function notifyNewMessage(
        UserId $userId,
        string $senderUsername,
        string $subject
    ): bool {
        return $this->send(
            $userId,
            "New Message from {$senderUsername}",
            $subject,
            '/inbox.php'
        );
    }

    /**
     * Send a notification about a quote
     */
    public function notifyQuote(
        UserId $userId,
        string $quoterUsername,
        int $postId
    ): bool {
        return $this->send(
            $userId,
            "Quoted by {$quoterUsername}",
            'You have been quoted in a forum post',
            "/forums.php?action=viewthread&postid={$postId}#post{$postId}"
        );
    }

    /**
     * Send a notification about a subscription update
     */
    public function notifySubscription(
        UserId $userId,
        string $topicTitle,
        int $postId
    ): bool {
        return $this->send(
            $userId,
            'Subscription Update',
            "New reply in: {$topicTitle}",
            "/forums.php?action=viewthread&postid={$postId}#post{$postId}"
        );
    }

    /**
     * Register a device for push notifications
     */
    public function registerDevice(
        UserId $userId,
        string $deviceToken,
        string $platform
    ): bool {
        $payload = [
            'user_id' => $userId->value(),
            'device_token' => $deviceToken,
            'platform' => $platform,
        ];

        return $this->request('register', $payload);
    }

    /**
     * Unregister a device
     */
    public function unregisterDevice(UserId $userId, string $deviceToken): bool
    {
        $payload = [
            'user_id' => $userId->value(),
            'device_token' => $deviceToken,
        ];

        return $this->request('unregister', $payload);
    }

    /**
     * Make an API request
     *
     * @param array<string, mixed> $payload
     */
    private function request(string $endpoint, array $payload): bool
    {
        $url = rtrim($this->apiUrl, '/') . '/' . $endpoint;

        $ch = curl_init($url);

        if ($ch === false) {
            error_log('Push notification: Failed to initialize curl');
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            error_log("Push notification failed: {$error}");
            return false;
        }

        if ($httpCode >= 400) {
            error_log("Push notification failed: HTTP {$httpCode} - {$response}");
            return false;
        }

        return true;
    }
}
