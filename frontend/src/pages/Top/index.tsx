/**
 * Top Page
 *
 * Top 10 lists for various categories
 */

import React from 'react';
import { useParams, Link } from 'react-router-dom';
import styled from 'styled-components';
import { variables } from '@splunk/themes';

import Card from '@components/Card';
import { IconTrophy, IconTorrent, IconUsers, IconUpload } from '@components/Icons';
import { formatSize } from '@utils/format';

const PageContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingLarge};
`;

const CategoryNav = styled.div`
  display: flex;
  gap: ${variables.spacingHalf};
  flex-wrap: wrap;
`;

const CategoryLink = styled(Link)<{ $active?: boolean }>`
  padding: ${variables.spacingHalf} ${variables.spacing};
  background-color: ${({ $active }) =>
    $active ? variables.brandColor : variables.backgroundColorSection};
  color: ${({ $active }) => ($active ? 'white' : variables.textColor)};
  border: 1px solid ${variables.borderColor};
  border-radius: ${variables.borderRadius};
  text-decoration: none;
  font-size: ${variables.fontSizeSmall};

  &:hover {
    background-color: ${({ $active }) =>
      $active ? variables.brandColor : variables.backgroundColorHover};
  }
`;

const RankList = styled.div`
  display: flex;
  flex-direction: column;
`;

const RankItem = styled.div`
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: ${variables.spacing};
  padding: ${variables.spacing};
  border-bottom: 1px solid ${variables.borderColor};
  align-items: center;

  &:last-child {
    border-bottom: none;
  }
`;

const Rank = styled.div<{ $position: number }>`
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: ${({ $position }) =>
    $position === 1
      ? '#FFD700'
      : $position === 2
      ? '#C0C0C0'
      : $position === 3
      ? '#CD7F32'
      : variables.backgroundColorHover};
  color: ${({ $position }) => ($position <= 3 ? '#000' : variables.textColor)};
  font-weight: ${variables.fontWeightBold};
`;

const ItemInfo = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${variables.spacingQuarter};
`;

const ItemTitle = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.textColor};
`;

const ItemMeta = styled.span`
  font-size: ${variables.fontSizeSmall};
  color: ${variables.textGray};
`;

const StatValue = styled.span`
  font-weight: ${variables.fontWeightSemiBold};
  color: ${variables.brandColor};
`;

/**
 * Top Page Component
 */
const TopPage: React.FC = () => {
  const { category = 'torrents' } = useParams<{ category: string }>();

  const categories = [
    { id: 'torrents', label: 'Top Torrents', icon: <IconTorrent /> },
    { id: 'uploaders', label: 'Top Uploaders', icon: <IconUpload /> },
    { id: 'seeders', label: 'Top Seeders', icon: <IconUsers /> },
  ];

  const topItems = [
    { id: 1, name: 'Popular Release 1', detail: 'Artist Name', value: '15,234 snatches' },
    { id: 2, name: 'Popular Release 2', detail: 'Another Artist', value: '12,456 snatches' },
    { id: 3, name: 'Popular Release 3', detail: 'Band Name', value: '10,789 snatches' },
    { id: 4, name: 'Popular Release 4', detail: 'Artist 4', value: '8,234 snatches' },
    { id: 5, name: 'Popular Release 5', detail: 'Artist 5', value: '7,123 snatches' },
  ];

  return (
    <PageContainer>
      <Card title="Top 10" subtitle="Most popular content and users">
        <CategoryNav>
          {categories.map((cat) => (
            <CategoryLink
              key={cat.id}
              to={`/top/${cat.id}`}
              $active={category === cat.id}
            >
              {cat.icon} {cat.label}
            </CategoryLink>
          ))}
        </CategoryNav>
      </Card>

      <Card title={`Top ${category.charAt(0).toUpperCase() + category.slice(1)}`}>
        <RankList>
          {topItems.map((item, index) => (
            <RankItem key={item.id}>
              <Rank $position={index + 1}>{index + 1}</Rank>
              <ItemInfo>
                <ItemTitle>{item.name}</ItemTitle>
                <ItemMeta>{item.detail}</ItemMeta>
              </ItemInfo>
              <StatValue>{item.value}</StatValue>
            </RankItem>
          ))}
        </RankList>
      </Card>
    </PageContainer>
  );
};

export default TopPage;
