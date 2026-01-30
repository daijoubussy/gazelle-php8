/**
 * MSW Request Handlers
 *
 * Define mock API responses for all endpoints used in tests.
 * Handlers should return deterministic, predictable data.
 */

import { http, HttpResponse } from 'msw';
import { mockUsers, mockTorrents, mockForums } from '../fixtures';

// API base URL
const API_URL = '/api/v1';

/**
 * Default handlers for common API endpoints
 */
export const handlers = [
  // ========================================
  // Authentication Endpoints
  // ========================================

  http.post(`${API_URL}/auth/login`, async ({ request }) => {
    const body = await request.json() as { email: string; password: string };

    // Simulate rate limiting
    const rateLimitHeader = request.headers.get('X-Rate-Limit-Remaining');
    if (rateLimitHeader === '0') {
      return HttpResponse.json(
        { error: 'Too many login attempts' },
        { status: 429 }
      );
    }

    // Valid credentials check
    const user = mockUsers.find((u) => u.email === body.email);
    if (user && body.password === 'TestPassword123!') {
      return HttpResponse.json({
        success: true,
        user: {
          id: user.id,
          username: user.username,
          email: user.email,
          role: user.role,
        },
        token: 'mock-jwt-token-' + user.id,
        expiresAt: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
      });
    }

    return HttpResponse.json(
      { success: false, error: 'Invalid credentials' },
      { status: 401 }
    );
  }),

  http.post(`${API_URL}/auth/logout`, () => {
    return HttpResponse.json({ success: true });
  }),

  http.post(`${API_URL}/auth/password-reset`, async ({ request }) => {
    const body = await request.json() as { email: string };
    // Always return success to prevent email enumeration
    return HttpResponse.json({
      success: true,
      message: 'If an account exists, a reset email has been sent',
    });
  }),

  http.get(`${API_URL}/auth/me`, ({ request }) => {
    const authHeader = request.headers.get('Authorization');
    if (!authHeader?.startsWith('Bearer mock-jwt-token-')) {
      return HttpResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const userId = parseInt(authHeader.replace('Bearer mock-jwt-token-', ''), 10);
    const user = mockUsers.find((u) => u.id === userId);

    if (!user) {
      return HttpResponse.json({ error: 'User not found' }, { status: 404 });
    }

    return HttpResponse.json({ user });
  }),

  // ========================================
  // User Endpoints
  // ========================================

  http.get(`${API_URL}/users/:id`, ({ params }) => {
    const id = parseInt(params.id as string, 10);
    const user = mockUsers.find((u) => u.id === id);

    if (!user) {
      return HttpResponse.json({ error: 'User not found' }, { status: 404 });
    }

    return HttpResponse.json({ user });
  }),

  http.get(`${API_URL}/users/:id/stats`, ({ params }) => {
    const id = parseInt(params.id as string, 10);
    const user = mockUsers.find((u) => u.id === id);

    if (!user) {
      return HttpResponse.json({ error: 'User not found' }, { status: 404 });
    }

    return HttpResponse.json({
      uploaded: user.uploaded,
      downloaded: user.downloaded,
      ratio: user.uploaded / Math.max(user.downloaded, 1),
      bonusPoints: user.bonusPoints,
      seeding: 42,
      leeching: 2,
      snatched: 156,
    });
  }),

  // ========================================
  // Torrent Endpoints
  // ========================================

  http.get(`${API_URL}/torrents`, ({ request }) => {
    const url = new URL(request.url);
    const page = parseInt(url.searchParams.get('page') || '1', 10);
    const perPage = parseInt(url.searchParams.get('per_page') || '25', 10);
    const search = url.searchParams.get('search') || '';
    const category = url.searchParams.get('category');

    let filtered = [...mockTorrents];

    if (search) {
      filtered = filtered.filter((t) =>
        t.name.toLowerCase().includes(search.toLowerCase())
      );
    }

    if (category) {
      filtered = filtered.filter((t) => t.category === category);
    }

    const total = filtered.length;
    const start = (page - 1) * perPage;
    const paginated = filtered.slice(start, start + perPage);

    return HttpResponse.json({
      torrents: paginated,
      pagination: {
        page,
        perPage,
        total,
        totalPages: Math.ceil(total / perPage),
      },
    });
  }),

  http.get(`${API_URL}/torrents/:id`, ({ params }) => {
    const id = parseInt(params.id as string, 10);
    const torrent = mockTorrents.find((t) => t.id === id);

    if (!torrent) {
      return HttpResponse.json({ error: 'Torrent not found' }, { status: 404 });
    }

    return HttpResponse.json({ torrent });
  }),

  http.get(`${API_URL}/torrents/:id/download`, ({ params, request }) => {
    const authHeader = request.headers.get('Authorization');
    if (!authHeader) {
      return HttpResponse.json({ error: 'Unauthorized' }, { status: 401 });
    }

    const id = parseInt(params.id as string, 10);
    const torrent = mockTorrents.find((t) => t.id === id);

    if (!torrent) {
      return HttpResponse.json({ error: 'Torrent not found' }, { status: 404 });
    }

    // Return mock .torrent file
    return new HttpResponse(
      new Blob(['mock torrent content'], { type: 'application/x-bittorrent' }),
      {
        headers: {
          'Content-Disposition': `attachment; filename="${torrent.name}.torrent"`,
        },
      }
    );
  }),

  // ========================================
  // Forum Endpoints
  // ========================================

  http.get(`${API_URL}/forums`, () => {
    return HttpResponse.json({ forums: mockForums });
  }),

  http.get(`${API_URL}/forums/:id`, ({ params }) => {
    const id = parseInt(params.id as string, 10);
    const forum = mockForums.find((f) => f.id === id);

    if (!forum) {
      return HttpResponse.json({ error: 'Forum not found' }, { status: 404 });
    }

    return HttpResponse.json({ forum });
  }),

  // ========================================
  // Health Check
  // ========================================

  http.get(`${API_URL}/health`, () => {
    return HttpResponse.json({
      status: 'ok',
      timestamp: '2024-01-15T10:00:00Z',
      version: '1.0.0',
    });
  }),
];

/**
 * Error handlers for testing error scenarios
 */
export const errorHandlers = {
  serverError: http.get('*', () => {
    return HttpResponse.json({ error: 'Internal server error' }, { status: 500 });
  }),

  networkError: http.get('*', () => {
    return HttpResponse.error();
  }),

  timeout: http.get('*', async () => {
    await new Promise((resolve) => setTimeout(resolve, 30000));
    return HttpResponse.json({});
  }),
};
