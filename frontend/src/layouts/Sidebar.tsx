/**
 * Sidebar Component
 *
 * Navigation sidebar using Splunk UI Toolkit components
 * Provides access to all main site sections
 */

import React from 'react';
import styled from 'styled-components';
import { NavLink, useLocation } from 'react-router-dom';
import { variables } from '@splunk/themes';
import Tooltip from '@splunk/react-ui/Tooltip';

import {
  IconHome,
  IconTorrent,
  IconUpload,
  IconRequest,
  IconForum,
  IconCollage,
  IconChart,
  IconUser,
  IconUserGroup,
  IconCalendar,
  IconTag,
  IconBookmark,
  IconStarFilled,
  IconSettings,
  IconShield,
  IconHelp,
  IconInfo,
  IconGift,
  IconDocument,
  IconMail,
  IconBell,
} from '@components/Icons';
import { useAuth } from '@hooks/useAuth';

// Styled components
const SidebarContainer = styled.nav<{ $collapsed: boolean }>`
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: ${variables.spacing} 0;
  overflow-y: auto;
  overflow-x: hidden;
`;

const SidebarSection = styled.div`
  margin-bottom: ${variables.spacing};
`;

const SectionTitle = styled.div<{ $collapsed: boolean }>`
  padding: ${variables.spacingHalf} ${variables.spacing};
  font-size: ${variables.fontSizeSmall};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textGray};
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
  overflow: hidden;
  opacity: ${({ $collapsed }) => ($collapsed ? 0 : 1)};
  transition: opacity 0.2s ease;
`;

const SidebarLink = styled(NavLink)<{ $collapsed: boolean }>`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
  padding: ${variables.spacingHalf} ${variables.spacing};
  color: ${variables.textColor};
  text-decoration: none;
  font-size: ${variables.fontSize};
  white-space: nowrap;
  transition: all 0.15s ease;
  border-left: 3px solid transparent;

  &:hover {
    background-color: ${variables.backgroundColorHover};
    color: ${variables.brandColor};
  }

  &.active {
    background-color: ${variables.backgroundColorHover};
    border-left-color: ${variables.brandColor};
    color: ${variables.brandColor};
  }

  span {
    opacity: ${({ $collapsed }) => ($collapsed ? 0 : 1)};
    transition: opacity 0.2s ease;
    overflow: hidden;
  }
`;

const SidebarDivider = styled.hr`
  border: none;
  border-top: 1px solid ${variables.borderColor};
  margin: ${variables.spacing} ${variables.spacing};
`;

const SidebarFooter = styled.div`
  margin-top: auto;
  padding-top: ${variables.spacing};
  border-top: 1px solid ${variables.borderColor};
`;

// Navigation item type
interface NavItem {
  to: string;
  icon: React.ReactNode;
  label: string;
  permission?: string;
  minClass?: number;
}

// Navigation sections
const mainNavItems: NavItem[] = [
  { to: '/', icon: <IconHome size="small" />, label: 'Home' },
  { to: '/torrents', icon: <IconTorrent size="small" />, label: 'Torrents' },
  { to: '/upload', icon: <IconUpload size="small" />, label: 'Upload' },
  { to: '/requests', icon: <IconRequest size="small" />, label: 'Requests' },
  { to: '/forums', icon: <IconForum size="small" />, label: 'Forums' },
  { to: '/collages', icon: <IconCollage size="small" />, label: 'Collages' },
];

const browseNavItems: NavItem[] = [
  { to: '/top10', icon: <IconChart size="small" />, label: 'Top 10' },
  { to: '/artists', icon: <IconUserGroup size="small" />, label: 'Artists' },
  { to: '/tags', icon: <IconTag size="small" />, label: 'Tags' },
  { to: '/calendar', icon: <IconCalendar size="small" />, label: 'Calendar' },
];

const userNavItems: NavItem[] = [
  { to: '/bookmarks', icon: <IconBookmark size="small" />, label: 'Bookmarks' },
  { to: '/subscriptions', icon: <IconStarFilled size="small" />, label: 'Subscriptions' },
  { to: '/notifications', icon: <IconBell size="small" />, label: 'Notifications' },
  { to: '/inbox', icon: <IconMail size="small" />, label: 'Inbox' },
];

const communityNavItems: NavItem[] = [
  { to: '/users', icon: <IconUser size="small" />, label: 'Users' },
  { to: '/donate', icon: <IconGift size="small" />, label: 'Donate' },
  { to: '/wiki', icon: <IconDocument size="small" />, label: 'Wiki' },
];

const staffNavItems: NavItem[] = [
  { to: '/tools', icon: <IconSettings size="small" />, label: 'Tools', minClass: 500 },
  { to: '/staff', icon: <IconShield size="small" />, label: 'Staff Panel', minClass: 800 },
];

// Props
export interface SidebarProps {
  collapsed: boolean;
}

/**
 * Sidebar Component
 */
const Sidebar: React.FC<SidebarProps> = ({ collapsed }) => {
  const { user, hasPermission, hasMinClass } = useAuth();
  const location = useLocation();

  // Filter nav items by permission
  const filterByAccess = (items: NavItem[]) => {
    return items.filter((item) => {
      if (item.permission && !hasPermission(item.permission)) {
        return false;
      }
      if (item.minClass && !hasMinClass(item.minClass)) {
        return false;
      }
      return true;
    });
  };

  // Render nav items
  const renderNavItems = (items: NavItem[]) => {
    const filteredItems = filterByAccess(items);

    return filteredItems.map((item) => {
      const link = (
        <SidebarLink
          key={item.to}
          to={item.to}
          $collapsed={collapsed}
          className={location.pathname === item.to ? 'active' : ''}
        >
          {item.icon}
          <span>{item.label}</span>
        </SidebarLink>
      );

      // Wrap in tooltip when collapsed
      if (collapsed) {
        return (
          <Tooltip key={item.to} content={item.label} placement="right">
            {link}
          </Tooltip>
        );
      }

      return link;
    });
  };

  return (
    <SidebarContainer $collapsed={collapsed}>
      <SidebarSection>
        <SectionTitle $collapsed={collapsed}>Main</SectionTitle>
        {renderNavItems(mainNavItems)}
      </SidebarSection>

      <SidebarSection>
        <SectionTitle $collapsed={collapsed}>Browse</SectionTitle>
        {renderNavItems(browseNavItems)}
      </SidebarSection>

      <SidebarSection>
        <SectionTitle $collapsed={collapsed}>Personal</SectionTitle>
        {renderNavItems(userNavItems)}
      </SidebarSection>

      <SidebarSection>
        <SectionTitle $collapsed={collapsed}>Community</SectionTitle>
        {renderNavItems(communityNavItems)}
      </SidebarSection>

      {user && hasMinClass(500) && (
        <SidebarSection>
          <SidebarDivider />
          <SectionTitle $collapsed={collapsed}>Staff</SectionTitle>
          {renderNavItems(staffNavItems)}
        </SidebarSection>
      )}

      <SidebarFooter>
        <SidebarLink to="/help" $collapsed={collapsed}>
          <IconHelp size="small" />
          <span>Help</span>
        </SidebarLink>
        <SidebarLink to="/about" $collapsed={collapsed}>
          <IconInfo size="small" />
          <span>About</span>
        </SidebarLink>
      </SidebarFooter>
    </SidebarContainer>
  );
};

export default Sidebar;
