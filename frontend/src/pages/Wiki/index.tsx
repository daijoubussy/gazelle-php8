/**
 * Wiki Page
 *
 * Site wiki and documentation
 */

import React from 'react';
import { useParams, Link } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Text from '@splunk/react-ui/Text';

import Card from '@components/Card';
import { IconSearch, IconWiki, IconEdit } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const WikiLayout = styled.div`
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: ${variables.spacingLarge};

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
`;

const Sidebar = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacing};
`;

const SidebarSection = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingHalf};
`;

const SidebarTitle = styled.h4`
  margin: 0;
  font-size: ${variables.fontSizeSmall};
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textGray};
  text-transform: uppercase;
`;

const SidebarLink = styled(Link)`
  display: flex;
  align-items: center;
  gap: ${variables.spacingHalf};
  padding: ${variables.spacingHalf};
  color: ${variables.textColor};
  text-decoration: none;
  border-radius: ${variables.borderRadius};

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const MainContent = styled.div`
  flex: 1;
`;

const ArticleTitle = styled.h1`
  margin: 0 0 ${variables.spacing};
  font-size: ${variables.fontSizeXXLarge};
  font-weight: ${variables.fontWeightBold};
  color: ${variables.textColor};
`;

const ArticleMeta = styled.div`
  display: flex;
  gap: ${variables.spacing};
  margin-bottom: ${variables.spacingLarge};
  padding-bottom: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const ArticleContent = styled.div`
  line-height: 1.8;
  color: ${variables.textColor};

  h2 {
    margin: ${variables.spacingLarge} 0 ${variables.spacing};
    font-size: ${variables.fontSizeLarge};
    font-weight: ${variables.fontWeightSemiBold};
  }

  p {
    margin: 0 0 ${variables.spacing};
  }

  ul,
  ol {
    margin: 0 0 ${variables.spacing};
    padding-left: ${variables.spacingLarge};
  }

  li {
    margin-bottom: ${variables.spacingHalf};
  }
`;

/**
 * Wiki Page Component
 */
const WikiPage: React.FC = () => {
  const { articleId } = useParams<{ articleId?: string }>();

  const categories = [
    { id: 'getting-started', label: 'Getting Started' },
    { id: 'uploading', label: 'Uploading Guide' },
    { id: 'ratio', label: 'Ratio System' },
    { id: 'classes', label: 'User Classes' },
    { id: 'formatting', label: 'Formatting Guide' },
  ];

  return (
    <PageContainer>
      <Card>
        <Text
          placeholder="Search wiki..."
          startAdornment={<IconSearch size="small" />}
        />
      </Card>

      <WikiLayout>
        <Sidebar>
          <Card padding="small">
            <SidebarSection>
              <SidebarTitle>Categories</SidebarTitle>
              {categories.map((cat) => (
                <SidebarLink key={cat.id} to={`/wiki/${cat.id}`}>
                  <IconWiki size="small" />
                  {cat.label}
                </SidebarLink>
              ))}
            </SidebarSection>
          </Card>
        </Sidebar>

        <MainContent>
          <Card>
            <ArticleTitle>
              {articleId ? articleId.replace(/-/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) : 'Welcome to the Wiki'}
            </ArticleTitle>
            <ArticleMeta>
              <span>Last updated: 2 days ago</span>
              <span>|</span>
              <span>Contributors: 5</span>
            </ArticleMeta>
            <ArticleContent>
              <p>
                Welcome to the site wiki. Here you can find documentation about
                how to use the site, guidelines for uploading, and answers to
                frequently asked questions.
              </p>

              <h2>Quick Links</h2>
              <ul>
                <li>Getting Started - Learn the basics of using the site</li>
                <li>Uploading Guide - How to upload content properly</li>
                <li>Ratio System - Understanding the ratio requirements</li>
                <li>User Classes - Learn about different user levels</li>
              </ul>

              <h2>Need Help?</h2>
              <p>
                If you cannot find what you are looking for, try using the
                search function or ask in the Help forum.
              </p>
            </ArticleContent>
          </Card>
        </MainContent>
      </WikiLayout>
    </PageContainer>
  );
};

export default WikiPage;
