/**
 * Collages Page
 *
 * Browse and manage collages
 */

import React from 'react';
import { useParams, Link } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';
import Button from '@splunk/react-ui/Button';

import Card from '@components/Card';
import { IconCollage, IconAdd } from '@components/Icons';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const CollageGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: ${variables.spacing};
`;

const CollageCard = styled(Link)`
  display: flex;
  flex-direction: column;
  padding: ${variables.spacing};
  background-color: ${variables.backgroundColorSection};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  text-decoration: none;
  color: inherit;

  &:hover {
    background-color: ${variables.backgroundColorHover};
  }
`;

const CollageCover = styled.div`
  height: 150px;
  background-color: ${variables.backgroundColorHover};
  border-radius: ${variables.borderRadius};
  margin-bottom: ${variables.spacing};
  display: flex;
  align-items: center;
  justify-content: center;
  color: ${variables.textGray};
`;

const CollageTitle = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
  margin-bottom: ${variables.spacingQuarter};
`;

const CollageMeta = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

/**
 * Collages Page Component
 */
const CollagesPage: React.FC = () => {
  const { collageId } = useParams<{ collageId?: string }>();

  const collages = [
    { id: 1, name: 'Best of 2024', torrents: 42, subscribers: 156, creator: 'User1' },
    { id: 2, name: 'Essential Jazz', torrents: 89, subscribers: 234, creator: 'User2' },
    { id: 3, name: 'Electronic Classics', torrents: 67, subscribers: 189, creator: 'User3' },
    { id: 4, name: 'Hidden Gems', torrents: 34, subscribers: 78, creator: 'User4' },
  ];

  if (collageId) {
    return (
      <PageContainer>
        <Card title="Collage Details">
          <p>Viewing collage {collageId}</p>
        </Card>
      </PageContainer>
    );
  }

  return (
    <PageContainer>
      <Card
        title="Collages"
        subtitle="Curated collections of torrents"
        actions={<Button icon={<IconAdd />} appearance="primary" label="New Collage" />}
      >
        <CollageGrid>
          {collages.map((collage) => (
            <CollageCard key={collage.id} to={`/collages/${collage.id}`}>
              <CollageCover>
                <IconCollage size="large" />
              </CollageCover>
              <CollageTitle>{collage.name}</CollageTitle>
              <CollageMeta>
                {collage.torrents} torrents - {collage.subscribers} subscribers
              </CollageMeta>
              <CollageMeta>by {collage.creator}</CollageMeta>
            </CollageCard>
          ))}
        </CollageGrid>
      </Card>
    </PageContainer>
  );
};

export default CollagesPage;
