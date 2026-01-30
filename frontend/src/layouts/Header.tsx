/**
 * Header Component
 *
 * Corresponds to privateheader.php and publicheader.php
 * Uses Splunk UI Toolkit components
 */

import React, { useMemo } from 'react';
import styled from 'styled-components';
import { Link, useNavigate } from 'react-router-dom';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';
import Menu from '@splunk/react-ui/Menu';
import Dropdown from '@splunk/react-ui/Dropdown';
import Badge from '@splunk/react-ui/Badge';
import Tooltip from '@splunk/react-ui/Tooltip';

import {
  IconMenu,
  IconSearch,
  IconBell,
  IconMail,
  IconUser,
  IconSettings,
  IconHome,
  IconChevronDown,
  IconUpload,
  IconDownload,
  IconTrendUp,
  IconTrendDown,
  IconGift,
} from '@components/Icons';
import { type User } from '@types';
import { type GazelleTheme } from '@themes';
import { formatBytes, formatRatio } from '@utils/format';

// Styled components using Splunk variables
const HeaderContainer = styled.header`
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 56px;
  padding: 0 ${variables.spacingLarge};
  background-color: ${variables.backgroundColorNavigation};
  border-bottom: 1px solid ${variables.borderColor};
  position: sticky;
  top: 0;
  z-index: ${variables.zindexFixed};
`;

const HeaderLeft = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
`;

const HeaderCenter = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingLarge};
`;

const HeaderRight = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
`;

const Logo = styled(Link)`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  font-size: ${variables.fontSizeLarge};
  font-weight: ${variables.fontWeightBold};
  color: ${variables.textColor};
  text-decoration: none;

  &:hover {
    color: ${variables.brandColor};
  }
`;

const NavLinks = styled.nav`
  display: flex;
  align-items: center;
  gap: ${variables.spacingSmall};
`;

const NavLink = styled(Link)`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  padding: ${variables.spacingHalf} ${variables.spacing};
  color: ${variables.textColor};
  text-decoration: none;
  font-size: ${variables.fontSize};
  border-radius: ${variables.borderRadius};
  transition: background-color 0.15s ease;

  &:hover {
    background-color: ${variables.backgroundColorHover};
    color: ${variables.brandColor};
  }
`;

const SearchContainer = styled.div`
  position: relative;
  width: 300px;
`;

const SearchInput = styled.input`
  width: 100%;
  padding: ${variables.spacingHalf} ${variables.spacing};
  padding-left: 36px;
  background-color: ${variables.inputBackgroundColor};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  color: ${variables.textColor};
  font-size: ${variables.fontSize};

  &::placeholder {
    color: ${variables.textGray};
  }

  &:focus {
    outline: none;
    border-color: ${variables.focusColor};
    box-shadow: 0 0 0 2px ${variables.focusShadow};
  }
`;

const SearchIcon = styled.span`
  position: absolute;
  left: ${variables.spacingHalf};
  top: 50%;
  transform: translateY(-50%);
  color: ${variables.textGray};
  pointer-events: none;
`;

const StatsContainer = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingLarge};
  padding: 0 ${variables.spacing};
`;

const StatItem = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};

  span {
    color: ${variables.textColor};
    font-weight: ${variables.fontWeightSemiBold};
  }
`;

const IconButton = styled(Button)`
  padding: ${variables.spacingHalf};
  min-width: auto;
`;

const UserMenu = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  cursor: pointer;
  padding: ${variables.spacingQuarter} ${variables.spacingHalf};
  border-radius: ${variables.borderRadius};

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const UserAvatar = styled.img`
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
`;

const UserAvatarPlaceholder = styled.div`
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: ${variables.brandColor};
  display: flex;
  align-items: center;
  justify-content: center;
  color: ${variables.white};
  font-weight: ${variables.fontWeightBold};
`;

const UserName = styled.span`
  font-size: ${variables.fontSize};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

// Props
export interface HeaderProps {
  user?: User | null;
  theme?: GazelleTheme;
  onThemeChange?: (theme: GazelleTheme) => void;
  onSidebarToggle?: () => void;
  sidebarCollapsed?: boolean;
  isPublic?: boolean;
}

/**
 * Header Component
 */
