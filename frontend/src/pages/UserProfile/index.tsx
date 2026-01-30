/**
 * User Profile Page
 *
 * Display user profile and statistics
 */

import React from 'react';
import { useParams } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import TabLayout from '@splunk/react-ui/TabLayout';

import Card from '@components/Card';
import { IconUser, IconUpload, IconDownload, IconRatio, IconClock } from '@components/Icons';
import { formatSize, formatRatio, formatTimeAgo } from '@utils/format';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const ProfileHeader = styled.div`
  display: grid;
  grid-template-columns: auto 1fr;
  gap: ${variables.spacingLarge};
  align-items: start;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const Avatar = styled.div`
  width: 150px;
  height: 150px;
  border-radius: ${variables.borderRadius};
  background-color: ${variables.backgroundColorHover};
  display: flex;
  align-items: center;
  justify-content: center;
  color: ${variables.textGray};
`;

const UserInfo = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const Username = styled.h1`
  margin: 0;
  font-size: ${variables.fontSizeXXLarge};
  font-weight: ${variables.fontWeightBold};
  color: ${variables.textColor};
`;

const UserClass = styled.span`
  font-size: ${variables.fontSize};
  color: ${variables.brandColor};
`;

const StatsGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: ${variables.spacing};
`;

const StatItem = styled.div`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  padding: ${variables.spacing};
  background-color: ${variables.backgroundColorHover};
  border-radius: ${variables.borderRadius};
`;

const StatIcon = styled.div`
  color: ${variables.brandColor};
`;

const StatContent = styled.div`
  display: flex;
  flex-direction: column;
`;

const StatValue = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const StatLabel = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

/**
 * User Profile Page Component
 */
const UserProfilePage: React.FC = () => {
  const { userId } = useParams<{ userId: string }>();

  // Placeholder data
  const user = {
    id: Number(userId),
    username: 'ExampleUser',
    userClass: 'Power User',
    avatar: null,
    uploaded: 1099511627776,
    downloaded: 549755813888,
    ratio: 2.0,
    joinDate: '2020-01-15T00:00:00Z',
    lastSeen: new Date(Date.now() - 3600000).toISOString(),
    torrentsUploaded: 42,
    forumPosts: 256,
    invites: 5,
  };

  const stats = [
    { icon: <IconUpload />, value: formatSize(user.uploaded), label: 'Uploaded' },
    { icon: <IconDownload />, value: formatSize(user.downloaded), label: 'Downloaded' },
    { icon: <IconRatio />, value: formatRatio(user.ratio), label: 'Ratio' },
    { icon: <IconClock />, value: formatTimeAgo(user.lastSeen), label: 'Last Seen' },
  ];

  return (
    <PageContainer>
      <Card>
        <ProfileHeader>
          <Avatar>
            <IconUser size="xlarge" />
          </Avatar>
          <UserInfo>
            <div>
              <Username>{user.username}</Username>
              <UserClass>{user.userClass}</UserClass>
            </div>
            <StatsGrid>
              {stats.map((stat, index) => (
                <StatItem key={index}>
                  <StatIcon>{stat.icon}</StatIcon>
                  <StatContent>
                    <StatValue>{stat.value}</StatValue>
                    <StatLabel>{stat.label}</StatLabel>
                  </StatContent>
                </StatItem>
              ))}
            </StatsGrid>
          </UserInfo>
        </ProfileHeader>
      </Card>

      <TabLayout defaultActivePanelId="uploads">
        <TabLayout.Panel label="Uploads" panelId="uploads">
          <Card>
            <p>User has {user.torrentsUploaded} uploads</p>
          </Card>
        </TabLayout.Panel>
        <TabLayout.Panel label="Forum Posts" panelId="posts">
          <Card>
            <p>User has {user.forumPosts} forum posts</p>
          </Card>
        </TabLayout.Panel>
        <TabLayout.Panel label="Snatches" panelId="snatches">
          <Card>
            <p>Snatched torrents will appear here</p>
          </Card>
        </TabLayout.Panel>
      </TabLayout>
    </PageContainer>
  );
};

export default UserProfilePage;
