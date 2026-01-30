/**
 * Home Page
 *
 * Dashboard/landing page showing site stats, recent activity, and announcements
 */

import React from 'react';
import styled from 'styled-components';
import { variables } from '@splunk/themes';

import Card from '@components/Card';
import { IconNews, IconTorrent, IconUsers, IconStats } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: ${variables.spacing};
`;

const StatCard = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacing};
  padding: ${variables.spacingLarge};
  background-color: ${variables.backgroundColorSection};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
`;

const StatIcon = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: ${variables.borderRadius};
  background-color: ${variables.backgroundColorHover};
  color: ${variables.brandColor};
`;

const StatContent = styled.div`
  flex: 1;
`;

const StatValue = styled.div`
  font-size: ${variables.fontSizeXXLarge};
  font-weight: ${variables.fontWeightBold};
  color: ${variables.textColor};
`;

const StatLabel = styled.div`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const SectionGrid = styled.div`
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: ${variables.spacingLarge};

  @media (max-width: 1024px) {
    grid-template-columns: 1fr;
  }
`;

const AnnouncementItem = styled.div`
  padding: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};

  &:last-child {
    border-bottom: none;
  }
`;

const AnnouncementTitle = styled.h4`
  margin: 0 0 ${variables.spacingQuarter};
  font-size: ${variables.fontSize};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const AnnouncementMeta = styled.div`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const ActivityItem = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  padding: ${variables.spacingHalf} 0;
  border-bottom: 1px solid ${variables.borderColor};

  &:last-child {
    border-bottom: none;
  }
`;

const ActivityText = styled.span`
  flex: 1;
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textColor};
`;

const ActivityTime = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

/**
 * Home Page Component
 */
const HomePage: React.FC = () => {
  // Placeholder data - would be fetched from API
  const stats = [
    { icon: <IconTorrent size="large" />, value: '125,432', label: 'Torrents' },
    { icon: <IconUsers size="large" />, value: '45,231', label: 'Users' },
    { icon: <IconStats size="large" />, value: '2.1 PB', label: 'Total Data' },
    { icon: <IconNews size="large" />, value: '892', label: 'Active Peers' },
  ];

  const announcements = [
    { id: 1, title: 'Site Maintenance Scheduled', author: 'Admin', date: '2 hours ago' },
    { id: 2, title: 'New Upload Rules Update', author: 'Staff', date: '1 day ago' },
    { id: 3, title: 'Freeleech Weekend Event', author: 'Admin', date: '3 days ago' },
  ];

  const recentActivity = [
    { id: 1, text: 'New torrent uploaded: Example Release', time: '5 min ago' },
    { id: 2, text: 'User joined: NewMember123', time: '12 min ago' },
    { id: 3, text: 'Request filled: Requested Album', time: '25 min ago' },
    { id: 4, text: 'Forum post: Help with seeding', time: '1 hour ago' },
  ];

  return (
    <PageContainer>
      <StatsGrid>
        {stats.map((stat, index) => (
          <StatCard key={index}>
            <StatIcon>{stat.icon}</StatIcon>
            <StatContent>
              <StatValue>{stat.value}</StatValue>
              <StatLabel>{stat.label}</StatLabel>
            </StatContent>
          </StatCard>
        ))}
      </StatsGrid>

      <SectionGrid>
        <Card title="Announcements" collapsible>
          {announcements.map((announcement) => (
            <AnnouncementItem key={announcement.id}>
              <AnnouncementTitle>{announcement.title}</AnnouncementTitle>
              <AnnouncementMeta>
                Posted by {announcement.author} - {announcement.date}
              </AnnouncementMeta>
            </AnnouncementItem>
          ))}
        </Card>

        <Card title="Recent Activity" collapsible>
          {recentActivity.map((activity) => (
            <ActivityItem key={activity.id}>
              <ActivityText>{activity.text}</ActivityText>
              <ActivityTime>{activity.time}</ActivityTime>
            </ActivityItem>
          ))}
        </Card>
      </SectionGrid>
    </PageContainer>
  );
};

export default HomePage;