const Header: React.FC<HeaderProps> = ({
  user,
  theme,
  onThemeChange,
  onSidebarToggle,
  sidebarCollapsed,
  isPublic = false,
}) => {
  const navigate = useNavigate();

  // Calculate ratio color
  const ratioColor = useMemo(() => {
    if (!user) return variables.textColor;
    if (user.ratio >= 1) return variables.successColor;
    if (user.ratio >= 0.5) return variables.warningColor;
    return variables.errorColor;
  }, [user?.ratio]);

  // Theme menu items
  const themeMenuItems = useMemo(
    () => [
      { value: 'oppai', label: 'Oppai (Light)' },
      { value: 'beluga', label: 'Beluga (Compact)' },
      { value: 'genaviv', label: 'Genaviv (Light Compact)' },
      { value: 'kuro', label: 'Kuro (Dark)' },
    ],
    []
  );

  // User menu toggle
  const userMenuToggle = (
    <UserMenu>
      {user?.avatar ? (
        <UserAvatar src={user.avatar} alt={user.username} />
      ) : (
        <UserAvatarPlaceholder>
          {user?.username?.charAt(0).toUpperCase() || 'U'}
        </UserAvatarPlaceholder>
      )}
      <UserName>{user?.username}</UserName>
      <IconChevronDown size="small" />
    </UserMenu>
  );

  // Public header (login page, etc.)
  if (isPublic) {
    return (
      <HeaderContainer>
        <HeaderLeft>
          <Logo to="/">
            <IconHome size="medium" />
            {import.meta.env.VITE_SITE_NAME || 'Gazelle'}
          </Logo>
        </HeaderLeft>

        <HeaderRight>
          <Button appearance="primary" onClick={() => navigate('/login')}>
            Log In
          </Button>
          {import.meta.env.VITE_OPEN_REGISTRATION === 'true' && (
            <Button onClick={() => navigate('/register')}>Register</Button>
          )}
        </HeaderRight>
      </HeaderContainer>
    );
  }

  // Private header (authenticated users)
  return (
    <HeaderContainer>
      <HeaderLeft>
        {onSidebarToggle && (
          <Tooltip content={sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}>
            <IconButton appearance="secondary" onClick={onSidebarToggle}>
              <IconMenu size="medium" />
            </IconButton>
          </Tooltip>
        )}

        <Logo to="/">
          <IconHome size="medium" />
          {import.meta.env.VITE_SITE_NAME || 'Gazelle'}
        </Logo>

        <NavLinks>
          <NavLink to="/torrents">Torrents</NavLink>
          <NavLink to="/requests">Requests</NavLink>
          <NavLink to="/forums">Forums</NavLink>
          <NavLink to="/collages">Collages</NavLink>
          <NavLink to="/top10">Top 10</NavLink>
        </NavLinks>
      </HeaderLeft>

      <HeaderCenter>
        <SearchContainer>
          <SearchIcon>
            <IconSearch size="small" />
          </SearchIcon>
          <SearchInput
            type="text"
            placeholder="Search torrents..."
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                const query = (e.target as HTMLInputElement).value;
                if (query) {
                  navigate(`/torrents?search=${encodeURIComponent(query)}`);
                }
              }
            }}
          />
        </SearchContainer>

        {user && (
          <StatsContainer>
            <StatItem>
              <IconUpload size="small" color={variables.successColor} />
              <span>{formatBytes(user.uploaded)}</span>
            </StatItem>
            <StatItem>
              <IconDownload size="small" color={variables.errorColor} />
              <span>{formatBytes(user.downloaded)}</span>
            </StatItem>
            <StatItem>
              <IconTrendUp size="small" color={ratioColor} />
              <span style={{ color: ratioColor }}>{formatRatio(user.ratio)}</span>
            </StatItem>
          </StatsContainer>
        )}
      </HeaderCenter>

      <HeaderRight>
        {user && (
          <>
            <Tooltip content="Notifications">
              <IconButton
                appearance="secondary"
                onClick={() => navigate('/notifications')}
              >
                <Badge count={user.notificationsNew}>
                  <IconBell size="medium" />
                </Badge>
              </IconButton>
            </Tooltip>

            <Tooltip content="Messages">
              <IconButton appearance="secondary" onClick={() => navigate('/inbox')}>
                <Badge count={user.messagesNew}>
                  <IconMail size="medium" />
                </Badge>
              </IconButton>
            </Tooltip>

            {user.donor && (
              <Tooltip content="Donor">
                <IconGift size="medium" color={variables.brandColor} />
              </Tooltip>
            )}

            <Dropdown toggle={userMenuToggle}>
              <Menu>
                <Menu.Item onClick={() => navigate(`/user/${user.id}`)}>
                  <IconUser size="small" />
                  Profile
                </Menu.Item>
                <Menu.Item onClick={() => navigate('/user/settings')}>
                  <IconSettings size="small" />
                  Settings
                </Menu.Item>
                <Menu.Divider />
                <Menu.Heading>Theme</Menu.Heading>
                {themeMenuItems.map((item) => (
                  <Menu.Item
                    key={item.value}
                    selected={theme === item.value}
                    onClick={() => onThemeChange?.(item.value as GazelleTheme)}
                  >
                    {item.label}
                  </Menu.Item>
                ))}
                <Menu.Divider />
                <Menu.Item onClick={() => navigate('/logout')}>Log Out</Menu.Item>
              </Menu>
            </Dropdown>
          </>
        )}
      </HeaderRight>
    </HeaderContainer>
  );
};

export default Header;
