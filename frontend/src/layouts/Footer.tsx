/**
 * Footer Component
 *
 * Corresponds to privatefooter.php and publicfooter.php
 * Uses Splunk UI Toolkit components
 */

import React, { useMemo } from 'react';
import styled from 'styled-components';
import { Link } from 'react-router-dom';
import { variables } from '@splunk/themes';
import Tooltip from '@splunk/react-ui/Tooltip';

import { IconClock, IconServer, IconInfo } from '@components/Icons';
import { useAuth } from '@hooks/useAuth';

// Styled components
const FooterContainer = styled.footer`
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: ${variables.spacingHalf} ${variables.spacingLarge};
  background-color: ${variables.backgroundColorNavigation};
  border-top: 1px solid ${variables.borderColor};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const FooterLeft = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingLarge};
`;

const FooterCenter = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
`;

const FooterRight = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
`;

const FooterLink = styled(Link)`
  color: ${variables.textGray};
  text-decoration: none;

  &:hover {
    color: ${variables.brandColor};
    text-decoration: underline;
  }
`;

const FooterExternalLink = styled.a`
  color: ${variables.textGray};
  text-decoration: none;

  &:hover {
    color: ${variables.brandColor};
    text-decoration: underline;
  }
`;

const StatItem = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingQuarter};
`;

const PublicFooterContainer = styled.footer`
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: ${variables.spacingLarge};
  background-color: ${variables.backgroundColorNavigation};
  border-top: 1px solid ${variables.borderColor};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
  text-align: center;
`;

const PublicFooterLinks = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
  margin-bottom: ${variables.spacingHalf};
`;

const CopyrightText = styled.p`
  margin: 0;
`;

// Props
export interface FooterProps {
  isPublic?: boolean;
}

/**
 * Footer Component
 */
const Footer: React.FC<FooterProps> = ({ isPublic = false }) => {
  const { user, hasPermission } = useAuth();

  // Calculate page render time (simulated - would be passed from server in real app)
  const renderTime = useMemo(() => {
    if (typeof performance !== 'undefined') {
      return Math.round(performance.now()) / 1000;
    }
    return 0;
  }, []);

  // Current year for copyright
  const currentYear = new Date().getFullYear();

  // Site name from env
  const siteName = import.meta.env.VITE_SITE_NAME || 'Gazelle';

  // Public footer
  if (isPublic) {
    return (
      <PublicFooterContainer>
        <PublicFooterLinks>
          <FooterLink to="/about">About</FooterLink>
          <span>|</span>
          <FooterLink to="/rules">Rules</FooterLink>
          <span>|</span>
          <FooterLink to="/help">Help</FooterLink>
          <span>|</span>
          <FooterExternalLink
            href="https://github.com/OPSnet/Gazelle"
            target="_blank"
            rel="noopener noreferrer"
          >
            Powered by Gazelle
          </FooterExternalLink>
        </PublicFooterLinks>
        <CopyrightText>
          {currentYear} {siteName}. All rights reserved.
        </CopyrightText>
      </PublicFooterContainer>
    );
  }

  // Private footer
  return (
    <FooterContainer>
      <FooterLeft>
        <CopyrightText>
          {currentYear} {siteName}
        </CopyrightText>
        <FooterLink to="/changelog">Changelog</FooterLink>
        <FooterLink to="/rules">Rules</FooterLink>
        <FooterLink to="/wiki">Wiki</FooterLink>
      </FooterLeft>

      <FooterCenter>
        {hasPermission('site_debug') && (
          <>
            <Tooltip content="Page render time">
              <StatItem>
                <IconClock size="small" />
                <span>{renderTime.toFixed(3)}s</span>
              </StatItem>
            </Tooltip>

            <Tooltip content="Server status">
              <StatItem>
                <IconServer size="small" />
                <span>Online</span>
              </StatItem>
            </Tooltip>
          </>
        )}
      </FooterCenter>

      <FooterRight>
        <Tooltip content="Powered by Gazelle">
          <FooterExternalLink
            href="https://github.com/OPSnet/Gazelle"
            target="_blank"
            rel="noopener noreferrer"
          >
            <StatItem>
              <IconInfo size="small" />
              <span>Gazelle</span>
            </StatItem>
          </FooterExternalLink>
        </Tooltip>
      </FooterRight>
    </FooterContainer>
  );
};

export default Footer;
