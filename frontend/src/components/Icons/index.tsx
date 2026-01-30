/**
 * Gazelle Icon System
 *
 * Uses Splunk UI Toolkit icons exclusively - NO EMOJIS
 * All icons are imported from @splunk/react-icons
 *
 * @see https://splunkui.splunk.com/Packages/react-icons/icons
 */

import React from 'react';

// Action Icons
import Add from '@splunk/react-icons/Add';
import Remove from '@splunk/react-icons/Remove';
import Edit from '@splunk/react-icons/Edit';
import Delete from '@splunk/react-icons/Delete';
import Save from '@splunk/react-icons/Save';
import Cancel from '@splunk/react-icons/Cancel';
import Check from '@splunk/react-icons/Check';
import Close from '@splunk/react-icons/Close';
import Refresh from '@splunk/react-icons/Refresh';
import Search from '@splunk/react-icons/Search';
import Filter from '@splunk/react-icons/Filter';
import Sort from '@splunk/react-icons/Sort';
import Copy from '@splunk/react-icons/Copy';
import Download from '@splunk/react-icons/Download';
import Upload from '@splunk/react-icons/Upload';
import Share from '@splunk/react-icons/Share';
import Link from '@splunk/react-icons/Link';
import Unlink from '@splunk/react-icons/Unlink';
import Lock from '@splunk/react-icons/Lock';
import Unlock from '@splunk/react-icons/Unlock';
import Settings from '@splunk/react-icons/Settings';
import More from '@splunk/react-icons/More';
import Menu from '@splunk/react-icons/Menu';
import Expand from '@splunk/react-icons/Expand';
import Collapse from '@splunk/react-icons/Collapse';

// Navigation Icons
import ArrowLeft from '@splunk/react-icons/ArrowLeft';
import ArrowRight from '@splunk/react-icons/ArrowRight';
import ArrowUp from '@splunk/react-icons/ArrowUp';
import ArrowDown from '@splunk/react-icons/ArrowDown';
import ChevronLeft from '@splunk/react-icons/ChevronLeft';
import ChevronRight from '@splunk/react-icons/ChevronRight';
import ChevronUp from '@splunk/react-icons/ChevronUp';
import ChevronDown from '@splunk/react-icons/ChevronDown';
import Home from '@splunk/react-icons/Home';
import External from '@splunk/react-icons/External';

// Status Icons
import Success from '@splunk/react-icons/Success';
import Warning from '@splunk/react-icons/Warning';
import Error from '@splunk/react-icons/Error';
import Info from '@splunk/react-icons/InfoCircle';
import Question from '@splunk/react-icons/QuestionCircle';
import Clock from '@splunk/react-icons/Clock';
import Calendar from '@splunk/react-icons/Calendar';
import Bell from '@splunk/react-icons/Bell';
import BellOff from '@splunk/react-icons/BellOff';

// User Icons
import User from '@splunk/react-icons/User';
import UserGroup from '@splunk/react-icons/UserGroup';
import UserAdd from '@splunk/react-icons/UserAdd';
import UserRemove from '@splunk/react-icons/UserRemove';

// Content Icons
import Document from '@splunk/react-icons/Document';
import Folder from '@splunk/react-icons/Folder';
import FolderOpen from '@splunk/react-icons/FolderOpen';
import File from '@splunk/react-icons/File';
import Image from '@splunk/react-icons/Image';
import Video from '@splunk/react-icons/Video';
import Music from '@splunk/react-icons/Music';
import Code from '@splunk/react-icons/Code';
import Data from '@splunk/react-icons/Data';
import Table from '@splunk/react-icons/Table';
import List from '@splunk/react-icons/List';
import Grid from '@splunk/react-icons/Grid';

// Communication Icons
import Chat from '@splunk/react-icons/Chat';
import Mail from '@splunk/react-icons/Mail';
import MailOpen from '@splunk/react-icons/MailOpen';
import Send from '@splunk/react-icons/Send';
import Reply from '@splunk/react-icons/Reply';
import Forward from '@splunk/react-icons/Forward';
import Quote from '@splunk/react-icons/Quote';

