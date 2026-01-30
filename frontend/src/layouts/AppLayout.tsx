/**
 * AppLayout - Main Application Layout
 *
 * This is the TypeScript equivalent of View::show_header() and View::show_footer()
 * Uses Splunk UI Toolkit components with defaults from @splunk/themes
 */

import React, { useState, useCallback, useMemo } from 'react';
import styled from 'styled-components';
import SplunkThemeProvider from '@splunk/themes/SplunkThemeProvider';
import { variables } from '@splunk/themes';

import Header from '@layouts/Header';
import Footer from '@layouts/Footer';
import Sidebar from '@layouts/Sidebar';
import { useAuth } from '@hooks/useAuth';
import { useTheme } from '@hooks/useTheme';
import {
  THEME_CONFIG,
  DEFAULT_THEME,
  DEFAULT_FAMILY,
  DEFAULT_COLOR_SCHEME,
  DEFAULT_DENSITY,
  type GazelleTheme,
} from '@themes';

// Layout container styled with Splunk variables
const LayoutContainer = styled.div`
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: ${variables.backgroundColor};
  color: ${variables.textColor};
  font-family: ${variables.fontFamily};
`;

const MainWrapper = styled.div`
  display: flex;
  flex: 1;
`;

const SidebarWrapper = styled.aside<{ $collapsed: boolean }>`
  width: ${({ $collapsed }) => ($collapsed ? '60px' : '240px')};
  min-height: calc(100vh - 120px);
  background-color: ${variables.backgroundColorNavigation};
  border-right: 1px solid ${variables.borderColor};
  transition: width 0.2s ease-in-out;
  flex-shrink: 0;
`;

const ContentWrapper = styled.main`
  flex: 1;
  padding: ${variables.spacingLarge};
  overflow-x: auto;
  min-width: 0;
`;

const ContentInner = styled.div`
  max-width: 1400px;
  margin: 0 auto;
`;

// Public layout (no sidebar, minimal header)
const PublicLayoutContainer = styled.div`
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: ${variables.backgroundColor};
  color: ${variables.textColor};
  font-family: ${variables.fontFamily};
`;

const PublicContent = styled.main`
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: ${variables.spacingLarge};
`;

// Props for AppLayout
export interface AppLayoutProps {
  children: React.ReactNode;
  pageTitle?: string;
  showSidebar?: boolean;
  breadcrumbs?: Array<{ label: string; href?: string }>;
}

// Props for PublicLayout
export interface PublicLayoutProps {
  children: React.ReactNode;
  pageTitle?: string;
}

/**
 * Private/Authenticated Layout
 *
 * Equivalent to PHP's privateheader.php + privatefooter.php
 */
export const AppLayout: React.FC<AppLayoutProps> = ({
  children,
  pageTitle,
  showSidebar = true,
  breadcrumbs,
}) => {
  const { user } = useAuth();
  const { theme, setTheme } = useTheme();
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

  const themeConfig = useMemo(() => {
    return THEME_CONFIG[theme] || THEME_CONFIG[DEFAULT_THEME];
  }, [theme]);

  const handleSidebarToggle = useCallback(() => {
    setSidebarCollapsed((prev) => !prev);
  }, []);

  const handleThemeChange = useCallback(
    (newTheme: GazelleTheme) => {
      setTheme(newTheme);
    },
    [setTheme]
  );

  // Update document title
  React.useEffect(() => {
    const siteName = import.meta.env.VITE_SITE_NAME || 'Gazelle';
    document.title = pageTitle ? `${pageTitle} :: ${siteName}` : siteName;
  }, [pageTitle]);

  return (
    <SplunkThemeProvider
      family={themeConfig.family}
      colorScheme={themeConfig.colorScheme}
      density={themeConfig.density}
    >
      <LayoutContainer>
        <Header
          user={user}
          theme={theme}
          onThemeChange={handleThemeChange}
          onSidebarToggle={handleSidebarToggle}
          sidebarCollapsed={sidebarCollapsed}
        />

        <MainWrapper>
          {showSidebar && (
            <SidebarWrapper $collapsed={sidebarCollapsed}>
              <Sidebar collapsed={sidebarCollapsed} />
            </SidebarWrapper>
          )}

          <ContentWrapper>
            <ContentInner>{children}</ContentInner>
          </ContentWrapper>
        </MainWrapper>

        <Footer />
      </LayoutContainer>
    </SplunkThemeProvider>
  );
};

/**
 * Public/Unauthenticated Layout
 *
 * Equivalent to PHP's publicheader.php + publicfooter.php
 */
export const PublicLayout: React.FC<PublicLayoutProps> = ({
  children,
  pageTitle,
}) => {
  // Update document title
  React.useEffect(() => {
    const siteName = import.meta.env.VITE_SITE_NAME || 'Gazelle';
    document.title = pageTitle ? `${pageTitle} :: ${siteName}` : siteName;
  }, [pageTitle]);

  return (
    <SplunkThemeProvider
      family={DEFAULT_FAMILY}
      colorScheme={DEFAULT_COLOR_SCHEME}
      density={DEFAULT_DENSITY}
    >
      <PublicLayoutContainer>
        <Header isPublic />
        <PublicContent>{children}</PublicContent>
        <Footer isPublic />
      </PublicLayoutContainer>
    </SplunkThemeProvider>
  );
};

export default AppLayout;
