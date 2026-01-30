/**
 * Gazelle Frontend Type Definitions
 *
 * These types mirror the PHP backend structures for type-safe communication
 */

// User and Authentication Types
export interface User {
  id: number;
  username: string;
  email: string;
  class: UserClass;
  uploaded: number;
  downloaded: number;
  ratio: number;
  enabled: boolean;
  avatar?: string;
  title?: string;
  donor: boolean;
  warned: boolean;
  permissions: UserPermissions;
  notificationsNew: number;
  messagesNew: number;
}

export interface UserClass {
  id: number;
  name: string;
  level: number;
  color?: string;
}

export interface UserPermissions {
  [key: string]: boolean;
}

export interface AuthState {
  isAuthenticated: boolean;
  user: User | null;
  authKey: string | null;
}

// Torrent Types
export interface Torrent {
  id: number;
  groupId: number;
  hash: string;
  media: string;
  format: string;
  encoding: string;
  resolution: string;
  container: string;
  size: number;
  fileCount: number;
  seeders: number;
  leechers: number;
  snatches: number;
  freeleech: boolean;
  neutralLeech: boolean;
  uploadTime: string;
  uploaderId: number;
  uploaderName: string;
  description: string;
  filePath: string;
  reported: boolean;
}

export interface TorrentGroup {
  id: number;
  categoryId: number;
  name: string;
  year: number;
  image: string;
  tags: Tag[];
  torrents: Torrent[];
  artists?: Artist[];
  wikiBody?: string;
  vanityHouse: boolean;
}

// Artist Types
export interface Artist {
  id: number;
  name: string;
  image?: string;
  body?: string;
  similarArtists?: Artist[];
  tags?: Tag[];
  torrentGroups?: TorrentGroup[];
}

// Tag Types
export interface Tag {
  id: number;
  name: string;
  count: number;
  namespace?: string;
}

// Forum Types
export interface Forum {
  id: number;
  name: string;
  description: string;
  minClassRead: number;
  minClassWrite: number;
  minClassCreate: number;
  autoLock: boolean;
  numTopics: number;
  numPosts: number;
  lastPostId: number;
  lastPostTime: string;
  lastPostAuthorId: number;
  lastTopicId: number;
}

export interface ForumTopic {
  id: number;
  forumId: number;
  title: string;
  authorId: number;
  authorName: string;
  isLocked: boolean;
  isSticky: boolean;
  numPosts: number;
  lastPostId: number;
  lastPostTime: string;
  lastPostAuthorId: number;
  createdTime: string;
}

export interface ForumPost {
  id: number;
  topicId: number;
  authorId: number;
  authorName: string;
  authorAvatar?: string;
  authorClass: UserClass;
  body: string;
  addedTime: string;
  editedTime?: string;
  editedUserId?: number;
}

// Request Types
export interface TorrentRequest {
  id: number;
  categoryId: number;
  title: string;
  year: number;
  description: string;
  image?: string;
  fillerId?: number;
  fillerName?: string;
  torrentId?: number;
  isFilled: boolean;
  bounty: number;
  votes: number;
  tags: Tag[];
  requesterId: number;
  requesterName: string;
  createdTime: string;
}

// Notification Types
export interface Notification {
  id: number;
  type: NotificationType;
  message: string;
  read: boolean;
  createdTime: string;
  data?: Record<string, unknown>;
}

export type NotificationType =
  | 'torrent'
  | 'forum'
  | 'inbox'
  | 'news'
  | 'blog'
  | 'collage'
  | 'quote'
  | 'subscription';

// Message Types
export interface Conversation {
  id: number;
  subject: string;
  unread: boolean;
  sticky: boolean;
  messages: Message[];
  participants: User[];
}

export interface Message {
  id: number;
  conversationId: number;
  senderId: number;
  senderName: string;
  body: string;
  sentTime: string;
}

// Collage Types
export interface Collage {
  id: number;
  name: string;
  description: string;
  categoryId: number;
  userId: number;
  numTorrents: number;
  numSubscribers: number;
  featured: boolean;
  locked: boolean;
  entries: CollageEntry[];
}

export interface CollageEntry {
  groupId: number;
  sort: number;
  addedTime: string;
  addedById: number;
}

// API Response Types
export interface ApiResponse<T> {
  status: 'success' | 'failure';
  response?: T;
  error?: string;
}

export interface PaginatedResponse<T> {
  items: T[];
  page: number;
  pages: number;
  total: number;
}

// View Configuration Types
export interface PageConfig {
  title: string;
  jsIncludes?: string[];
  cssIncludes?: string[];
  requiresAuth?: boolean;
  minClass?: number;
  permissions?: string[];
}

export interface BreadcrumbItem {
  label: string;
  href?: string;
}

export interface TableColumn<T> {
  key: keyof T | string;
  label: string;
  sortable?: boolean;
  width?: string | number;
  align?: 'left' | 'center' | 'right';
  render?: (value: unknown, row: T) => React.ReactNode;
}

// Form Types
export interface FormField {
  name: string;
  label: string;
  type: 'text' | 'password' | 'email' | 'textarea' | 'select' | 'checkbox' | 'radio' | 'file' | 'number';
  required?: boolean;
  placeholder?: string;
  defaultValue?: unknown;
  options?: { value: string; label: string }[];
  validation?: FormValidation;
}

export interface FormValidation {
  pattern?: RegExp;
  minLength?: number;
  maxLength?: number;
  min?: number;
  max?: number;
  custom?: (value: unknown) => string | null;
}

// Site Configuration
export interface SiteConfig {
  siteName: string;
  siteDomain: string;
  staticServer: string;
  imageDomain: string;
  bonusPointsName: string;
  openRegistration: boolean;
  debugMode: boolean;
}