// Favorite/Bookmark Icons
import Star from '@splunk/react-icons/Star';
import StarFilled from '@splunk/react-icons/StarFilled';
import Heart from '@splunk/react-icons/Heart';
import HeartFilled from '@splunk/react-icons/HeartFilled';
import Bookmark from '@splunk/react-icons/Bookmark';
import BookmarkFilled from '@splunk/react-icons/BookmarkFilled';
import Flag from '@splunk/react-icons/Flag';

// Analytics Icons
import Chart from '@splunk/react-icons/Chart';
import ChartBar from '@splunk/react-icons/ChartBar';
import ChartLine from '@splunk/react-icons/ChartLine';
import ChartPie from '@splunk/react-icons/ChartPie';
import TrendUp from '@splunk/react-icons/TrendUp';
import TrendDown from '@splunk/react-icons/TrendDown';

// Miscellaneous Icons
import Gift from '@splunk/react-icons/Gift';
import Trophy from '@splunk/react-icons/Trophy';
import Badge from '@splunk/react-icons/Badge';
import Tag from '@splunk/react-icons/Tag';
import Tags from '@splunk/react-icons/Tags';
import Globe from '@splunk/react-icons/Globe';
import Lightning from '@splunk/react-icons/Lightning';
import Fire from '@splunk/react-icons/Fire';
import Shield from '@splunk/react-icons/Shield';
import Key from '@splunk/react-icons/Key';
import Eye from '@splunk/react-icons/Eye';
import EyeOff from '@splunk/react-icons/EyeOff';
import Print from '@splunk/react-icons/Print';
import Help from '@splunk/react-icons/Help';
import Server from '@splunk/react-icons/Server';
import Cloud from '@splunk/react-icons/Cloud';
import CloudDownload from '@splunk/react-icons/CloudDownload';
import CloudUpload from '@splunk/react-icons/CloudUpload';

// Icon size presets
export type IconSize = 'small' | 'medium' | 'large' | 'xlarge';

export const ICON_SIZES: Record<IconSize, string> = {
  small: '16px',
  medium: '20px',
  large: '24px',
  xlarge: '32px',
};

// Icon component props
export interface IconProps {
  size?: IconSize;
  color?: string;
  className?: string;
  title?: string;
  onClick?: () => void;
  style?: React.CSSProperties;
}

// Helper function to create icon wrapper
const createIcon = (IconComponent: React.ComponentType<{ size?: string | number; color?: string; screenReaderText?: string }>) => {
  const WrappedIcon: React.FC<IconProps> = ({
    size = 'medium',
    color,
    className,
    title,
    onClick,
    style,
  }) => (
    <IconComponent
      size={ICON_SIZES[size]}
      color={color}
      screenReaderText={title}
    />
  );
  return WrappedIcon;
};

// Export wrapped icons with consistent API
// This maps Gazelle concepts to Splunk icons

// Action icons
export const IconAdd = createIcon(Add);
export const IconRemove = createIcon(Remove);
export const IconEdit = createIcon(Edit);
export const IconDelete = createIcon(Delete);
export const IconSave = createIcon(Save);
export const IconCancel = createIcon(Cancel);
export const IconCheck = createIcon(Check);
export const IconClose = createIcon(Close);
export const IconRefresh = createIcon(Refresh);
export const IconSearch = createIcon(Search);
export const IconFilter = createIcon(Filter);
export const IconSort = createIcon(Sort);
export const IconCopy = createIcon(Copy);
export const IconDownload = createIcon(Download);
export const IconUpload = createIcon(Upload);
export const IconShare = createIcon(Share);
export const IconLink = createIcon(Link);
export const IconUnlink = createIcon(Unlink);
export const IconLock = createIcon(Lock);
export const IconUnlock = createIcon(Unlock);
export const IconSettings = createIcon(Settings);
export const IconMore = createIcon(More);
export const IconMenu = createIcon(Menu);
export const IconExpand = createIcon(Expand);
export const IconCollapse = createIcon(Collapse);

