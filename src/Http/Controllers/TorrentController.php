<?php

declare(strict_types=1);

namespace Gazelle\Http\Controllers;

use Gazelle\Core\Http\Request;
use Gazelle\Core\Http\Response;
use Gazelle\Http\Controller;

/**
 * Torrent Controller
 */
final class TorrentController extends Controller
{
    /**
     * Browse torrents
     */
    public function index(Request $request): Response
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('per_page', 50);
        $search = $request->query('search', '');
        $category = $request->query('category');

        // Query would go here
        return $this->success([
            'torrents' => [],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => 0,
            ],
        ]);
    }

    /**
     * Get torrent details
     */
    public function show(Request $request, int $id): Response
    {
        // Query would go here
        return $this->success([
            'id' => $id,
        ]);
    }

    /**
     * Upload a new torrent
     */
    public function store(Request $request): Response
    {
        $file = $request->file('torrent');

        if ($file === null) {
            return $this->error('Torrent file is required');
        }

        // Upload handling would go here
        return $this->created(['id' => 1]);
    }

    /**
     * Download torrent file
     */
    public function download(Request $request, int $id): Response
    {
        // Generate torrent file with user passkey
        $userId = $request->attribute('user_id');

        // Download logic would go here
        return Response::make('', 200, [
            'Content-Type' => 'application/x-bittorrent',
            'Content-Disposition' => "attachment; filename=\"{$id}.torrent\"",
        ]);
    }
}
