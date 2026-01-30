/**
 * Test Fixtures
 *
 * Deterministic mock data for frontend tests.
 * All data is static and predictable to ensure test reproducibility.
 */

// ========================================
// User Fixtures
// ========================================

export interface MockUser {
  id: number;
  username: string;
  email: string;
  role: string;
  uploaded: number;
  downloaded: number;
  bonusPoints: number;
  enabled: boolean;
  joinDate: string;
}

export const mockUsers: MockUser[] = [
  {
    id: 1,
    username: 'TestUser',
    email: 'test@example.com',
    role: 'user',
    uploaded: 10737418240, // 10 GB
    downloaded: 5368709120, // 5 GB
    bonusPoints: 1000,
    enabled: true,
    joinDate: '2023-01-15T10:00:00Z',
  },
  {
    id: 2,
    username: 'PowerUser',
    email: 'power@example.com',
    role: 'power_user',
    uploaded: 107374182400, // 100 GB
    downloaded: 32212254720, // 30 GB
    bonusPoints: 15000,
    enabled: true,
    joinDate: '2022-06-01T10:00:00Z',
  },
  {
    id: 3,
    username: 'ModUser',
    email: 'mod@example.com',
    role: 'moderator',
    uploaded: 536870912000, // 500 GB
    downloaded: 107374182400, // 100 GB
    bonusPoints: 50000,
    enabled: true,
    joinDate: '2021-01-01T10:00:00Z',
  },
  {
    id: 4,
    username: 'AdminUser',
    email: 'admin@example.com',
    role: 'admin',
    uploaded: 1099511627776, // 1 TB
    downloaded: 214748364800, // 200 GB
    bonusPoints: 100000,
    enabled: true,
    joinDate: '2020-01-01T10:00:00Z',
  },
  {
    id: 5,
    username: 'DisabledUser',
    email: 'disabled@example.com',
    role: 'user',
    uploaded: 0,
    downloaded: 0,
    bonusPoints: 0,
    enabled: false,
    joinDate: '2023-06-01T10:00:00Z',
  },
];

// ========================================
// Torrent Fixtures
// ========================================

export interface MockTorrent {
  id: number;
  name: string;
  category: string;
  size: number;
  seeders: number;
  leechers: number;
  snatched: number;
  uploadedAt: string;
  uploaderId: number;
  freeleech: boolean;
  description: string;
}

export const mockTorrents: MockTorrent[] = [
  {
    id: 1,
    name: 'Test Album - Artist Name (2024) [FLAC]',
    category: 'Music',
    size: 524288000, // 500 MB
    seeders: 42,
    leechers: 5,
    snatched: 156,
    uploadedAt: '2024-01-10T15:30:00Z',
    uploaderId: 2,
    freeleech: true,
    description: 'High quality FLAC rip from CD.',
  },
  {
    id: 2,
    name: 'Another Album - Different Artist (2023) [MP3 320]',
    category: 'Music',
    size: 157286400, // 150 MB
    seeders: 28,
    leechers: 3,
    snatched: 89,
    uploadedAt: '2024-01-08T10:00:00Z',
    uploaderId: 1,
    freeleech: false,
    description: 'MP3 320kbps from WEB.',
  },
  {
    id: 3,
    name: 'Educational Course - Programming 101',
    category: 'E-Learning Videos',
    size: 5368709120, // 5 GB
    seeders: 15,
    leechers: 8,
    snatched: 234,
    uploadedAt: '2024-01-05T08:00:00Z',
    uploaderId: 3,
    freeleech: false,
    description: 'Complete programming course with exercises.',
  },
  {
    id: 4,
    name: 'Classic Novel - Author Name [EPUB/MOBI/PDF]',
    category: 'E-Books',
    size: 10485760, // 10 MB
    seeders: 67,
    leechers: 1,
    snatched: 512,
    uploadedAt: '2023-12-20T12:00:00Z',
    uploaderId: 2,
    freeleech: true,
    description: 'Multiple formats included. Well formatted.',
  },
  {
    id: 5,
    name: 'Audiobook - Bestseller Title [M4B]',
    category: 'Audiobooks',
    size: 1073741824, // 1 GB
    seeders: 23,
    leechers: 4,
    snatched: 78,
    uploadedAt: '2024-01-12T09:00:00Z',
    uploaderId: 1,
    freeleech: false,
    description: 'Complete unabridged audiobook.',
  },
];