// Navigation icons
export const IconArrowLeft = createIcon(ArrowLeft);
export const IconArrowRight = createIcon(ArrowRight);
export const IconArrowUp = createIcon(ArrowUp);
export const IconArrowDown = createIcon(ArrowDown);
export const IconChevronLeft = createIcon(ChevronLeft);
export const IconChevronRight = createIcon(ChevronRight);
export const IconChevronUp = createIcon(ChevronUp);
export const IconChevronDown = createIcon(ChevronDown);
export const IconHome = createIcon(Home);
export const IconExternal = createIcon(External);

// Status icons
export const IconSuccess = createIcon(Success);
export const IconWarning = createIcon(Warning);
export const IconError = createIcon(Error);
export const IconInfo = createIcon(Info);
export const IconQuestion = createIcon(Question);
export const IconClock = createIcon(Clock);
export const IconCalendar = createIcon(Calendar);
export const IconBell = createIcon(Bell);
export const IconBellOff = createIcon(BellOff);

// User icons
export const IconUser = createIcon(User);
export const IconUserGroup = createIcon(UserGroup);
export const IconUserAdd = createIcon(UserAdd);
export const IconUserRemove = createIcon(UserRemove);

// Content icons
export const IconDocument = createIcon(Document);
export const IconFolder = createIcon(Folder);
export const IconFolderOpen = createIcon(FolderOpen);
export const IconFile = createIcon(File);
export const IconImage = createIcon(Image);
export const IconVideo = createIcon(Video);
export const IconMusic = createIcon(Music);
export const IconCode = createIcon(Code);
export const IconData = createIcon(Data);
export const IconTable = createIcon(Table);
export const IconList = createIcon(List);
export const IconGrid = createIcon(Grid);

// Communication icons
export const IconChat = createIcon(Chat);
export const IconMail = createIcon(Mail);
export const IconMailOpen = createIcon(MailOpen);
export const IconSend = createIcon(Send);
export const IconReply = createIcon(Reply);
export const IconForward = createIcon(Forward);
export const IconQuote = createIcon(Quote);

// Favorite/Bookmark icons
export const IconStar = createIcon(Star);
export const IconStarFilled = createIcon(StarFilled);
export const IconHeart = createIcon(Heart);
export const IconHeartFilled = createIcon(HeartFilled);
export const IconBookmark = createIcon(Bookmark);
export const IconBookmarkFilled = createIcon(BookmarkFilled);
export const IconFlag = createIcon(Flag);

// Analytics icons
export const IconChart = createIcon(Chart);
export const IconChartBar = createIcon(ChartBar);
export const IconChartLine = createIcon(ChartLine);
export const IconChartPie = createIcon(ChartPie);
export const IconTrendUp = createIcon(TrendUp);
export const IconTrendDown = createIcon(TrendDown);

// Miscellaneous icons
export const IconGift = createIcon(Gift);
export const IconTrophy = createIcon(Trophy);
export const IconBadge = createIcon(Badge);
export const IconTag = createIcon(Tag);
export const IconTags = createIcon(Tags);
export const IconGlobe = createIcon(Globe);
export const IconLightning = createIcon(Lightning);
export const IconFire = createIcon(Fire);
export const IconShield = createIcon(Shield);
export const IconKey = createIcon(Key);
export const IconEye = createIcon(Eye);
export const IconEyeOff = createIcon(EyeOff);
export const IconPrint = createIcon(Print);
export const IconHelp = createIcon(Help);
export const IconServer = createIcon(Server);
export const IconCloud = createIcon(Cloud);
export const IconCloudDownload = createIcon(CloudDownload);
export const IconCloudUpload = createIcon(CloudUpload);

// Gazelle-specific icon aliases
export const IconTorrent = IconCloudDownload;
export const IconSeeding = IconCloudUpload;
export const IconSnatched = IconDownload;
export const IconFreeleech = IconLightning;
export const IconNeutralLeech = IconData;
export const IconRequest = IconFlag;
export const IconCollage = IconGrid;
export const IconForum = IconChat;
export const IconInbox = IconMail;
export const IconNotification = IconBell;
export const IconDonate = IconGift;
export const IconRatio = IconChartLine;
export const IconUploadAmount = IconTrendUp;
export const IconDownloadAmount = IconTrendDown;
export const IconWarn = IconWarning;
export const IconBan = IconUserRemove;
export const IconReport = IconFlag;
export const IconStaff = IconShield;
export const IconModerator = IconShield;
export const IconAdmin = IconKey;

