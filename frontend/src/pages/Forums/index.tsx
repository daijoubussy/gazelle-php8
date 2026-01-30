/**
 * Forums Page
 *
 * Forum listing and thread views
 */

import React from 'react';
import { useParams, Link } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';

import Card from '@components/Card';
import { IconForum, IconPin, IconLock, IconUsers } from '@components/Icons';
import { formatTimeAgo } from '@utils/format';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const ForumList = styled.div`
  display: flex;
  flex-direction: column;
`;

const ForumItem = styled(Link)`
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: ${variables.spacing};
  padding: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};
  align-items: center;
  text-decoration: none;
  color: inherit;

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }

  &:last-child {
    border-bottom: none;
  }
`;

const ForumIcon = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: ${variables.borderRadius};
  background-color: ${variables.backgroundColorHover};
  color: ${variables.brandColor};
`;

const ForumInfo = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
`;

const ForumName = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const ForumDescription = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const ForumStats = styled.div`
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: ${variables.spacingQuarter};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

/**
 * Forums Page Component
 */
const ForumsPage: React.FC = () => {
  const { forumId, topicId } = useParams<{ forumId?: string; topicId?: string }>();

  // Placeholder data
  const forums = [
    {
      id: 1,
      name: 'Announcements',
      description: 'Official site announcements and news',
      topics: 42,
      posts: 256,
      lastPost: { title: 'Freeleech Event', time: '2 hours ago' },
    },
    {
      id: 2,
      name: 'General Discussion',
      description: 'Talk about anything related to music',
      topics: 1234,
      posts: 45678,
      lastPost: { title: 'Best albums of 2024', time: '5 minutes ago' },
    },
    {
      id: 3,
      name: 'Help & Support',
      description: 'Get help with technical issues and site features',
      topics: 567,
      posts: 3456,
      lastPost: { title: 'How to seed properly', time: '1 hour ago' },
    },
    {
      id: 4,
      name: 'Requests',
      description: 'Request music and discuss requests',
      topics: 890,
      posts: 7890,
      lastPost: { title: 'Looking for rare album', time: '30 minutes ago' },
    },
  ];

  if (topicId) {
    return (
      <PageContainer>
        <Card title="Topic View">
          <p>Viewing topic {topicId} in forum {forumId}</p>
        </Card>
      </PageContainer>
    );
  }

  if (forumId) {
    return (
      <PageContainer>
        <Card title="Forum Topics">
          <p>Viewing topics in forum {forumId}</p>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer>
      <Card title="Forums">
        <ForumList>
          {forums.map((forum) => (
            <ForumItem key={forum.id} to={`/forums/${forum.id}`}>
              <ForumIcon>
                <IconForum size="large" />
              </ForumIcon>
              <ForumInfo>
                <ForumName>{forum.name}</ForumName>
                <ForumDescription>{forum.description}</ForumDescription>
              </ForumInfo>
              <ForumStats>
                <span>{forum.topics} topics / {forum.posts} posts</span>
                <span>Last: {forum.lastPost.title}</span>
                <span>{forum.lastPost.time}</span>
              </ForumStats>
            </ForumItem>
          ))}
        </ForumList>
      </Card>
    </PageContainer>
  );
};

export default ForumsPage;
