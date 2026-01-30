/**
 * Formatting Utility Functions
 *
 * Common formatting functions for displaying data in the UI
 */

/**
 * Format bytes to human-readable string
 */
export function formatBytes(bytes: number, decimals = 2): string {
  if (bytes === 0) return '0 B';

  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

/**
 * Format ratio with color indication
 */
export function formatRatio(ratio: number): string {
  if (!isFinite(ratio)) return 'Inf.';
  if (ratio < 0) return '---';
  return ratio.toFixed(2);
}

/**
 * Format number with thousands separator
 */
export function formatNumber(num: number): string {
  return num.toLocaleString();
}

/**
 * Format percentage
 */
export function formatPercentage(value: number, decimals = 1): string {
  return `${(value * 100).toFixed(decimals)}%`;
}

/**
 * Format date to relative time
 */
export function formatRelativeTime(date: string | Date): string {
  const now = new Date();
  const then = new Date(date);
  const diffMs = now.getTime() - then.getTime();
  const diffSecs = Math.floor(diffMs / 1000);
  const diffMins = Math.floor(diffSecs / 60);
  const diffHours = Math.floor(diffMins / 60);
  const diffDays = Math.floor(diffHours / 24);
  const diffWeeks = Math.floor(diffDays / 7);
  const diffMonths = Math.floor(diffDays / 30);
  const diffYears = Math.floor(diffDays / 365);

  if (diffSecs < 60) return 'just now';
  if (diffMins < 60) return `${diffMins} minute${diffMins !== 1 ? 's' : ''} ago`;
  if (diffHours < 24) return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
  if (diffDays < 7) return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
  if (diffWeeks < 4) return `${diffWeeks} week${diffWeeks !== 1 ? 's' : ''} ago`;
  if (diffMonths < 12) return `${diffMonths} month${diffMonths !== 1 ? 's' : ''} ago`;
  return `${diffYears} year${diffYears !== 1 ? 's' : ''} ago`;
}

/**
 * Format date to standard format
 */
export function formatDate(date: string | Date, options?: Intl.DateTimeFormatOptions): string {
  const defaultOptions: Intl.DateTimeFormatOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    ...options,
  };
  return new Date(date).toLocaleDateString(undefined, defaultOptions);
}

/**
 * Format datetime with time
 */
export function formatDateTime(date: string | Date): string {
  return new Date(date).toLocaleString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

/**
 * Format duration in seconds to human-readable
 */
export function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = Math.floor(seconds % 60);

  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  }
  return `${minutes}:${secs.toString().padStart(2, '0')}`;
}

/**
 * Truncate text with ellipsis
 */
export function truncate(text: string, maxLength: number): string {
  if (text.length <= maxLength) return text;
  return `${text.slice(0, maxLength - 3)}...`;
}

/**
 * Capitalize first letter
 */
export function capitalize(text: string): string {
  return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
}

/**
 * Convert to title case
 */
export function toTitleCase(text: string): string {
  return text
    .split(' ')
    .map((word) => capitalize(word))
    .join(' ');
}

/**
 * Slugify text for URLs
 */
export function slugify(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

/**
 * Format tag with namespace
 */
export function formatTag(tag: string, namespace?: string): string {
  if (namespace) {
    return `${namespace}:${tag}`;
  }
  return tag;
}

/**
 * Parse tag with namespace
 */
export function parseTag(tag: string): { namespace?: string; name: string } {
  const parts = tag.split(':');
  if (parts.length === 2) {
    return { namespace: parts[0], name: parts[1] };
  }
  return { name: tag };
}

/**
 * Format file size for torrents
 */
export function formatTorrentSize(bytes: number): string {
  return formatBytes(bytes, 2);
}

/**
 * Format seeder/leecher count with color class
 */
export function formatPeerCount(count: number, type: 'seeders' | 'leechers'): {
  value: string;
  color: string;
} {
  const value = formatNumber(count);

  if (type === 'seeders') {
    if (count === 0) return { value, color: '#F44336' }; // Red
    if (count < 5) return { value, color: '#FF9800' }; // Orange
    return { value, color: '#4CAF50' }; // Green
  }

  // Leechers
  return { value, color: '#2196F3' }; // Blue
}

export default {
  formatBytes,
  formatRatio,
  formatNumber,
  formatPercentage,
  formatRelativeTime,
  formatDate,
  formatDateTime,
  formatDuration,
  truncate,
  capitalize,
  toTitleCase,
  slugify,
  formatTag,
  parseTag,
  formatTorrentSize,
  formatPeerCount,
};