// Default export for convenience
export default {
  // Sizes
  ICON_SIZES,

  // Action
  Add: IconAdd,
  Remove: IconRemove,
  Edit: IconEdit,
  Delete: IconDelete,
  Save: IconSave,
  Cancel: IconCancel,
  Check: IconCheck,
  Close: IconClose,
  Refresh: IconRefresh,
  Search: IconSearch,
  Filter: IconFilter,
  Sort: IconSort,
  Copy: IconCopy,
  Download: IconDownload,
  Upload: IconUpload,
  Share: IconShare,
  Link: IconLink,
  Unlink: IconUnlink,
  Lock: IconLock,
  Unlock: IconUnlock,
  Settings: IconSettings,
  More: IconMore,
  Menu: IconMenu,
  Expand: IconExpand,
  Collapse: IconCollapse,

  // Navigation
  ArrowLeft: IconArrowLeft,
  ArrowRight: IconArrowRight,
  ArrowUp: IconArrowUp,
  ArrowDown: IconArrowDown,
  ChevronLeft: IconChevronLeft,
  ChevronRight: IconChevronRight,
  ChevronUp: IconChevronUp,
  ChevronDown: IconChevronDown,
  Home: IconHome,
  External: IconExternal,

  // Status
  Success: IconSuccess,
  Warning: IconWarning,
  Error: IconError,
  Info: IconInfo,
  Question: IconQuestion,
  Clock: IconClock,
  Calendar: IconCalendar,
  Bell: IconBell,
  BellOff: IconBellOff,

  // User
  User: IconUser,
  UserGroup: IconUserGroup,
  UserAdd: IconUserAdd,
  UserRemove: IconUserRemove,

  // Content
  Document: IconDocument,
  Folder: IconFolder,
  FolderOpen: IconFolderOpen,
  File: IconFile,
  Image: IconImage,
  Video: IconVideo,
  Music: IconMusic,
  Code: IconCode,
  Data: IconData,
  Table: IconTable,
  List: IconList,
  Grid: IconGrid,

  // Communication
  Chat: IconChat,
  Mail: IconMail,
  MailOpen: IconMailOpen,
  Send: IconSend,
  Reply: IconReply,
  Forward: IconForward,
  Quote: IconQuote,

  // Favorite
  Star: IconStar,
  StarFilled: IconStarFilled,
  Heart: IconHeart,
  HeartFilled: IconHeartFilled,
  Bookmark: IconBookmark,
  BookmarkFilled: IconBookmarkFilled,
  Flag: IconFlag,

  // Analytics
  Chart: IconChart,
  ChartBar: IconChartBar,
  ChartLine: IconChartLine,
  ChartPie: IconChartPie,
  TrendUp: IconTrendUp,
  TrendDown: IconTrendDown,

  // Misc
  Gift: IconGift,
  Trophy: IconTrophy,
  Badge: IconBadge,
  Tag: IconTag,
  Tags: IconTags,
  Globe: IconGlobe,
  Lightning: IconLightning,
  Fire: IconFire,
  Shield: IconShield,
  Key: IconKey,
  Eye: IconEye,
  EyeOff: IconEyeOff,
  Print: IconPrint,
  Help: IconHelp,
  Server: IconServer,
  Cloud: IconCloud,
  CloudDownload: IconCloudDownload,
  CloudUpload: IconCloudUpload,

  // Gazelle aliases
  Torrent: IconTorrent,
  Seeding: IconSeeding,
  Snatched: IconSnatched,
  Freeleech: IconFreeleech,
  NeutralLeech: IconNeutralLeech,
  Request: IconRequest,
  Collage: IconCollage,
  Forum: IconForum,
  Inbox: IconInbox,
  Notification: IconNotification,
  Donate: IconDonate,
  Ratio: IconRatio,
  UploadAmount: IconUploadAmount,
  DownloadAmount: IconDownloadAmount,
  Warn: IconWarn,
  Ban: IconBan,
  Report: IconReport,
  Staff: IconStaff,
  Moderator: IconModerator,
  Admin: IconAdmin,
};