// ========================================
// Forum Fixtures
// ========================================

export interface MockForum {
  id: number;
  name: string;
  description: string;
  category: string;
  topics: number;
  posts: number;
  lastPost?: {
    title: string;
    author: string;
    date: string;
  };
}

export const mockForums: MockForum[] = [
  {
    id: 1,
    name: 'Announcements',
    description: 'Official site announcements and news',
    category: 'Site',
    topics: 45,
    posts: 892,
    lastPost: {
      title: 'January Freeleech Event',
      author: 'AdminUser',
      date: '2024-01-15T08:00:00Z',
    },
  },
  {
    id: 2,
    name: 'Support',
    description: 'Get help with site features and issues',
    category: 'Site',
    topics: 234,
    posts: 1567,
    lastPost: {
      title: 'How to use freeleech tokens?',
      author: 'TestUser',
      date: '2024-01-14T16:30:00Z',
    },
  },
  {
    id: 3,
    name: 'Music Discussion',
    description: 'Discuss music, artists, and albums',
    category: 'Discussion',
    topics: 1234,
    posts: 15678,
    lastPost: {
      title: 'Best albums of 2023',
      author: 'PowerUser',
      date: '2024-01-15T10:00:00Z',
    },
  },
  {
    id: 4,
    name: 'Requests',
    description: 'Request content from other users',
    category: 'Community',
    topics: 567,
    posts: 3456,
    lastPost: {
      title: '[REQ] Looking for rare album',
      author: 'TestUser',
      date: '2024-01-15T09:45:00Z',
    },
  },
];

// ========================================
// Helper Functions
// ========================================

/**
 * Get a mock user by ID
 */
export const getMockUser = (id: number): MockUser | undefined =>
  mockUsers.find((u) => u.id === id);

/**
 * Get a mock torrent by ID
 */
export const getMockTorrent = (id: number): MockTorrent | undefined =>
  mockTorrents.find((t) => t.id === id);

/**
 * Get a mock forum by ID
 */
export const getMockForum = (id: number): MockForum | undefined =>
  mockForums.find((f) => f.id === id);

/**
 * Create a custom mock user
 */
export const createMockUser = (overrides: Partial<MockUser> = {}): MockUser => ({
  id: Math.floor(Math.random() * 10000) + 100,
  username: 'CustomUser',
  email: 'custom@example.com',
  role: 'user',
  uploaded: 0,
  downloaded: 0,
  bonusPoints: 0,
  enabled: true,
  joinDate: '2024-01-15T10:00:00Z',
  ...overrides,
});

/**
 * Create a custom mock torrent
 */
export const createMockTorrent = (overrides: Partial<MockTorrent> = {}): MockTorrent => ({
  id: Math.floor(Math.random() * 10000) + 100,
  name: 'Custom Torrent',
  category: 'Music',
  size: 104857600,
  seeders: 10,
  leechers: 2,
  snatched: 25,
  uploadedAt: '2024-01-15T10:00:00Z',
  uploaderId: 1,
  freeleech: false,
  description: 'Test description',
  ...overrides,
});

/**
 * Format bytes to human readable string
 */
export const formatBytes = (bytes: number): string => {
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  let unitIndex = 0;
  let value = bytes;

  while (value >= 1024 && unitIndex < units.length - 1) {
    value /= 1024;
    unitIndex++;
  }

  return `${value.toFixed(2)} ${units[unitIndex]}`;
};
