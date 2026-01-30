<?php

declare(strict_types=1);

namespace Gazelle\Infrastructure\External;

/**
 * IRC Service
 *
 * Sends messages to IRC channels.
 * Replaces legacy IRC class.
 */
final class IrcService
{
    private ?\Socket $socket = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly ?string $password = null,
        private readonly int $timeout = 5
    ) {}

    /**
     * Send a message to a channel
     */
    public function send(string $channel, string $message): bool
    {
        try {
            $this->connect();
            $this->write("PRIVMSG {$channel} :{$message}");
            return true;
        } catch (\Exception $e) {
            error_log("IRC send failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a raw IRC command
     */
    public function raw(string $command): bool
    {
        try {
            $this->connect();
            $this->write($command);
            return true;
        } catch (\Exception $e) {
            error_log("IRC raw command failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Announce a new torrent upload
     */
    public function announceUpload(
        string $channel,
        string $artist,
        string $title,
        string $format,
        int $size,
        string $url
    ): bool {
        $sizeFormatted = $this->formatSize($size);
        $message = "NEW UPLOAD: {$artist} - {$title} [{$format}] ({$sizeFormatted}) - {$url}";

        return $this->send($channel, $message);
    }

    /**
     * Announce a request being filled
     */
    public function announceRequestFilled(
        string $channel,
        string $title,
        string $fillerUsername,
        string $url
    ): bool {
        $message = "REQUEST FILLED: {$title} by {$fillerUsername} - {$url}";

        return $this->send($channel, $message);
    }

    /**
     * Connect to IRC server
     */
    private function connect(): void
    {
        if ($this->socket !== null) {
            return;
        }

        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false) {
            throw new \RuntimeException('Failed to create socket');
        }

        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, [
            'sec' => $this->timeout,
            'usec' => 0,
        ]);

        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, [
            'sec' => $this->timeout,
            'usec' => 0,
        ]);

        $result = @socket_connect($this->socket, $this->host, $this->port);

        if ($result === false) {
            $error = socket_strerror(socket_last_error($this->socket));
            throw new \RuntimeException("Failed to connect to IRC: {$error}");
        }

        // Authenticate if password is set
        if ($this->password !== null) {
            $this->write("PASS {$this->password}");
        }
    }

    /**
     * Write to socket
     */
    private function write(string $data): void
    {
        if ($this->socket === null) {
            throw new \RuntimeException('Not connected');
        }

        $data .= "\r\n";
        $written = @socket_write($this->socket, $data, strlen($data));

        if ($written === false) {
            $error = socket_strerror(socket_last_error($this->socket));
            throw new \RuntimeException("Failed to write to IRC: {$error}");
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Disconnect from IRC
     */
    public function disconnect(): void
    {
        if ($this->socket !== null) {
            @socket_close($this->socket);
            $this->socket = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
